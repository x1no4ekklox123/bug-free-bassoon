<?php

/**
 * Date formats are date-fns patterns. Colons are quoted (HH':'mm) because a bare ":word"
 * would be turned into a placeholder when the file is sent to the front-end.
 */
return [
    'title' => 'Файловый менеджер',
    'new_file' => 'Новый файл',
    'empty' => 'Эта папка пуста.',
    'too_large' => 'Папка слишком большая для отображения в браузере, показаны только первые 250 файлов.',
    'date_format' => "d MMM yyyy, HH':'mm",
    'actions' => [
        'rename' => 'Переименовать',
        'move' => 'Переместить',
        'permissions' => 'Права доступа',
        'archive' => 'Архивировать',
        'unarchive' => 'Распаковать',
    ],
    'delete' => [
        'file_title' => 'Удалить файл',
        'directory_title' => 'Удалить папку',
        'message' => 'Содержимое <strong>:name</strong> нельзя будет восстановить после удаления.',
    ],
    'mass' => [
        'archiving' => 'Архивация файлов...',
        'deleting' => 'Удаление файлов...',
        'delete_title' => 'Удалить файлы',
        'delete_message_one' => 'Вы уверены, что хотите удалить <strong>:count файл</strong>? Это действие необратимо, восстановить файлы будет невозможно.',
        'delete_message_few' => 'Вы уверены, что хотите удалить <strong>:count файла</strong>? Это действие необратимо, восстановить файлы будет невозможно.',
        'delete_message_many' => 'Вы уверены, что хотите удалить <strong>:count файлов</strong>? Это действие необратимо, восстановить файлы будет невозможно.',
        'delete_message_other' => 'Вы уверены, что хотите удалить <strong>:count файла</strong>? Это действие необратимо, восстановить файлы будет невозможно.',
        'others_one' => 'и ещё :count',
        'others_few' => 'и ещё :count',
        'others_many' => 'и ещё :count',
        'others_other' => 'и ещё :count',
    ],
    'rename' => [
        'file_name' => 'Имя файла',
        'move_description' => 'Введите новое имя и путь к файлу или папке относительно текущей папки.',
        'new_location' => 'Новое расположение:',
    ],
    'chmod' => [
        'label' => 'Права доступа',
        'submit' => 'Применить',
    ],
    'directory' => [
        'create' => 'Создать папку',
        'name' => 'Имя папки',
        'created_as' => 'Папка будет создана как',
        'name_required' => 'Укажите корректное имя папки.',
    ],
    'upload' => [
        'button' => 'Загрузить',
        'drop' => 'Перетащите файлы сюда, чтобы загрузить их.',
        'folders_unsupported' => 'Загрузка папок не поддерживается.',
    ],
    'uploads' => [
        'title' => 'Загрузка файлов',
        'description' => 'Эти файлы сейчас загружаются на ваш сервер.',
        'in_progress_one' => 'Загружается :count файл, нажмите, чтобы посмотреть',
        'in_progress_few' => 'Загружаются :count файла, нажмите, чтобы посмотреть',
        'in_progress_many' => 'Загружаются :count файлов, нажмите, чтобы посмотреть',
        'in_progress_other' => 'Загружаются :count файла, нажмите, чтобы посмотреть',
        'cancel' => 'Отменить загрузки',
    ],
    'editor' => [
        'save' => 'Сохранить',
        'create' => 'Создать файл',
        'mode' => 'Синтаксис',
        'plain_text' => 'Обычный текст',
        'pteroignore' => 'Вы редактируете файл <code>.pteroignore</code>. Перечисленные в нём файлы и папки не попадут в бэкапы. Поддерживаются шаблоны со звёздочкой (<code>*</code>). Чтобы отменить предыдущее правило, добавьте в начало строки восклицательный знак (<code>!</code>).',
    ],
    'new_file_modal' => [
        'label' => 'Имя файла',
        'description' => 'Введите имя, под которым будет сохранён файл.',
        'required' => 'Укажите имя файла.',
        'submit' => 'Создать файл',
    ],
];
