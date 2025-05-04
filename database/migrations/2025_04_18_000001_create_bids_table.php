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
    {        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->string('merchant_id')->nullable();
            $table->string('junkshop_id');
            $table->foreignId('item_id')->constrained();
            $table->decimal('quantity', 10, 2);
            $table->decimal('price_per_kg', 10, 2);
            $table->text('notes')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'expired', 'completed'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_bulk_order')->default(false);
            $table->timestamps();

            // Foreign keys
            $table->foreign('merchant_id')->references('ulid')->on('merchants')->onDelete('cascade');
            $table->foreign('junkshop_id')->references('ulid')->on('junkshops')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bids');
    }
};
