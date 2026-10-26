<?php

namespace Pterodactyl\Http\Requests\Admin\Settings;

use Pterodactyl\Http\Requests\Admin\AdminFormRequest;

class DatabaseBackupSettingsFormRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return [
            'pterodactyl:database_backups:enabled' => 'required|in:true,false',
            'pterodactyl:database_backups:limit' => 'required|integer|between:0,1000',
            'pterodactyl:database_backups:s3:endpoint' => 'nullable|url|max:191',
            'pterodactyl:database_backups:s3:region' => 'nullable|string|max:64',
            'pterodactyl:database_backups:s3:bucket' => 'nullable|required_if:pterodactyl:database_backups:enabled,true|string|max:191',
            'pterodactyl:database_backups:s3:prefix' => 'nullable|string|max:191',
            'pterodactyl:database_backups:s3:key' => 'nullable|required_if:pterodactyl:database_backups:enabled,true|string|max:191',
            'pterodactyl:database_backups:s3:secret' => 'nullable|string|max:191',
            'pterodactyl:database_backups:s3:use_path_style_endpoint' => 'required|in:true,false',
        ];
    }

    public function attributes(): array
    {
        return [
            'pterodactyl:database_backups:enabled' => 'Status',
            'pterodactyl:database_backups:limit' => 'Backups per Database',
            'pterodactyl:database_backups:s3:endpoint' => 'Endpoint',
            'pterodactyl:database_backups:s3:region' => 'Region',
            'pterodactyl:database_backups:s3:bucket' => 'Bucket',
            'pterodactyl:database_backups:s3:prefix' => 'Path Prefix',
            'pterodactyl:database_backups:s3:key' => 'Access Key ID',
            'pterodactyl:database_backups:s3:secret' => 'Secret Access Key',
            'pterodactyl:database_backups:s3:use_path_style_endpoint' => 'Path Style Endpoint',
        ];
    }
}
