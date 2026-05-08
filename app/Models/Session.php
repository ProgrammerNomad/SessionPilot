<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    protected $table = 'sp_sessions';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'token', 'device_id', 'ip_address', 'user_agent',
        'browser', 'browser_version', 'os', 'device_type',
        'created_at', 'last_activity', 'expires_at', 'logged_out_at',
    ];

    protected $casts = [
        'created_at'    => 'datetime',
        'last_activity' => 'datetime',
        'expires_at'    => 'datetime',
        'logged_out_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('logged_out_at')
                     ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function isActive(): bool
    {
        return $this->logged_out_at === null
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
