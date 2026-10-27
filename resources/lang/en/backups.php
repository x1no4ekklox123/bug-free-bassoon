<?php

/**
 * Date formats are date-fns patterns. Colons are quoted (HH':'mm) because a bare ":word"
 * would be turned into a placeholder when the file is sent to the front-end.
 */
return [
    'title' => 'Backups',
    'empty' => 'It looks like there are no backups currently stored for this server.',
    'empty_page' => 'Looks like we\'ve run out of backups to show you, try going back a page.',
    'limit_zero' => 'Backups cannot be created for this server because the backup limit is set to 0.',
    'usage' => ':used of :limit backups have been created for this server.',
    'row' => [
        'failed' => 'Failed',
        'created' => 'Created',
        'date_format' => "ddd, MMMM do, yyyy HH':'mm':'ss",
    ],
    'create' => [
        'button' => 'Create backup',
        'title' => 'Create server backup',
        'name_label' => 'Backup name',
        'name_description' => 'If provided, the name that should be used to reference this backup.',
        'name_max' => 'name must be at most 191 characters',
        'ignored_label' => 'Ignored Files & Directories',
        'ignored_description' => 'Enter the files or folders to ignore while generating this backup. Leave blank to use the contents of the .pteroignore file in the root of the server directory if present. Wildcard matching of files and folders is supported in addition to negating a rule by prefixing the path with an exclamation point.',
        'locked_label' => 'Locked',
        'locked_description' => 'Prevents this backup from being deleted until explicitly unlocked.',
        'submit' => 'Start backup',
    ],
    'menu' => [
        'lock' => 'Lock',
        'unlock' => 'Unlock',
    ],
    'unlock' => [
        'title' => 'Unlock ":name"',
        'message' => 'This backup will no longer be protected from automated or accidental deletions.',
    ],
    'restore' => [
        'title' => 'Restore ":name"',
        'message' => 'Your server will be stopped. You will not be able to control the power state, access the file manager, or create additional backups until completed.',
        'truncate' => 'Delete all files before restoring backup.',
    ],
    'delete' => [
        'title' => 'Delete ":name"',
        'message' => 'This is a permanent operation. The backup cannot be recovered once deleted.',
    ],
];
