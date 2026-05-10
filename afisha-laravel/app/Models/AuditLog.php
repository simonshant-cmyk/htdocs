<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table      = 'audit_logs';
    protected $primaryKey = 'log_id';
    public    $timestamps = false;

    protected $fillable = [
        'actor_id', 'actor_role', 'action',
        'target_type', 'target_id', 'details', 'created_at',
    ];

    protected $casts = ['details' => 'array'];

    public static function write(string $action, string $targetType, int $targetId, ?int $actorId, ?int $actorRole, array $details = []): void
    {
        static::create([
            'actor_id'    => $actorId,
            'actor_role'  => $actorRole,
            'action'      => $action,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'details'     => $details ?: null,
            'created_at'  => now(),
        ]);
    }
}
