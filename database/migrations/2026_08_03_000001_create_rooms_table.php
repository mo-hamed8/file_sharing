<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('code', 8)->unique();
            $table->string('name', 100)->nullable();
            $table->string('host_token_hash', 64);
            $table->string('status', 20)->default('active');
            $table->timestamp('expires_at');
            $table->boolean('allow_participant_uploads')->default(true);
            $table->unsignedBigInteger('maximum_file_size')->nullable();
            $table->unsignedInteger('maximum_files')->nullable();
            $table->unsignedBigInteger('total_storage_limit')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
