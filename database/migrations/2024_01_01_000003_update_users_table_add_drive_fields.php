<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'user'])->default('user')->after('password');
            $table->decimal('storage_limit', 10, 2)->nullable()->default(10)->after('role')->comment('Storage limit in MB');
            $table->decimal('used_storage', 10, 2)->default(0)->after('storage_limit')->comment('Used storage in MB');
            $table->boolean('is_blocked')->default(false)->after('used_storage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'storage_limit', 'used_storage', 'is_blocked']);
        });
    }
};
