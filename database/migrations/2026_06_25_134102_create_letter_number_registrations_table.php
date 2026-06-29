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
        Schema::create('letter_number_registrations', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Letter Information
            |--------------------------------------------------------------------------
            */

            $table->string('index_code', 50);

            $table->string('letter_code', 50);

            $table->unsignedBigInteger('sequence_number');

            $table->year('year');

            $table->string('letter_number');

            $table->string('subject');

            $table->text('summary')->nullable();

            $table->string('recipient');

            $table->date('letter_date');

            $table->enum('letter_type', [
                'top_secret',   // Sangat Rahasia
                'secret',       // Rahasia
                'restricted',   // Terbatas
                'public',       // Biasa/Terbuka
                'urgent',       // Amat Segera/Kilat
                'immediate',    // Segera
                'important',    // Penting
                'regular',      // Biasa
            ])->default('regular');

            $table->string('attachment')->nullable();

            $table->text('notes')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'active',
                'inactive',
            ])->default('active');

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('department_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Audit Trail
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Laravel Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->unique('letter_number', 'lnr_letter_number_unique');

            $table->unique([
                'sequence_number',
                'year',
            ], 'lnr_seq_year_unique');

            $table->index('index_code');

            $table->index('letter_code');

            $table->index('year');

            $table->index('letter_date');

            $table->index('letter_type');

            $table->index('status');

            $table->index('department_id');

            $table->index('recipient');

            $table->index('subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letter_number_registrations');
    }
};