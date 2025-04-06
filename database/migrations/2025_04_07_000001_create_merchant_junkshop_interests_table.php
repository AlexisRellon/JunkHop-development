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
        Schema::create('merchant_junkshop_interests', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_id');
            $table->string('junkshop_id');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('merchant_id')->references('ulid')->on('merchants')->onDelete('cascade');
            $table->foreign('junkshop_id')->references('ulid')->on('junkshops')->onDelete('cascade');
            
            // Prevent duplicate entries
            $table->unique(['merchant_id', 'junkshop_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_junkshop_interests');
    }
};