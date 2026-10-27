<?php

/**
 * Date formats are date-fns patterns. Colons are quoted (h':'mm) because a bare ":word"
 * would be turned into a placeholder when the file is sent to the front-end.
 */
return [
    'title' => 'File Manager',
    'new_file' => 'New File',
    'empty' => 'This directory seems to be empty.',
    'too_large' => 'This directory is too large to display in the browser, limiting the output to the first 250 files.',
    'date_format' => "MMM do, yyyy h':'mma",
    'actions' => [
        'rename' => 'Rename',
        'move' => 'Move',
        'permissions' => 'Permissions',
        'archive' => 'Archive',
        'unarchive' => 'Unarchive',
    ],
    'delete' => [
        'file_title' => 'Delete File',
        'directory_title' => 'Delete Directory',
        'message' => 'You will not be able to recover the contents of <strong>:name</strong> once deleted.',
    ],
    'mass' => [
        'archiving' => 'Archiving files...',
        'deleting' => 'Deleting files...',
        'delete_title' => 'Delete Files',
        'delete_message_one' => 'Are you sure you want to delete <strong>:count file</strong>? This is a permanent action and the files cannot be recovered.',
        'delete_message_other' => 'Are you sure you want to delete <strong>:count files</strong>? This is a permanent action and the files cannot be recovered.',
        'others_one' => 'and :count other',
        'others_other' => 'and :count others',
    ],
    'rename' => [
        'file_name' => 'File Name',
        'move_description' => 'Enter the new name and directory of this file or folder, relative to the current directory.',
        'new_location' => 'New location:',
    ],
    'chmod' => [
        'label' => 'File Mode',
        'submit' => 'Update',
    ],
    'directory' => [
        'create' => 'Create Directory',
        'name' => 'Name',
        'created_as' => 'This directory will be created as',
        'name_required' => 'A valid directory name must be provided.',
    ],
    'upload' => [
        'button' => 'Upload',
        'drop' => 'Drag and drop files to upload.',
        'folders_unsupported' => 'Folder uploads are not supported.',
    ],
    'uploads' => [
        'title' => 'File Uploads',
        'description' => 'The following files are being uploaded to your server.',
        'in_progress_one' => ':count file is uploading, click to view',
        'in_progress_other' => ':count files are uploading, click to view',
        'cancel' => 'Cancel Uploads',
    ],
    'editor' => [
        'save' => 'Save Content',
        'create' => 'Create File',
        'mode' => 'Language',
        'plain_text' => 'Plain Text',
        'pteroignore' => 'You\'re editing a <code>.pteroignore</code> file. Any files or directories listed in here will be excluded from backups. Wildcards are supported by using an asterisk (<code>*</code>). You can negate a prior rule by prepending an exclamation point (<code>!</code>).',
    ],
    'new_file_modal' => [
        'label' => 'File Name',
        'description' => 'Enter the name that this file should be saved as.',
        'required' => 'fileName is a required field',
        'submit' => 'Create File',
    ],
];
