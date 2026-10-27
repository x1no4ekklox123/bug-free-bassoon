<?php

/**
 * UI around activity log entries. The entry descriptions themselves live in activity.php.
 */
return [
    'account_title' => 'Журнал активности аккаунта',
    'server_title' => 'Журнал активности',
    'clear_filters' => 'Сбросить фильтры',
    'empty_server' => 'Для этого сервера пока нет записей активности.',
    'system' => 'Система',
    'system_user' => 'Системный пользователь',
    'using_api_key' => 'Через API-ключ',
    'using_sftp' => 'Через SFTP',
    // date-fns pattern for the date part of the exact timestamp tooltip, the time is appended in code.
    'date_format' => 'd MMM yyyy',
    'metadata' => [
        'title' => 'Метаданные',
        'view' => 'Показать дополнительные метаданные события',
    ],
];
