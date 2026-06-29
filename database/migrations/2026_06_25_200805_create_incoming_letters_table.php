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
        Schema::create('incoming_letters', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Letter Information
            |--------------------------------------------------------------------------
            */

            $table->string('letter_number');

            $table->date('sent_date');

            $table->date('received_date');

            $table->date('disposition_date')
                ->nullable();

            $table->string('sender');

            $table->foreignId('department_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('disposition_department_id')
                ->nullable()
                ->constrained('departments')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('subject');

            $table->string('agenda_name')
                ->nullable();

            $table->text('summary')
                ->nullable();

            $table->enum('letter_attribute', [
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
                ->comment('Contoh: 3 Berkas');

            $table->string('file_path')
                ->nullable()
                ->comment('Lokasi file PDF surat masuk');

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'active',
                'inactive',
            ])->default('active');

            $table->text('notes')
                ->nullable();

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

            $table->index('department_id');
            $table->index('disposition_department_id');
            $table->index('letter_attribute');
            $table->index('status');
            $table->index('created_by');
            $table->index('received_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_letters');
    }
};