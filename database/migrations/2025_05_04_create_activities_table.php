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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable()->index(); // Using ulid
            $table->string('type'); // e.g., 'user', 'junkshop', 'merchant', 'transaction'
            $table->string('subject_type')->nullable(); // Polymorphic relation
            $table->string('subject_id')->nullable(); // Using ulid
            $table->string('action'); // e.g., 'created', 'updated', 'deleted'
            $table->text('description');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            // Index for polymorphic relation
            $table->index(['subject_type', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
