<?php

namespace Pterodactyl\Transformers\Api\Client;

use Pterodactyl\Models\DatabaseBackup;

class DatabaseBackupTransformer extends BaseClientTransformer
{
    public function getResourceName(): string
    {
        return DatabaseBackup::RESOURCE_NAME;
    }

    public function transform(DatabaseBackup $model): array
    {
        return [
            'uuid' => $model->uuid,
            'name' => $model->name,
            'status' => $model->status,
            'restore_status' => $model->restore_status,
            'bytes' => $model->bytes,
            'checksum' => $model->checksum,
            'error' => $model->error,
            'created_at' => $model->created_at->toAtomString(),
            'completed_at' => $model->completed_at?->toAtomString(),
            'restored_at' => $model->restored_at?->toAtomString(),
        ];
    }
}
