<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sp_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('token', 255)->unique();
            $table->unsignedBigInteger('device_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('user_agent', 255)->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('browser_version', 50)->nullable();
            $table->string('os', 100)->nullable();
            $table->string('device_type', 20)->nullable(); // desktop|mobile|tablet
            $table->dateTime('created_at')->index();
            $table->dateTime('last_activity')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('logged_out_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sp_sessions');
    }
};
