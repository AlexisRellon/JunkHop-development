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
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->string('business_name');
            $table->string('contact');
            $table->string('address');
            $table->text('description')->nullable();
            $table->string('user_id');  // Foreign key to users table
            $table->foreign('user_id')->references('ulid')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};