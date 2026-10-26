<?php

namespace Pterodactyl\Jobs\DatabaseBackup;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Pterodactyl\Models\DatabaseBackup;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Pterodactyl\Exceptions\DisplayException;
use Illuminate\Queue\Attributes\WithoutRelations;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Pterodactyl\Services\Databases\Backups\DatabaseDumper;
use Pterodactyl\Services\Databases\Backups\DatabaseBackupStorage;

#[DeleteWhenMissingModels]
class CreateDatabaseBackupJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout;

    public bool $failOnTimeout = true;

    public function __construct(#[WithoutRelations] public DatabaseBackup $backup)
    {
        $this->timeout = max(60, (int) config('pterodactyl.database_backups.timeout', 3600));
    }

    /**
     * @throws \Throwable
     */
    public function handle(DatabaseDumper $dumper, DatabaseBackupStorage $storage): void
    {
        $backup = DatabaseBackup::query()->with('database.server')->find($this->backup->id);
        if (is_null($backup) || !$backup->isPending()) {
            return;
        }

        $path = tempnam(sys_get_temp_dir(), 'ptero-db-backup-');

        try {
            $dumper->dump($backup->database, $path);

            $key = $storage->objectKey($backup);
            $storage->upload($path, $key);

            $backup->forceFill([
                'status' => DatabaseBackup::STATUS_COMPLETED,
                'object_key' => $key,
                'bytes' => filesize($path),
                'checksum' => 'sha256:' . hash_file('sha256', $path),
                'error' => null,
                'completed_at' => CarbonImmutable::now(),
            ])->save();
        } finally {
            @unlink($path);
        }
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('Failed to create database backup.', ['backup' => $this->backup->uuid, 'exception' => $exception]);

        DatabaseBackup::query()
            ->where('id', $this->backup->id)
            ->where('status', DatabaseBackup::STATUS_PENDING)
            ->update([
                'status' => DatabaseBackup::STATUS_FAILED,
                // Raw driver errors can expose infrastructure details, keep them in the logs only.
                'error' => $exception instanceof DisplayException
                    ? $exception->getMessage()
                    : 'The backup could not be created. Contact an administrator if this keeps happening.',
                'completed_at' => CarbonImmutable::now(),
            ]);
    }
}
