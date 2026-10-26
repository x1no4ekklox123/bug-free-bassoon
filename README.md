# Pterodactyl Panel — Nebula

Форк [Pterodactyl Panel](https://github.com/pterodactyl/panel) **v1.15.2** (стабильная ветка 1.x, не 2.0 beta) с фиолетовой темой и бэкапами баз данных в S3.

Панель собирается и ставится **точно так же, как оригинальный Pterodactyl**: тот же архив `panel.tar.gz` с уже собранным фронтендом, те же команды из официальной документации — меняется только ссылка на архив. Никаких скриптов-патчеров, Blueprint и сборки Node.js на сервере.

## Что добавлено

- **Тема Nebula** — тёмный фиолетовый «glass» дизайн для клиентской части и админки (AdminLTE), шрифт Onest с кириллицей.
- **Бэкапы баз данных в S3** — во вкладке *Databases* у каждой базы есть кнопка бэкапов: создать, скачать (`.sql.gz`), восстановить, удалить.
  - Дамп делается самой панелью (PHP, без `mysqldump`), в фоне через очередь (`pteroq`), сохраняются таблицы, данные, view, триггеры, процедуры и функции.
  - Восстановление полностью заменяет содержимое базы содержимым бэкапа. Перед восстановлением дамп проверяется: допускаются только ожидаемые SQL-выражения, а у view/триггеров/процедур DEFINER принудительно ставится на пользователя этой базы (без эскалации прав до админа хоста БД).
  - Скачивание — через временную подписанную ссылку S3.
- **Настройки S3 в админке**: *Admin → Settings → Database Backups* — endpoint, регион, bucket, префикс, ключи (секрет хранится зашифрованным), path-style, лимит бэкапов на базу и кнопка «Test Connection». Подходит AWS S3, MinIO, Cloudflare R2, Backblaze B2, Wasabi, Selectel, Timeweb Cloud и любые S3-совместимые хранилища.
- **Права сабюзеров**: `database.backup` (просмотр/создание/скачивание), `database.backup_restore`, `database.backup_delete`.

## Установка (новая панель)

Подготовьте сервер по [официальной документации](https://pterodactyl.io/panel/1.0/getting_started.html) (PHP 8.2/8.3, MariaDB/MySQL, Redis, Nginx, Composer). Единственное отличие — архив панели скачивается из релизов этого репозитория:

```bash
mkdir -p /var/www/pterodactyl
cd /var/www/pterodactyl

curl -Lo panel.tar.gz https://github.com/x1no4ekklox123/bug-free-bassoon/releases/latest/download/panel.tar.gz
tar -xzvf panel.tar.gz
chmod -R 755 storage/* bootstrap/cache/
```

Дальше всё по официальной инструкции:

```bash
cp .env.example .env
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader
php artisan key:generate --force

php artisan p:environment:setup
php artisan p:environment:database
php artisan p:environment:mail

php artisan migrate --seed --force
php artisan p:user:make

chown -R www-data:www-data /var/www/pterodactyl/*
```

Затем, как обычно: cron `* * * * * php /var/www/pterodactyl/artisan schedule:run >> /dev/null 2>&1`, сервис очереди `pteroq.service` и конфиг Nginx — [Webserver Configuration](https://pterodactyl.io/panel/1.0/webserver_configuration.html). Wings ставится без изменений, используется официальный.

## Обновление существующей панели (в т.ч. с оригинального Pterodactyl 1.x)

Это стандартная [процедура обновления](https://pterodactyl.io/panel/1.0/updating.html) с другой ссылкой на архив:

```bash
cd /var/www/pterodactyl
php artisan down

curl -L https://github.com/x1no4ekklox123/bug-free-bassoon/releases/latest/download/panel.tar.gz | tar -xzv
chmod -R 755 storage/* bootstrap/cache

COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader
php artisan view:clear
php artisan config:clear
php artisan migrate --seed --force

chown -R www-data:www-data /var/www/pterodactyl/*
php artisan queue:restart
php artisan up
```

Все данные панели сохраняются, добавляется только таблица `database_backups`.

Вернуться на оригинальный Pterodactyl можно той же командой с официальным архивом. Если хотите убрать и таблицу бэкапов, перед этим выполните:

```bash
php artisan migrate:rollback --path=database/migrations/2026_10_26_120000_create_database_backups_table.php --force
```

## Настройка бэкапов баз данных

1. Создайте bucket в S3-хранилище и ключ доступа с правами на чтение/запись/удаление объектов в нём.
2. Откройте *Admin → Settings → Database Backups*, заполните endpoint (для AWS можно оставить пустым), регион (`auto` для Cloudflare R2), bucket и ключи. Для MinIO включите *Path Style Endpoint*.
3. Сохраните, нажмите **Test Connection**, затем включите *Status → Enabled*.
4. Убедитесь, что работает очередь: `systemctl status pteroq`. Дампы и восстановление выполняются именно ей.

Требования:

- Панель должна иметь сетевой доступ к хостам баз данных (как и для создания баз).
- Пользователь хоста БД в админке должен иметь права из официальной документации (`GRANT ALL PRIVILEGES ON *.* ... WITH GRANT OPTION`) — они нужны для восстановления view/триггеров/процедур с DEFINER пользователя базы.
- Таймаут одной операции — `DATABASE_BACKUPS_TIMEOUT` в `.env` (по умолчанию 3600 секунд).

Объекты хранятся как `prefix/<uuid сервера>/<имя базы>/<uuid бэкапа>.sql.gz`. Файл — обычный SQL-дамп, его можно импортировать и вручную: `zcat backup.sql.gz | mysql имя_базы`.

## Выпуск релиза

Релизы собирает GitHub Actions (`.github/workflows/release.yaml`) при пуше тега:

```bash
git tag v1.15.2-nebula.1
git push origin v1.15.2-nebula.1
```

Workflow собирает фронтенд и публикует `panel.tar.gz` (+ `sha256`) в релиз — ссылка `releases/latest/download/panel.tar.gz` из инструкций выше сразу начинает указывать на него. Описание релиза берётся из раздела с тем же именем в `CHANGELOG.md`.

## Разработка

```bash
composer install
npx yarn@1.22.22 install --frozen-lockfile   # проект использует yarn v1
npx yarn@1.22.22 build:production
vendor/bin/phpunit tests/Unit
vendor/bin/phpunit tests/Integration          # нужна MySQL/MariaDB из .env
```

## Лицензия

Pterodactyl® Copyright © 2015 - 2026 Dane Everitt and contributors. Код распространяется под лицензией MIT, см. [LICENSE.md](LICENSE.md). Этот проект — неофициальный форк и не связан с командой Pterodactyl.
