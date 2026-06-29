<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replace single-column unique indexes with composite unique (code/name + deleted_at).
     */
    public function up(): void
    {
        $this->dropUniqueIndexIfExists('departments', 'dept_code_unique');
        $this->dropUniqueIndexIfExists('departments', 'departments_code_unique');

        Schema::table('departments', function (Blueprint $table) {
            $table->unique(['code', 'deleted_at'], 'dept_code_deleted_uq');
            $table->unique(['name', 'deleted_at'], 'dept_name_deleted_uq');
        });
    }

    /**
     * Restore single-column code unique indexes.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropUnique('dept_code_deleted_uq');
            $table->dropUnique('dept_name_deleted_uq');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->unique('code', 'dept_code_unique');
        });
    }

    /**
     * Drop a unique index only when it exists (supports legacy auto-generated names).
     */
    private function dropUniqueIndexIfExists(string $table, string $indexName): void
    {
        if (! $this->indexExists($table, $indexName)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");

            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
            $blueprint->dropUnique($indexName);
        });
    }

    /**
     * Check index existence using driver-specific metadata.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $database = Schema::getConnection()->getDatabaseName();

            return DB::table('information_schema.statistics')
                ->where('table_schema', $database)
                ->where('table_name', $table)
                ->where('index_name', $indexName)
                ->exists();
        }

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");

            foreach ($indexes as $index) {
                if (($index->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }
};
