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
        Schema::create('bid_histories', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('bid_id')->constrained('bids');            $table->string('merchant_id');
            $table->decimal('bid_amount', 10, 2);
            $table->timestamp('bid_time');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Add foreign key constraint to merchants table
            $table->foreign('merchant_id')->references('ulid')->on('merchants');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bid_histories');
    }
};
