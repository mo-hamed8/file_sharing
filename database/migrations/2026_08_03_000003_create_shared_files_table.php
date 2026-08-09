<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_files', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->nullable()->constrained('participants')->nullOnDelete();
            $table->string('original_name', 255);
            $table->string('stored_name', 255);
            $table->string('storage_disk', 50);
            $table->string('storage_path', 500);
            $table->string('mime_type', 150);
            $table->string('extension', 20);
            $table->unsignedBigInteger('size');
            $table->string('status', 20)->default('active');
            $table->timestamp('uploaded_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'status']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_files');
    }
};
