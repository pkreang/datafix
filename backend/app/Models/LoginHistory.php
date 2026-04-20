<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginHistory extends Model
{
    public const UPDATED_AT = null;   // only created_at tracked

    protected $table = 'login_history';

    protected $fillable = [
        'user_id',
        'email',
        'auth_provider',
        'ip_address',
        'user_agent',
        'result',
        'failure_reason',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
