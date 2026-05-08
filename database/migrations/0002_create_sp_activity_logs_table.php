<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sp_activity_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('action_type', 100)->index();
            $table->text('description')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->enum('severity', ['info', 'warning', 'critical'])->default('info')->index();
            $table->dateTime('timestamp')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sp_activity_logs');
    }
};
