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
        Schema::create('outgoing_letters', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationship
            |--------------------------------------------------------------------------
            */

            $table->foreignId('letter_number_registration_id')
                ->constrained('letter_number_registrations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Archive Information
            |--------------------------------------------------------------------------
            */

            $table->enum('letter_type', [
                'top_secret',
                'secret',
                'restricted',
                'public',
                'urgent',
                'immediate',
                'important',
                'regular',
            ]);

            $table->string('attachment')
                ->nullable()
                ->comment('Contoh: 2 Berkas');

            $table->string('file_path')
                ->nullable()
                ->comment('Lokasi file PDF Surat');

            $table->enum('status', [
                'active',
                'inactive',
            ])->default('active');

            $table->text('notes')->nullable();

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
            | Laravel
            |--------------------------------------------------------------------------
            */

            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Index
            |--------------------------------------------------------------------------
            */

            $table->index('letter_number_registration_id');
            $table->index('letter_type');
            $table->index('status');
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('deleted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outgoing_letters');
    }
};