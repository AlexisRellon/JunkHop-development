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
        Schema::create('wanted_materials', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->string('merchant_id');
            $table->foreignId('item_id')->constrained();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('desired_price', 10, 2)->default(0);
            $table->string('grade')->nullable();
            $table->text('description')->nullable();
            $table->date('deadline')->nullable();
            $table->boolean('is_public')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign keys
            $table->foreign('merchant_id')->references('ulid')->on('merchants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wanted_materials');
    }
};
