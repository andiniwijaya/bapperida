<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replace single-column unique indexes with composite unique (+ deleted_at) for soft-delete restore.
     */
    public function up(): void
    {
        $legacyIndexes = [
            'lnr_letter_number_unique',
            'lnr_seq_year_unique',
            'letter_number_registrations_letter_number_unique',
            'letter_number_registrations_sequence_number_year_unique',
        ];

        foreach ($legacyIndexes as $indexName) {
            $this->dropUniqueIndexIfExists('letter_number_registrations', $indexName);
        }

        if (! $this->indexExists('letter_number_registrations', 'lnr_ln_deleted_uq')) {
            Schema::table('letter_number_registrations', function (Blueprint $table) {
                $table->unique(['letter_number', 'deleted_at'], 'lnr_ln_deleted_uq');
            });
        }

        if (! $this->indexExists('letter_number_registrations', 'lnr_seq_year_deleted_uq')) {
            Schema::table('letter_number_registrations', function (Blueprint $table) {
                $table->unique(['sequence_number', 'year', 'deleted_at'], 'lnr_seq_year_deleted_uq');
            });
        }
    }

    /**
     * Restore single-column / two-column unique indexes without deleted_at.
     */
    public function down(): void
    {
        Schema::table('letter_number_registrations', function (Blueprint $table) {
            $table->dropUnique('lnr_ln_deleted_uq');
            $table->dropUnique('lnr_seq_year_deleted_uq');
        });

        Schema::table('letter_number_registrations', function (Blueprint $table) {
            $table->unique('letter_number', 'lnr_letter_number_unique');
            $table->unique(['sequence_number', 'year'], 'lnr_seq_year_unique');
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
