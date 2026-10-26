<?php

namespace Database\Factories;

use Ramsey\Uuid\Uuid;
use Carbon\CarbonImmutable;
use Pterodactyl\Models\DatabaseBackup;
use Illuminate\Database\Eloquent\Factories\Factory;

class DatabaseBackupFactory extends Factory
{
    protected $model = DatabaseBackup::class;

    public function definition(): array
    {
        $uuid = Uuid::uuid4()->toString();

        return [
            'uuid' => $uuid,
            'name' => $this->faker->words(3, true),
            'status' => DatabaseBackup::STATUS_COMPLETED,
            'object_key' => "database-backups/test/$uuid.sql.gz",
            'bytes' => 1024,
            'checksum' => hash('sha256', $uuid),
            'completed_at' => CarbonImmutable::now(),
        ];
    }
}
