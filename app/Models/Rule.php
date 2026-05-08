<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Models;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    protected $table = 'sp_rules';

    protected $fillable = [
        'user_role', 'user_id', 'max_sessions', 'enforcement_mode', 'idle_timeout_seconds', 'is_active',
    ];

    protected $casts = [
        'max_sessions'          => 'integer',
        'idle_timeout_seconds'  => 'integer',
        'is_active'             => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForRole($query, string $role)
    {
        return $query->where('user_role', $role)->whereNull('user_id');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId)->whereNull('user_role');
    }
}
