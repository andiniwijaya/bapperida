<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enforce unique letter_number per active row; allow restore via composite with deleted_at.
     */
    public function up(): void
    {
        Schema::table('incoming_letters', function (Blueprint $table) {
            $table->unique(['letter_number', 'deleted_at'], 'incl_ln_deleted_uq');
        });
    }

    /**
     * Remove composite unique on letter_number and deleted_at.
     */
    public function down(): void
    {
        Schema::table('incoming_letters', function (Blueprint $table) {
            $table->dropUnique('incl_ln_deleted_uq');
        });
    }
};
