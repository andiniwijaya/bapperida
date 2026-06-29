<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing records with capitalized status to lowercase
        DB::table('letter_number_registrations')
            ->where('status', 'Active')
            ->update(['status' => 'active']);

        DB::table('letter_number_registrations')
            ->where('status', 'Cancelled')
            ->update(['status' => 'inactive']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to capitalized status
        DB::table('letter_number_registrations')
            ->where('status', 'active')
            ->update(['status' => 'Active']);

        DB::table('letter_number_registrations')
            ->where('status', 'inactive')
            ->update(['status' => 'Cancelled']);
    }
};
