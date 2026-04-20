<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'submission_activity_log';

    protected $fillable = [
        'submission_id',
        'user_id',
        'action',
        'meta',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(DocumentFormSubmission::class, 'submission_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a new activity row. Intentionally forgiving — logging failures must
     * never block the primary action, so we swallow exceptions in production.
     */
    public static function record(int $submissionId, ?int $userId, string $action, array $meta = []): void
    {
        try {
            static::create([
                'submission_id' => $submissionId,
                'user_id' => $userId,
                'action' => $action,
                'meta' => $meta ?: null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            if (app()->environment('testing', 'local')) {
                throw $e;
            }
        }
    }
}
