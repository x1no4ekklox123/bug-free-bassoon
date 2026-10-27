<?php

return [
    'return_to_login' => 'Вернуться ко входу',
    'login' => [
        'title' => 'Войдите, чтобы продолжить',
        'username' => 'Имя пользователя или e-mail',
        'password' => 'Пароль',
        'submit' => 'Войти',
        'forgot_password' => 'Забыли пароль?',
        'validation' => [
            'username_required' => 'Укажите имя пользователя или e-mail.',
            'password_required' => 'Введите пароль от аккаунта.',
        ],
    ],
    'forgot_password' => [
        'title' => 'Восстановление пароля',
        'email' => 'E-mail',
        'email_description' => 'Укажите e-mail вашего аккаунта, и мы отправим инструкции по сбросу пароля.',
        'submit' => 'Отправить письмо',
        'validation' => [
            'email' => 'Укажите корректный адрес e-mail.',
        ],
    ],
    'checkpoint' => [
        'title' => 'Подтверждение входа',
        'authentication_code' => 'Код аутентификации',
        'authentication_code_description' => 'Введите одноразовый код из приложения-аутентификатора на вашем устройстве.',
        'recovery_code' => 'Код восстановления',
        'recovery_code_description' => 'Введите один из кодов восстановления, полученных при настройке двухфакторной аутентификации для этого аккаунта.',
        'lost_device' => 'Нет доступа к устройству',
        'have_device' => 'Ввести код с устройства',
    ],
    'reset_password' => [
        'title' => 'Сброс пароля',
        'email' => 'E-mail',
        'new_password' => 'Новый пароль',
        'new_password_description' => 'Пароль должен содержать не менее 8 символов.',
        'confirm_password' => 'Подтверждение нового пароля',
        'submit' => 'Сбросить пароль',
        'validation' => [
            'password_required' => 'Введите новый пароль.',
            'password_min' => 'Новый пароль должен содержать не менее 8 символов.',
            'password_mismatch' => 'Пароли не совпадают.',
        ],
    ],
];
