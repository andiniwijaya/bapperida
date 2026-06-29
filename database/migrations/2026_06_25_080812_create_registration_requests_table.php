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
        Schema::create('registration_requests', function (Blueprint $table) {

            $table->id();

            // User yang mendaftar
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Status approval
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
            ])->default('pending');

            // Super Admin yang approve
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Waktu approve
            $table->timestamp('approved_at')
                ->nullable();

            // Alasan ditolak
            $table->text('rejection_reason')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_requests');
    }
};