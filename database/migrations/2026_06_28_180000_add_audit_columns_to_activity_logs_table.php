<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extends activity_logs for enterprise audit trail metadata.
 *
 * Business rules:
 * - user_role and department_id snapshot actor context at log time.
 * - entity_type and entity_id identify the affected record for compliance queries.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('user_role', 20)->nullable()->after('user_id');
            $table->foreignId('department_id')
                ->nullable()
                ->after('user_role')
                ->constrained()
                ->nullOnDelete();
            $table->string('entity_type', 100)->nullable()->after('module');
            $table->unsignedBigInteger('entity_id')->nullable()->after('entity_type');

            $table->index('user_role');
            $table->index('department_id');
            $table->index(['entity_type', 'entity_id']);
            $table->index(['module', 'action']);
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['entity_type', 'entity_id']);
            $table->dropIndex(['module', 'action']);
            $table->dropForeign(['department_id']);
            $table->dropColumn([
                'user_role',
                'department_id',
                'entity_type',
                'entity_id',
            ]);
        });
    }
};
