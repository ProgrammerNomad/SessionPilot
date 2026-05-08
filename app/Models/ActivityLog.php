<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'sp_activity_logs';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'action_type', 'description', 'ip', 'user_agent', 'severity', 'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];
}
