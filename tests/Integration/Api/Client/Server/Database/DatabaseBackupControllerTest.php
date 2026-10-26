<?php

namespace Pterodactyl\Tests\Integration\Api\Client\Server\Database;

use Illuminate\Http\Response;
use Pterodactyl\Models\Subuser;
use Pterodactyl\Models\Database;
use Pterodactyl\Models\Permission;
use Illuminate\Support\Facades\Bus;
use Pterodactyl\Models\DatabaseHost;
use Pterodactyl\Models\DatabaseBackup;
use PHPUnit\Framework\Attributes\DataProvider;
use Pterodactyl\Contracts\Extensions\HashidsInterface;
use Pterodactyl\Jobs\DatabaseBackup\CreateDatabaseBackupJob;
use Pterodactyl\Jobs\DatabaseBackup\RestoreDatabaseBackupJob;
use Pterodactyl\Services\Databases\Backups\DatabaseBackupStorage;
use Pterodactyl\Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class DatabaseBackupControllerTest extends ClientApiIntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Bus::fake([CreateDatabaseBackupJob::class, RestoreDatabaseBackupJob::class]);

        config()->set('pterodactyl.database_backups', [
            'enabled' => true,
            'limit' => 2,
            'timeout' => 3600,
            'download_link_lifetime' => 15,
            's3' => [
                'endpoint' => 'http://127.0.0.1:9000',
                'region' => 'us-east-1',
                'bucket' => 'backups',
                'prefix' => 'db',
                'key' => 'key',
                'secret' => 'secret',
                'use_path_style_endpoint' => true,
            ],
        ]);
    }

    public function testBackupsCanBeListed(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $database = $this->createDatabase($server->id);
        $backup = DatabaseBackup::factory()->create(['database_id' => $database->id]);

        $this->actingAs($user)->getJson($this->endpoint($server, $database))
            ->assertOk()
            ->assertJsonPath('meta.enabled', true)
            ->assertJsonPath('meta.limit', 2)
            ->assertJsonPath('data.0.object', DatabaseBackup::RESOURCE_NAME)
            ->assertJsonPath('data.0.attributes.uuid', $backup->uuid)
            ->assertJsonMissingPath('data.0.attributes.object_key');
    }

    public function testBackupCanBeCreated(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $database = $this->createDatabase($server->id);

        $response = $this->actingAs($user)->postJson($this->endpoint($server, $database), ['name' => 'Before update'])
            ->assertOk()
            ->assertJsonPath('attributes.name', 'Before update')
            ->assertJsonPath('attributes.status', DatabaseBackup::STATUS_PENDING);

        $backup = DatabaseBackup::query()->where('uuid', $response->json('attributes.uuid'))->firstOrFail();
        $this->assertSame($database->id, $backup->database_id);

        Bus::assertDispatched(CreateDatabaseBackupJob::class, fn (CreateDatabaseBackupJob $job) => $job->backup->is($backup));
        $this->assertActivityFor('server:database.backup.create', $user, [$database, $backup]);
    }

    public function testBackupCannotBeCreatedWhenDisabled(): void
    {
        config()->set('pterodactyl.database_backups.enabled', false);

        [$user, $server] = $this->generateTestAccount();
        $database = $this->createDatabase($server->id);

        $this->actingAs($user)->postJson($this->endpoint($server, $database))
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonPath('errors.0.detail', 'Database backups are not enabled on this Panel.');

        Bus::assertNotDispatched(CreateDatabaseBackupJob::class);
    }

    public function testBackupLimitIsEnforcedIgnoringFailedBackups(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $database = $this->createDatabase($server->id);

        DatabaseBackup::factory()->create(['database_id' => $database->id]);
        DatabaseBackup::factory()->create(['database_id' => $database->id, 'status' => DatabaseBackup::STATUS_FAILED]);

        $this->actingAs($user)->postJson($this->endpoint($server, $database))->assertOk();

        $this->actingAs($user)->postJson($this->endpoint($server, $database))
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonPath('errors.0.detail', 'Another backup or restore is already running for this database.');

        DatabaseBackup::query()->where('status', DatabaseBackup::STATUS_PENDING)->update(['status' => DatabaseBackup::STATUS_COMPLETED]);

        $this->actingAs($user)->postJson($this->endpoint($server, $database))
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonPath('errors.0.detail', 'This database already has the maximum of 2 backups, delete an existing backup first.');
    }

    public function testStalePendingBackupsAreMarkedAsFailed(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $database = $this->createDatabase($server->id);
        $backup = DatabaseBackup::factory()->create([
            'database_id' => $database->id,
            'status' => DatabaseBackup::STATUS_PENDING,
            'created_at' => now()->subHours(3),
        ]);

        $this->actingAs($user)->getJson($this->endpoint($server, $database))
            ->assertOk()
            ->assertJsonPath('data.0.attributes.status', DatabaseBackup::STATUS_FAILED);

        $this->assertSame(DatabaseBackup::STATUS_FAILED, $backup->refresh()->status);
    }

    public function testBackupCanBeRestored(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $database = $this->createDatabase($server->id);
        $backup = DatabaseBackup::factory()->create(['database_id' => $database->id]);

        $this->actingAs($user)->postJson($this->endpoint($server, $database, "/$backup->uuid/restore"))
            ->assertStatus(Response::HTTP_ACCEPTED);

        $this->assertSame(DatabaseBackup::RESTORE_RUNNING, $backup->refresh()->restore_status);
        Bus::assertDispatched(RestoreDatabaseBackupJob::class);

        // A second restore cannot be started while the first one is running.
        $this->actingAs($user)->postJson($this->endpoint($server, $database, "/$backup->uuid/restore"))
            ->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testIncompleteBackupCannotBeRestoredOrDownloaded(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $database = $this->createDatabase($server->id);
        $backup = DatabaseBackup::factory()->create(['database_id' => $database->id, 'status' => DatabaseBackup::STATUS_FAILED, 'object_key' => null]);

        $this->actingAs($user)->postJson($this->endpoint($server, $database, "/$backup->uuid/restore"))->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->actingAs($user)->getJson($this->endpoint($server, $database, "/$backup->uuid/download"))->assertStatus(Response::HTTP_BAD_REQUEST);

        Bus::assertNotDispatched(RestoreDatabaseBackupJob::class);
    }

    public function testDownloadReturnsSignedUrl(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $database = $this->createDatabase($server->id);
        $backup = DatabaseBackup::factory()->create(['database_id' => $database->id]);

        $response = $this->actingAs($user)->getJson($this->endpoint($server, $database, "/$backup->uuid/download"))
            ->assertOk()
            ->assertJsonPath('object', 'signed_url');

        $url = $response->json('attributes.url');
        $this->assertStringStartsWith('http://127.0.0.1:9000/backups/' . $backup->object_key, $url);
        $this->assertStringContainsString('X-Amz-Signature=', $url);
    }

    public function testBackupCanBeDeleted(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $database = $this->createDatabase($server->id);
        $backup = DatabaseBackup::factory()->create(['database_id' => $database->id]);

        $this->mock(DatabaseBackupStorage::class, function ($mock) use ($backup) {
            $mock->expects('delete')->with($backup->object_key);
        });

        $this->actingAs($user)->deleteJson($this->endpoint($server, $database, "/$backup->uuid"))
            ->assertNoContent();

        $this->assertModelMissing($backup);
    }

    public function testPendingBackupCannotBeDeleted(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $database = $this->createDatabase($server->id);
        $backup = DatabaseBackup::factory()->create(['database_id' => $database->id, 'status' => DatabaseBackup::STATUS_PENDING]);

        $this->actingAs($user)->deleteJson($this->endpoint($server, $database, "/$backup->uuid"))
            ->assertStatus(Response::HTTP_BAD_REQUEST);

        $this->assertModelExists($backup);
    }

    #[DataProvider('permissionDataProvider')]
    public function testSubuserPermissionsAreEnforced(string $method, string $path, string $permission): void
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_DATABASE_READ]);
        $database = $this->createDatabase($server->id);
        $backup = DatabaseBackup::factory()->create(['database_id' => $database->id]);

        $this->actingAs($user)->json($method, $this->endpoint($server, $database, str_replace('{backup}', $backup->uuid, $path)))
            ->assertForbidden();

        $user->subuserOf()->where('server_id', $server->id)->update(['permissions' => [$permission]]);

        $this->mock(DatabaseBackupStorage::class, function ($mock) {
            $mock->allows(['isEnabled' => true, 'limit' => 0, 'delete' => null, 'temporaryUrl' => 'https://example.com/file']);
        });

        $this->actingAs($user->refresh())->json($method, $this->endpoint($server, $database, str_replace('{backup}', $backup->uuid, $path)))
            ->assertSuccessful();
    }

    public static function permissionDataProvider(): array
    {
        return [
            ['GET', '', Permission::ACTION_DATABASE_BACKUP],
            ['POST', '', Permission::ACTION_DATABASE_BACKUP],
            ['GET', '/{backup}/download', Permission::ACTION_DATABASE_BACKUP],
            ['POST', '/{backup}/restore', Permission::ACTION_DATABASE_BACKUP_RESTORE],
            ['DELETE', '/{backup}', Permission::ACTION_DATABASE_BACKUP_DELETE],
        ];
    }

    public function testBackupsOfOtherServersOrDatabasesCannotBeAccessed(): void
    {
        [$user, $server1] = $this->generateTestAccount();
        $server2 = $this->createServerModel();
        Subuser::factory()->create(['server_id' => $server2->id, 'user_id' => $user->id, 'permissions' => [Permission::ACTION_DATABASE_BACKUP]]);
        $server3 = $this->createServerModel();

        $database1 = $this->createDatabase($server1->id);
        $database1b = $this->createDatabase($server1->id);
        $database2 = $this->createDatabase($server2->id);
        $database3 = $this->createDatabase($server3->id);

        $backup1 = DatabaseBackup::factory()->create(['database_id' => $database1->id]);
        $backup3 = DatabaseBackup::factory()->create(['database_id' => $database3->id]);

        $this->actingAs($user)->getJson($this->endpoint($server1, $database1, "/$backup1->uuid/download"))->assertOk();

        // Backup exists but belongs to a different database of the same server.
        $this->actingAs($user)->getJson($this->endpoint($server1, $database1b, "/$backup1->uuid/download"))->assertNotFound();
        // Database of another server, accessed through a server the user owns.
        $this->actingAs($user)->getJson($this->endpoint($server1, $database2))->assertNotFound();
        $this->actingAs($user)->getJson($this->endpoint($server2, $database2, "/$backup1->uuid/download"))->assertNotFound();
        $this->actingAs($user)->getJson($this->endpoint($server1, $database1, "/$backup3->uuid/download"))->assertNotFound();
        $this->actingAs($user)->getJson($this->endpoint($server3, $database3, "/$backup3->uuid/download"))->assertNotFound();
    }

    private function createDatabase(int $serverId): Database
    {
        $host = DatabaseHost::factory()->create();

        return Database::factory()->create(['server_id' => $serverId, 'database_host_id' => $host->id]);
    }

    private function endpoint($server, Database $database, string $path = ''): string
    {
        $hashids = $this->app->make(HashidsInterface::class);

        return $this->link($server, '/databases/' . $hashids->encode($database->id) . '/backups' . $path);
    }
}
