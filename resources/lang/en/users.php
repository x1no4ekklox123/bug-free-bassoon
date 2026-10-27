<?php

return [
    'title' => 'Users',
    'empty' => 'It looks like you don\'t have any subusers.',
    'new' => 'New User',
    'row' => [
        'two_factor' => '2FA Enabled',
        'permissions' => 'Permissions',
        'edit' => 'Edit subuser',
        'delete' => 'Delete subuser',
    ],
    'remove' => [
        'title' => 'Delete this subuser?',
        'confirm' => 'Yes, remove subuser',
        'description' => 'Are you sure you wish to remove this subuser? They will have all access to this server revoked immediately.',
    ],
    'edit' => [
        'title_modify' => 'Modify permissions for :email',
        'title_view' => 'View permissions for :email',
        'title_create' => 'Create new subuser',
        'invite' => 'Invite User',
        'limited' => 'Only permissions which your account is currently assigned may be selected when creating or modifying other users.',
        'email' => 'User Email',
        'email_description' => 'Enter the email address of the user you wish to invite as a subuser for this server.',
        'email_max' => 'Email addresses must not exceed 191 characters.',
        'email_invalid' => 'A valid email address must be provided.',
    ],
    // Mirrors app/Models/Permission.php; the API text is used for anything missing here.
    'permissions' => [
        'websocket' => [
            'title' => 'websocket',
            'description' => 'Allows the user to connect to the server websocket, giving them access to view console output and realtime server stats.',
            'labels' => [
                'connect' => 'connect',
            ],
            'keys' => [
                'connect' => 'Allows a user to connect to the websocket instance for a server to stream the console.',
            ],
        ],
        'control' => [
            'title' => 'control',
            'description' => 'Permissions that control a user\'s ability to control the power state of a server, or send commands.',
            'labels' => [
                'console' => 'console',
                'start' => 'start',
                'stop' => 'stop',
                'restart' => 'restart',
            ],
            'keys' => [
                'console' => 'Allows a user to send commands to the server instance via the console.',
                'start' => 'Allows a user to start the server if it is stopped.',
                'stop' => 'Allows a user to stop a server if it is running.',
                'restart' => 'Allows a user to perform a server restart. This allows them to start the server if it is offline, but not put the server in a completely stopped state.',
            ],
        ],
        'user' => [
            'title' => 'user',
            'description' => 'Permissions that allow a user to manage other subusers on a server. They will never be able to edit their own account, or assign permissions they do not have themselves.',
            'labels' => [
                'create' => 'create',
                'read' => 'read',
                'update' => 'update',
                'delete' => 'delete',
            ],
            'keys' => [
                'create' => 'Allows a user to create new subusers for the server.',
                'read' => 'Allows the user to view subusers and their permissions for the server.',
                'update' => 'Allows a user to modify other subusers.',
                'delete' => 'Allows a user to delete a subuser from the server.',
            ],
        ],
        'file' => [
            'title' => 'file',
            'description' => 'Permissions that control a user\'s ability to modify the filesystem for this server.',
            'labels' => [
                'create' => 'create',
                'read' => 'read',
                'read-content' => 'read-content',
                'update' => 'update',
                'delete' => 'delete',
                'archive' => 'archive',
                'sftp' => 'sftp',
            ],
            'keys' => [
                'create' => 'Allows a user to create additional files and folders via the Panel or direct upload.',
                'read' => 'Allows a user to view the contents of a directory, but not view the contents of or download files.',
                'read-content' => 'Allows a user to view the contents of a given file. This will also allow the user to download files.',
                'update' => 'Allows a user to update the contents of an existing file or directory.',
                'delete' => 'Allows a user to delete files or directories.',
                'archive' => 'Allows a user to archive the contents of a directory as well as decompress existing archives on the system.',
                'sftp' => 'Allows a user to connect to SFTP and manage server files using the other assigned file permissions.',
            ],
        ],
        'backup' => [
            'title' => 'backup',
            'description' => 'Permissions that control a user\'s ability to generate and manage server backups.',
            'labels' => [
                'create' => 'create',
                'read' => 'read',
                'delete' => 'delete',
                'download' => 'download',
                'restore' => 'restore',
            ],
            'keys' => [
                'create' => 'Allows a user to create new backups for this server.',
                'read' => 'Allows a user to view all backups that exist for this server.',
                'delete' => 'Allows a user to remove backups from the system.',
                'download' => 'Allows a user to download a backup for the server. Danger: this allows a user to access all files for the server in the backup.',
                'restore' => 'Allows a user to restore a backup for the server. Danger: this allows the user to delete all of the server files in the process.',
            ],
        ],
        'allocation' => [
            'title' => 'allocation',
            'description' => 'Permissions that control a user\'s ability to modify the port allocations for this server.',
            'labels' => [
                'read' => 'read',
                'create' => 'create',
                'update' => 'update',
                'delete' => 'delete',
            ],
            'keys' => [
                'read' => 'Allows a user to view all allocations currently assigned to this server. Users with any level of access to this server can always view the primary allocation.',
                'create' => 'Allows a user to assign additional allocations to the server.',
                'update' => 'Allows a user to change the primary server allocation and attach notes to each allocation.',
                'delete' => 'Allows a user to delete an allocation from the server.',
            ],
        ],
        'startup' => [
            'title' => 'startup',
            'description' => 'Permissions that control a user\'s ability to view this server\'s startup parameters.',
            'labels' => [
                'read' => 'read',
                'update' => 'update',
                'docker-image' => 'docker-image',
            ],
            'keys' => [
                'read' => 'Allows a user to view the startup variables for a server.',
                'update' => 'Allows a user to modify the startup variables for the server.',
                'docker-image' => 'Allows a user to modify the Docker image used when running the server.',
            ],
        ],
        'database' => [
            'title' => 'database',
            'description' => 'Permissions that control a user\'s access to the database management for this server.',
            'labels' => [
                'create' => 'create',
                'read' => 'read',
                'update' => 'update',
                'delete' => 'delete',
                'view_password' => 'view_password',
                'backup' => 'backup',
                'backup_restore' => 'backup_restore',
                'backup_delete' => 'backup_delete',
            ],
            'keys' => [
                'create' => 'Allows a user to create a new database for this server.',
                'read' => 'Allows a user to view the database associated with this server.',
                'update' => 'Allows a user to rotate the password on a database instance. If the user does not have the view_password permission they will not see the updated password.',
                'delete' => 'Allows a user to remove a database instance from this server.',
                'view_password' => 'Allows a user to view the password associated with a database instance for this server.',
                'backup' => 'Allows a user to view, create and download S3 backups of the databases for this server.',
                'backup_restore' => 'Allows a user to restore a database from a backup. This replaces all current data in that database.',
                'backup_delete' => 'Allows a user to permanently delete database backups.',
            ],
        ],
        'schedule' => [
            'title' => 'schedule',
            'description' => 'Permissions that control a user\'s access to the schedule management for this server.',
            'labels' => [
                'create' => 'create',
                'read' => 'read',
                'update' => 'update',
                'delete' => 'delete',
            ],
            'keys' => [
                'create' => 'Allows a user to create new schedules for this server.',
                'read' => 'Allows a user to view schedules and the tasks associated with them for this server.',
                'update' => 'Allows a user to update schedules and schedule tasks for this server.',
                'delete' => 'Allows a user to delete schedules for this server.',
            ],
        ],
        'settings' => [
            'title' => 'settings',
            'description' => 'Permissions that control a user\'s access to the settings for this server.',
            'labels' => [
                'rename' => 'rename',
                'reinstall' => 'reinstall',
            ],
            'keys' => [
                'rename' => 'Allows a user to rename this server and change the description of it.',
                'reinstall' => 'Allows a user to trigger a reinstall of this server.',
            ],
        ],
        'activity' => [
            'title' => 'activity',
            'description' => 'Permissions that control a user\'s access to the server activity logs.',
            'labels' => [
                'read' => 'read',
            ],
            'keys' => [
                'read' => 'Allows a user to view the activity logs for the server.',
            ],
        ],
    ],
];
