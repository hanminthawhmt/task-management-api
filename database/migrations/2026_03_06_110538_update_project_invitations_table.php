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
        Schema::table('project_invitations', function (Blueprint $table) {
            $table->uuid('token')->unique()->change();
            $table->enum('status', ['pending', 'accepted', 'declined', 'expired', 'cancelled'])
                ->default('pending')
                ->change();
            $table->timestamp('accepted_at')->nullable()->after('invited_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_invitations', function (Blueprint $table) {
            $table->string('token', 64)->unique(false)->change();
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending')->change();
            $table->dropColumn('accepted_at');
        });
    }
};
