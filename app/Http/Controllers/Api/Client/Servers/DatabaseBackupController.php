<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Database;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Models\DatabaseBackup;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Jobs\DatabaseBackup\CreateDatabaseBackupJob;
use Pterodactyl\Jobs\DatabaseBackup\RestoreDatabaseBackupJob;
use Pterodactyl\Transformers\Api\Client\DatabaseBackupTransformer;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\Services\Databases\Backups\DatabaseBackupStorage;
use Pterodactyl\Http\Requests\Api\Client\Servers\Databases\Backups\GetDatabaseBackupsRequest;
use Pterodactyl\Http\Requests\Api\Client\Servers\Databases\Backups\StoreDatabaseBackupRequest;
use Pterodactyl\Http\Requests\Api\Client\Servers\Databases\Backups\DeleteDatabaseBackupRequest;
use Pterodactyl\Http\Requests\Api\Client\Servers\Databases\Backups\RestoreDatabaseBackupRequest;
use Pterodactyl\Http\Requests\Api\Client\Servers\Databases\Backups\DownloadDatabaseBackupRequest;

class DatabaseBackupController extends ClientApiController
{
    public function __construct(private DatabaseBackupStorage $storage)
    {
        parent::__construct();
    }

    /**
     * Returns all backups of a server database.
     */
    public function index(GetDatabaseBackupsRequest $request, Server $server, Database $database): array
    {
        $this->expireStaleBackups($database);

        return $this->fractal->collection($database->backups()->latest()->latest('id')->get())
            ->transformWith($this->getTransformer(DatabaseBackupTransformer::class))
            ->addMeta([
                'enabled' => $this->storage->isEnabled(),
                'limit' => $this->storage->limit(),
            ])
            ->toArray();
    }

    /**
     * Starts a new backup of the database, the dump itself runs on the queue.
     *
     * @throws \Throwable
     */
    public function store(StoreDatabaseBackupRequest $request, Server $server, Database $database): array
    {
        $this->ensureEnabled();
        $this->expireStaleBackups($database);

        $backup = Activity::event('server:database.backup.create')->transaction(function ($log) use ($request, $database) {
            $backups = $database->backups()->lockForUpdate()->get();

            if ($backups->contains(fn (DatabaseBackup $backup) => $backup->isPending() || $backup->isRestoring())) {
                throw new DisplayException('Another backup or restore is already running for this database.');
            }

            $limit = $this->storage->limit();
            $active = $backups->where('status', '!=', DatabaseBackup::STATUS_FAILED)->count();
            if ($limit > 0 && $active >= $limit) {
                throw new DisplayException("This database already has the maximum of $limit backups, delete an existing backup first.");
            }

            $name = trim((string) $request->input('name'));

            /** @var DatabaseBackup $backup */
            $backup = $database->backups()->create([
                'uuid' => Uuid::uuid4()->toString(),
                'name' => $name !== '' ? $name : 'Backup at ' . CarbonImmutable::now()->toDateTimeString(),
                'status' => DatabaseBackup::STATUS_PENDING,
            ]);

            $log->subject($database, $backup)->property(['name' => $database->database, 'backup' => $backup->name]);

            return $backup;
        });

        CreateDatabaseBackupJob::dispatch($backup);

        return $this->fractal->item($backup->refresh())
            ->transformWith($this->getTransformer(DatabaseBackupTransformer::class))
            ->toArray();
    }

    /**
     * Returns a short-lived signed S3 link to download the backup.
     */
    public function download(DownloadDatabaseBackupRequest $request, Server $server, Database $database, DatabaseBackup $databaseBackup): JsonResponse
    {
        $this->ensureEnabled();

        if (!$databaseBackup->isCompleted() || empty($databaseBackup->object_key)) {
            throw new DisplayException('This backup has not been completed and cannot be downloaded.');
        }

        $url = $this->storage->temporaryUrl($databaseBackup->object_key, $database->database . '_' . $databaseBackup->created_at->format('Y-m-d_H-i'));

        Activity::event('server:database.backup.download')
            ->subject($database, $databaseBackup)
            ->property(['name' => $database->database, 'backup' => $databaseBackup->name])
            ->log();

        return new JsonResponse([
            'object' => 'signed_url',
            'attributes' => ['url' => $url],
        ]);
    }

    /**
     * Replaces the current contents of the database with the backup.
     *
     * @throws \Throwable
     */
    public function restore(RestoreDatabaseBackupRequest $request, Server $server, Database $database, DatabaseBackup $databaseBackup): Response
    {
        $this->ensureEnabled();
        $this->expireStaleBackups($database);

        Activity::event('server:database.backup.restore')
            ->subject($database, $databaseBackup)
            ->property(['name' => $database->database, 'backup' => $databaseBackup->name])
            ->transaction(function () use ($database, $databaseBackup) {
                $backups = $database->backups()->lockForUpdate()->get();

                if ($backups->contains(fn (DatabaseBackup $backup) => $backup->isPending() || $backup->isRestoring())) {
                    throw new DisplayException('Another backup or restore is already running for this database.');
                }

                $databaseBackup->refresh();
                if (!$databaseBackup->isCompleted() || empty($databaseBackup->object_key)) {
                    throw new DisplayException('Only completed backups can be restored.');
                }

                $databaseBackup->forceFill(['restore_status' => DatabaseBackup::RESTORE_RUNNING, 'error' => null])->save();
            });

        RestoreDatabaseBackupJob::dispatch($databaseBackup);

        return new Response('', Response::HTTP_ACCEPTED);
    }

    /**
     * Deletes the backup and its file from S3.
     */
    public function delete(DeleteDatabaseBackupRequest $request, Server $server, Database $database, DatabaseBackup $databaseBackup): Response
    {
        if ($databaseBackup->isPending() || $databaseBackup->isRestoring()) {
            throw new DisplayException('This backup cannot be deleted while it is being created or restored.');
        }

        if (!empty($databaseBackup->object_key)) {
            try {
                $this->storage->delete($databaseBackup->object_key);
            } catch (\Exception $exception) {
                // Keep the entry removable even if the bucket is unreachable or was reconfigured.
                report($exception);
            }
        }

        $databaseBackup->delete();

        Activity::event('server:database.backup.delete')
            ->subject($database)
            ->property(['name' => $database->database, 'backup' => $databaseBackup->name])
            ->log();

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    protected function ensureEnabled(): void
    {
        if (!$this->storage->isEnabled()) {
            throw new DisplayException('Database backups are not enabled on this Panel.');
        }
    }

    /**
     * Marks backups and restores whose queue job was lost (e.g. the worker was killed)
     * as failed so they do not block the database forever.
     */
    protected function expireStaleBackups(Database $database): void
    {
        $cutoff = CarbonImmutable::now()->subSeconds(max(60, (int) config('pterodactyl.database_backups.timeout', 3600)) + 600);
        $message = 'The operation did not finish in time and was marked as failed.';

        $database->backups()
            ->where('status', DatabaseBackup::STATUS_PENDING)
            ->where('created_at', '<', $cutoff)
            ->update(['status' => DatabaseBackup::STATUS_FAILED, 'error' => $message, 'completed_at' => CarbonImmutable::now()]);

        $database->backups()
            ->where('restore_status', DatabaseBackup::RESTORE_RUNNING)
            ->where('updated_at', '<', $cutoff)
            ->update(['restore_status' => DatabaseBackup::RESTORE_FAILED, 'error' => $message]);
    }
}
