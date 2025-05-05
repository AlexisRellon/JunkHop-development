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
        // Create quality standards table
        Schema::create('quality_standards', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('item_id')->constrained();
            $table->string('grade');
            $table->text('description');
            $table->json('criteria')->nullable();
            $table->decimal('minimum_purity', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create verifications table
        Schema::create('quality_verifications', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->string('merchant_id');
            $table->string('junkshop_id');
            $table->foreignId('item_id')->constrained();
            $table->string('verification_code')->unique();
            $table->enum('status', ['pending', 'passed', 'failed', 'in_progress'])->default('pending');
            $table->decimal('quantity', 10, 2);
            $table->string('grade')->nullable();
            $table->decimal('purity_level', 5, 2)->nullable();
            $table->foreignId('bid_id')->nullable();
            $table->text('notes')->nullable();
            $table->json('verification_results')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('verified_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('merchant_id')->references('ulid')->on('merchants')->onDelete('cascade');
            $table->foreign('junkshop_id')->references('ulid')->on('junkshops')->onDelete('cascade');
        });

        // Create verification images table for images
        Schema::create('verification_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quality_verification_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->string('image_type'); // 'before', 'after', 'material', 'equipment'
            $table->text('caption')->nullable();
            $table->timestamps();
        });

        // Create verification methods table
        Schema::create('verification_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('equipment_required')->nullable();
            $table->json('steps')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create item verification methods pivot table
        Schema::create('item_verification_method', function (Blueprint $table) {
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->foreignId('verification_method_id')->constrained()->onDelete('cascade');
            $table->primary(['item_id', 'verification_method_id']);
        });

        // Create verification history table
        Schema::create('verification_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quality_verification_id')->constrained()->onDelete('cascade');
            $table->string('action');
            $table->text('details')->nullable();
            $table->string('performed_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_history');
        Schema::dropIfExists('item_verification_method');
        Schema::dropIfExists('verification_methods');
        Schema::dropIfExists('verification_images');
        Schema::dropIfExists('quality_verifications');
        Schema::dropIfExists('quality_standards');
    }
};
