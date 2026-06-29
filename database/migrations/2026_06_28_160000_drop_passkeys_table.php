<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove passkeys table — application does not use WebAuthn/passkey authentication.
     */
    public function up(): void
    {
        Schema::dropIfExists('passkeys');
    }

    /**
     * Restore passkeys table structure for rollback only.
     */
    public function down(): void
    {
        Schema::create('passkeys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('credential');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }
};
