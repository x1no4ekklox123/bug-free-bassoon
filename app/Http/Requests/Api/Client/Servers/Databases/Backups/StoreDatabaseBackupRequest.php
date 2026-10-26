<?php

namespace Pterodactyl\Http\Requests\Api\Client\Servers\Databases\Backups;

use Pterodactyl\Models\Permission;
use Pterodactyl\Contracts\Http\ClientPermissionsRequest;
use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;

class StoreDatabaseBackupRequest extends ClientApiRequest implements ClientPermissionsRequest
{
    public function permission(): string
    {
        return Permission::ACTION_DATABASE_BACKUP;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:191',
        ];
    }
}
