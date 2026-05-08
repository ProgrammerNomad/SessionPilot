<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sp_devices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('device_name', 100)->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('browser_version', 50)->nullable();
            $table->string('os', 100)->nullable();
            $table->string('device_type', 20)->nullable(); // desktop|mobile|tablet
            $table->string('last_ip', 45)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('last_seen')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sp_devices');
    }
};
