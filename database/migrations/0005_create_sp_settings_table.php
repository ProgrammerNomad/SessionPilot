<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sp_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('setting_key', 100)->unique();
            $table->longText('setting_value')->nullable();
            $table->string('autoload', 3)->default('yes');
            $table->timestamps();
        });

        // Insert default settings
        $now = current_time('mysql');
        global $wpdb;
        $defaults = [
            ['setting_key' => 'log_retention_days',       'setting_value' => '90',       'autoload' => 'yes'],
            ['setting_key' => 'session_retention_days',   'setting_value' => '30',       'autoload' => 'yes'],
            ['setting_key' => 'idle_timeout_seconds',     'setting_value' => '1800',     'autoload' => 'yes'],  // 30 min
            ['setting_key' => 'heartbeat_interval',       'setting_value' => '30',       'autoload' => 'yes'],  // seconds
            ['setting_key' => 'heartbeat_grace_period',   'setting_value' => '120',      'autoload' => 'yes'],  // seconds
            ['setting_key' => 'anonymize_ip',             'setting_value' => '0',        'autoload' => 'yes'],
            ['setting_key' => 'alert_email',              'setting_value' => '',         'autoload' => 'yes'],
            ['setting_key' => 'alert_on_limit_exceeded',  'setting_value' => '1',        'autoload' => 'yes'],
            ['setting_key' => 'alert_on_login_failures',  'setting_value' => '1',        'autoload' => 'yes'],
            ['setting_key' => 'login_failure_threshold',  'setting_value' => '5',        'autoload' => 'yes'],
        ];

        foreach ($defaults as $row) {
            $wpdb->insert(
                $wpdb->prefix . 'sp_settings',
                array_merge($row, ['created_at' => $now, 'updated_at' => $now]),
                ['%s', '%s', '%s', '%s', '%s']
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sp_settings');
    }
};
