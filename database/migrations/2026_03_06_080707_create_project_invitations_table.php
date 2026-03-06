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
        Schema::create('project_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('email');
            $table->foreignId('role_id')->constrained('roles');
            $table->string('token', 64);
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->foreignId('invited_by')->constrained('users');
            $table->timestamp('expires_at');
            $table->timestamps();

            // Index for faster lookups when checking an invite via email
            $table->index(['email', 'token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_invitations');
    }
};
