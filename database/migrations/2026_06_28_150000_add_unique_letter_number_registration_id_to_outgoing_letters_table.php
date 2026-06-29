<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enforce one outgoing letter per letter number registration.
     */
    public function up(): void
    {
        Schema::table('outgoing_letters', function (Blueprint $table) {
            $table->unique('letter_number_registration_id', 'outg_lnr_id_unique');
        });
    }

    /**
     * Remove outgoing letter registration uniqueness constraint.
     */
    public function down(): void
    {
        Schema::table('outgoing_letters', function (Blueprint $table) {
            $table->dropUnique('outg_lnr_id_unique');
        });
    }
};
