<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('session_token_hash', 64)->unique();
            $table->string('display_name', 50);
            $table->string('role', 20)->default('participant');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('joined_at');
            $table->timestamps();

            $table->index(['room_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
