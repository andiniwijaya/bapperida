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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // User yang melakukan aktivitas
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Jenis aktivitas
            $table->string('action', 50);

            // Nama modul
            $table->string('module', 100);

            // Deskripsi aktivitas
            $table->longText('description');

            // URL yang diakses
            $table->string('url')->nullable();

            // HTTP Method
            $table->string('method', 10)->nullable();

            // Informasi perangkat
            $table->ipAddress('ip_address')->nullable();
            $table->string('browser')->nullable();
            $table->string('platform')->nullable();
            $table->string('device')->nullable();
            $table->text('user_agent')->nullable();

            // Data tambahan
            $table->json('properties')->nullable();

            // Waktu aktivitas terjadi
            $table->timestamp('logged_at')->useCurrent();

            $table->timestamps();

            // Index
            $table->index('user_id');
            $table->index('action');
            $table->index('module');
            $table->index('logged_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};