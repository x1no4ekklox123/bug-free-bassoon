<?php

/**
 * Date formats are date-fns patterns. Colons are quoted (HH':'mm) because a bare ":word"
 * would be turned into a placeholder when the file is sent to the front-end.
 */
return [
    'title' => 'Databases',
    'empty' => 'It looks like you have no databases.',
    'unavailable' => 'Databases cannot be created for this server.',
    'usage' => ':used of :limit databases have been allocated to this server.',
    'endpoint' => 'Endpoint',
    'connections_from' => 'Connections from',
    'create' => [
        'button' => 'New Database',
        'title' => 'Create new database',
        'name_label' => 'Database Name',
        'name_description' => 'A descriptive name for your database instance.',
        'name_required' => 'A database name must be provided.',
        'name_min' => 'Database name must be at least 3 characters.',
        'name_max' => 'Database name must not exceed 48 characters.',
        'name_format' => 'Database name should only contain alphanumeric characters, underscores, dashes, and/or periods.',
        'connections_label' => 'Connections From',
        'connections_description' => 'Where connections should be allowed from. Leave blank to allow connections from anywhere.',
        'connections_format' => 'A valid host address must be provided.',
        'submit' => 'Create Database',
    ],
    'delete' => [
        'title' => 'Confirm database deletion',
        'message' => 'Deleting a database is a permanent action, it cannot be undone. This will permanently delete the <strong>:name</strong> database and remove all associated data.',
        'confirm_label' => 'Confirm Database Name',
        'confirm_description' => 'Enter the database name to confirm deletion.',
        'confirm_required' => 'The database name must be provided.',
        'submit' => 'Delete Database',
    ],
    'connection' => [
        'title' => 'Database connection details',
        'jdbc' => 'JDBC Connection String',
        'rotate_password' => 'Rotate Password',
    ],
    'backups' => [
        'button' => 'Backups',
        'title' => 'Backups of :name',
        'description' => 'Backups are compressed SQL dumps stored in S3.',
        'usage' => ':used of :limit backups used.',
        'disabled' => 'Database backups are currently disabled by the administrator.',
        'name_placeholder' => 'Backup name (optional)',
        'create' => 'Create Backup',
        'limit_reached' => 'The backup limit for this database has been reached, delete an older backup first.',
        'empty' => 'There are no backups for this database yet.',
        'date_format' => "ddd, MMMM do, yyyy HH':'mm':'ss",
        'status' => [
            'creating' => 'Creating backup…',
            'restoring' => 'Restoring…',
            'failed' => 'Backup failed',
            'restore_failed' => 'Last restore failed',
            'restored' => 'Restored :time',
        ],
        'restore_warning' => 'All current data in :name will be replaced with the contents of this backup. This cannot be undone.',
        'delete_warning' => 'This backup will be permanently deleted from storage.',
        'restore' => 'Restore Backup',
        'delete' => 'Delete Backup',
    ],
];
