<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only audit row for admin-side configuration changes.
 *
 * Sister to SubmissionActivityLog (which tracks per-document events). This
 * one tracks settings, workflow, permission and document-type changes —
 * captured by Eloquent observers in app/Observers/.
 *
 * `record()` swallows exceptions in production identical to
 * SubmissionActivityLog: a failed audit write must NEVER block the primary
 * action. Re-thrown in testing/local for visibility.
 */
class SystemChangeLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'system_change_log';

    protected $fillable = [
        'actor_user_id',
        'entity_type',
        'entity_id',
        'action',
        'changed_fields',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'changed_fields' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    /**
     * @param  array<string, array{from: mixed, to: mixed}>  $changedFields
     */
    public static function record(
        string $entityType,
        ?string $entityId,
        string $action,
        array $changedFields = [],
    ): void {
        try {
            $actorId = (int) (session('user.id') ?? 0) ?: null;
            $request = request();
            static::create([
                'actor_user_id' => $actorId,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'action' => $action,
                'changed_fields' => $changedFields ?: null,
                'ip_address' => $request?->ip(),
                'user_agent' => mb_substr((string) $request?->userAgent(), 0, 512),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            if (app()->environment('testing', 'local')) {
                throw $e;
            }
        }
    }
}
