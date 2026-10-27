<?php

/**
 * Dashboard: server list, server cards and the server search modal.
 */
return [
    'title' => 'Серверы',
    'count_one' => ':count сервер',
    'count_few' => ':count сервера',
    'count_many' => ':count серверов',
    'count_other' => ':count сервера',
    'showing_own' => 'Ваши серверы',
    'showing_others' => 'Серверы других пользователей',
    'empty' => 'К вашему аккаунту пока не привязано ни одного сервера.',
    'empty_others' => 'Других серверов нет.',
    'server' => [
        'cpu' => 'ЦП',
        'memory' => 'ОЗУ',
        'disk' => 'Диск',
        'of' => 'из :limit',
        'no_allocation' => 'Адрес не назначен',
        'status' => [
            'running' => 'Онлайн',
            'offline' => 'Офлайн',
            'starting' => 'Запускается',
            'stopping' => 'Останавливается',
            'suspended' => 'Заблокирован',
            'connection_error' => 'Нет связи',
            'maintenance' => 'Обслуживание',
            'transferring' => 'Переносится',
            'installing' => 'Устанавливается',
            'restoring_backup' => 'Восстановление',
            'unavailable' => 'Недоступен',
            'loading' => 'Загрузка',
        ],
    ],
    'search' => [
        'title' => 'Поиск серверов',
        'label' => 'Запрос',
        'description' => 'Введите название сервера, UUID или адрес, чтобы начать поиск.',
        'min_length' => 'Введите не менее трёх символов, чтобы начать поиск.',
        'no_results' => 'По вашему запросу ничего не найдено.',
    ],
];
