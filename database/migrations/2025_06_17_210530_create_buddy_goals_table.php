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
        Schema::create('buddy_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('buddy_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['PENDING', 'ACCEPTED', 'REJECTED'])->default('PENDING');
            $table->enum('role', ['VIEWER', 'CONTRIBUTOR', 'COLLABORATOR'])->default('VIEWER');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            // Ensure a buddy can't be invited to the same goal twice
            $table->unique(['goal_id', 'buddy_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buddy_goals');
    }
};
