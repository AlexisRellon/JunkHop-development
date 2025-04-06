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
        Schema::create('merchant_item_interests', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_id');
            $table->unsignedBigInteger('item_id');
            $table->integer('quantity')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('merchant_id')->references('ulid')->on('merchants')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            
            // Prevent duplicate entries
            $table->unique(['merchant_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_item_interests');
    }
};