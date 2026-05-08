<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sp_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_role', 100)->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedSmallInteger('max_sessions')->default(0); // 0 = unlimited
            $table->enum('enforcement_mode', ['block_new', 'logout_oldest', 'logout_all'])->default('logout_oldest');
            $table->unsignedInteger('idle_timeout_seconds')->default(0); // 0 = disabled
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            // A rule applies to either a role OR a user, not both
            $table->unique(['user_role', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sp_rules');
    }
};
