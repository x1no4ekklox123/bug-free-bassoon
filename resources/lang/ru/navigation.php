<?php

/**
 * Client area shell: sidebar, mobile top bar and the names of the routes
 * listed in resources/scripts/routers/routes.ts.
 */
return [
    'sections' => [
        'main' => 'Основное',
        'account' => 'Аккаунт',
        'server' => 'Сервер',
    ],
    'dashboard' => 'Главная',
    'search' => 'Поиск',
    'admin_panel' => 'Админ-панель',
    'admin_server' => 'Открыть в админке',
    'routes' => [
        'account' => [
            'overview' => 'Аккаунт',
            'api' => 'API-ключи',
            'ssh' => 'SSH-ключи',
            'activity' => 'Активность',
        ],
        'server' => [
            'console' => 'Обзор',
            'files' => 'Файлы',
            'databases' => 'Базы данных',
            'schedules' => 'Планировщик',
            'users' => 'Пользователи',
            'backups' => 'Бэкапы',
            'network' => 'Сеть',
            'startup' => 'Запуск',
            'settings' => 'Настройки',
            'activity' => 'Активность',
        ],
    ],
    'selected_server' => 'Выбранный сервер',
    'switch_server' => 'Сменить сервер',
    'logout' => 'Выйти',
    'open_menu' => 'Открыть меню',
    'close_menu' => 'Закрыть меню',
];
