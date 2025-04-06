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
        Schema::create('merchant_item', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_id');
            $table->unsignedBigInteger('item_id');
            $table->timestamps();

            $table->foreign('merchant_id')->references('ulid')->on('merchants')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            
            $table->unique(['merchant_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_item');
    }
};