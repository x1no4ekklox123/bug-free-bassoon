<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $uuid
 * @property int $database_id
 * @property string $name
 * @property string $status
 * @property string|null $restore_status
 * @property string|null $object_key
 * @property int $bytes
 * @property string|null $checksum
 * @property string|null $error
 * @property \Carbon\CarbonImmutable|null $completed_at
 * @property \Carbon\CarbonImmutable|null $restored_at
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 * @property Database $database
 */
class DatabaseBackup extends Model
{
    use HasFactory;

    public const RESOURCE_NAME = 'database_backup';

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public const RESTORE_RUNNING = 'restoring';
    public const RESTORE_COMPLETED = 'completed';
    public const RESTORE_FAILED = 'failed';

    protected $table = 'database_backups';

    protected bool $immutableDates = true;

    protected $casts = [
        'id' => 'int',
        'database_id' => 'int',
        'bytes' => 'int',
        'completed_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'restore_status' => null,
        'bytes' => 0,
    ];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public static array $validationRules = [
        'uuid' => 'required|uuid',
        'database_id' => 'bail|required|numeric|exists:databases,id',
        'name' => 'required|string|max:191',
        'status' => 'required|string|in:pending,completed,failed',
        'restore_status' => 'nullable|string|in:restoring,completed,failed',
        'object_key' => 'nullable|string|max:191',
        'bytes' => 'numeric',
        'checksum' => 'nullable|string|max:191',
        'error' => 'nullable|string',
    ];

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isRestoring(): bool
    {
        return $this->restore_status === self::RESTORE_RUNNING;
    }

    public function database(): BelongsTo
    {
        return $this->belongsTo(Database::class);
    }
}
