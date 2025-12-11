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
        // Ensure roles table exists before adding FK
        if (! Schema::hasTable('roles')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role_id')) {
                $table->uuid('role_id')->nullable()->after('password');
            }

            // Add foreign key constraint if not exists
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            // Drop foreign if exists
            try {
                $table->dropForeign(['role_id']);
            } catch (\Exception $e) {
                // ignore
            }
        });
    }
};
