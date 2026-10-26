<?php

namespace Pterodactyl\Services\Databases\Backups;

use Aws\S3\S3Client;
use Illuminate\Support\Str;
use Aws\S3\MultipartUploader;
use Pterodactyl\Models\DatabaseBackup;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

class DatabaseBackupStorage
{
    // Files above this size are uploaded with the multipart API.
    private const MULTIPART_THRESHOLD = 64 * 1024 * 1024;

    private ?S3Client $client = null;

    public function __construct(private ConfigRepository $config)
    {
    }

    /**
     * Returns true if the administrator enabled database backups and provided
     * enough S3 configuration for them to work.
     */
    public function isEnabled(): bool
    {
        return filter_var($this->config->get('pterodactyl.database_backups.enabled'), FILTER_VALIDATE_BOOL)
            && $this->isConfigured();
    }

    public function isConfigured(): bool
    {
        $settings = $this->settings();

        return !empty($settings['bucket']) && !empty($settings['key']) && !empty($settings['secret']);
    }

    /**
     * Returns the maximum number of backups for a single database, 0 means unlimited.
     */
    public function limit(): int
    {
        return max(0, (int) $this->config->get('pterodactyl.database_backups.limit', 0));
    }

    public function client(): S3Client
    {
        if (!is_null($this->client)) {
            return $this->client;
        }

        $settings = $this->settings();

        $config = [
            'version' => 'latest',
            'region' => $settings['region'] ?: 'us-east-1',
            'credentials' => [
                'key' => $settings['key'],
                'secret' => $settings['secret'],
            ],
            'use_path_style_endpoint' => filter_var($settings['use_path_style_endpoint'], FILTER_VALIDATE_BOOL),
            'http' => ['connect_timeout' => 10],
        ];

        if (!empty($settings['endpoint'])) {
            $config['endpoint'] = $settings['endpoint'];
        }

        return $this->client = new S3Client($config);
    }

    public function bucket(): string
    {
        return (string) $this->settings()['bucket'];
    }

    /**
     * Returns the object key a backup is stored under.
     */
    public function objectKey(DatabaseBackup $backup): string
    {
        $database = $backup->database;
        $key = sprintf('%s/%s/%s.sql.gz', $database->server->uuid, $database->database, $backup->uuid);
        $prefix = trim((string) $this->settings()['prefix'], '/');

        return $prefix === '' ? $key : "$prefix/$key";
    }

    public function upload(string $path, string $key): void
    {
        $params = ['ContentType' => 'application/gzip'];

        if (filesize($path) < self::MULTIPART_THRESHOLD) {
            $this->client()->putObject(array_merge($params, [
                'Bucket' => $this->bucket(),
                'Key' => $key,
                'SourceFile' => $path,
            ]));

            return;
        }

        (new MultipartUploader($this->client(), $path, [
            'bucket' => $this->bucket(),
            'key' => $key,
            'part_size' => 32 * 1024 * 1024,
            'params' => $params,
        ]))->upload();
    }

    public function download(string $key, string $path): void
    {
        $this->client()->getObject([
            'Bucket' => $this->bucket(),
            'Key' => $key,
            'SaveAs' => $path,
        ]);
    }

    public function delete(string $key): void
    {
        $this->client()->deleteObject(['Bucket' => $this->bucket(), 'Key' => $key]);
    }

    public function temporaryUrl(string $key, string $filename): string
    {
        $minutes = max(1, (int) $this->config->get('pterodactyl.database_backups.download_link_lifetime', 15));
        $filename = Str::slug(pathinfo($filename, PATHINFO_FILENAME), '_') ?: 'backup';

        $command = $this->client()->getCommand('GetObject', [
            'Bucket' => $this->bucket(),
            'Key' => $key,
            'ResponseContentDisposition' => sprintf('attachment; filename="%s.sql.gz"', $filename),
        ]);

        return (string) $this->client()->createPresignedRequest($command, "+$minutes minutes")->getUri();
    }

    /**
     * Writes and removes a small object to make sure the configured credentials work.
     */
    public function test(): void
    {
        $prefix = trim((string) $this->settings()['prefix'], '/');
        $key = ($prefix === '' ? '' : "$prefix/") . '.pterodactyl-connection-test-' . Str::random(8);

        $this->client()->putObject(['Bucket' => $this->bucket(), 'Key' => $key, 'Body' => 'ok']);
        $this->client()->deleteObject(['Bucket' => $this->bucket(), 'Key' => $key]);
    }

    protected function settings(): array
    {
        return array_merge([
            'endpoint' => null,
            'region' => null,
            'bucket' => null,
            'prefix' => null,
            'key' => null,
            'secret' => null,
            'use_path_style_endpoint' => false,
        ], (array) $this->config->get('pterodactyl.database_backups.s3', []));
    }
}
