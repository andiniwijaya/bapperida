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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Informasi Instansi
            |--------------------------------------------------------------------------
            */

            $table->string('institution_name');
            $table->string('institution_short_name')->nullable();

            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code', 10)->nullable();

            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('website', 255)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Branding
            |--------------------------------------------------------------------------
            */

            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Kepala Instansi
            |--------------------------------------------------------------------------
            */

            $table->string('head_of_agency')->nullable();
            $table->string('head_position')->nullable();
            $table->string('head_nip', 30)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Template Surat
            |--------------------------------------------------------------------------
            */

            $table->string('letter_number_template')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Pengaturan Aplikasi
            |--------------------------------------------------------------------------
            */

            $table->string('timezone')
                ->default('Asia/Jakarta');

            $table->string('locale', 10)
                ->default('id');

            $table->boolean('dark_mode_default')
                ->default(false);

            /*
            |--------------------------------------------------------------------------
            | Footer
            |--------------------------------------------------------------------------
            */

            $table->text('copyright')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};