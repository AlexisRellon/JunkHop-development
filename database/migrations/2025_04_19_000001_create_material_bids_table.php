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
        Schema::create('material_bids', function (Blueprint $table) {
            $table->id();
            $table->string('ulid')->unique();
            $table->string('wanted_material_id');
            $table->string('junkshop_id');
            $table->decimal('offered_price', 10, 2);
            $table->decimal('offered_quantity', 10, 2);
            $table->string('grade')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'expired'])->default('pending');
            $table->text('message')->nullable();
            $table->timestamp('expiry_date')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('wanted_material_id')->references('ulid')->on('wanted_materials')->onDelete('cascade');
            $table->foreign('junkshop_id')->references('ulid')->on('junkshops')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_bids');
    }
};
