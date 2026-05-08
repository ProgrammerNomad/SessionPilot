<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $table = 'sp_devices';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'device_name', 'browser', 'browser_version',
        'os', 'device_type', 'last_ip', 'created_at', 'last_seen',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'last_seen'  => 'datetime',
    ];
}
