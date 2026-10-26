<?php

namespace Pterodactyl\Jobs\DatabaseBackup;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Pterodactyl\Models\DatabaseBackup;
use Illuminate\Foundation\Queue\Queueable;
use Pterodactyl\Exceptions\DisplayException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Attributes\WithoutRelations;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Pterodactyl\Services\Databases\Backups\DatabaseRestorer;
use Pterodactyl\Services\Databases\Backups\DatabaseBackupStorage;

#[DeleteWhenMissingModels]
class RestoreDatabaseBackupJob implements ShouldQueue
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
    public function handle(DatabaseRestorer $restorer, DatabaseBackupStorage $storage): void
    {
        $backup = DatabaseBackup::query()->with('database')->find($this->backup->id);
        if (is_null($backup) || !$backup->isRestoring() || empty($backup->object_key)) {
            return;
        }

        $path = tempnam(sys_get_temp_dir(), 'ptero-db-restore-');

        try {
            $storage->download($backup->object_key, $path);

            if (!empty($backup->checksum) && $backup->checksum !== 'sha256:' . hash_file('sha256', $path)) {
                throw new DisplayException('The backup file failed its integrity check and was not restored.');
            }

            $restorer->restore($backup->database, $path);

            $backup->forceFill([
                'restore_status' => DatabaseBackup::RESTORE_COMPLETED,
                'restored_at' => CarbonImmutable::now(),
                'error' => null,
            ])->save();
        } finally {
            @unlink($path);
        }
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('Failed to restore database backup.', ['backup' => $this->backup->uuid, 'exception' => $exception]);

        DatabaseBackup::query()
            ->where('id', $this->backup->id)
            ->where('restore_status', DatabaseBackup::RESTORE_RUNNING)
            ->update([
                'restore_status' => DatabaseBackup::RESTORE_FAILED,
                'error' => $exception instanceof DisplayException
                    ? $exception->getMessage()
                    : 'The backup could not be restored. Contact an administrator if this keeps happening.',
            ]);
    }
}
