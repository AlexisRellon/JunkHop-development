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
    {        // Add inventory tracking fields to junkshop_items table
        Schema::table('items', function (Blueprint $table) {
            // Add quantity field if it doesn't exist
            if (!Schema::hasColumn('items', 'quantity')) {
                $table->decimal('quantity', 10, 2)->default(0)->after('name');
            }
            
            // Add grade field if it doesn't exist
            if (!Schema::hasColumn('items', 'grade')) {
                $table->string('grade')->nullable()->after('quantity');
            }
            
            // Add availability status field
            if (!Schema::hasColumn('items', 'is_available')) {
                $table->boolean('is_available')->default(true)->after('grade');
            }
            
            // Add last_updated field for inventory tracking
            if (!Schema::hasColumn('items', 'inventory_updated_at')) {
                $table->timestamp('inventory_updated_at')->nullable()->after('is_available');
            }
        });

        // Create merchant notification preferences table
        Schema::create('merchant_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_id');
            $table->boolean('notify_on_inventory_update')->default(true);
            $table->boolean('notify_on_price_change')->default(true);
            $table->boolean('notify_on_bid_response')->default(true);
            $table->boolean('notify_on_wanted_material_match')->default(true);
            $table->json('interested_item_ids')->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('merchant_id')->references('ulid')->on('merchants')->onDelete('cascade');
        });

        // Create inventory update history table
        Schema::create('inventory_updates', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('item_id')->constrained();
            $table->decimal('previous_quantity', 10, 2)->nullable();
            $table->decimal('new_quantity', 10, 2);
            $table->decimal('previous_price', 10, 2)->nullable();
            $table->decimal('new_price', 10, 2)->nullable();
            $table->string('update_type'); // add, remove, price_change, etc.
            $table->string('source')->nullable(); // bid, manual, transaction, etc.
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new tables
        Schema::dropIfExists('inventory_updates');
        Schema::dropIfExists('merchant_notification_preferences');

        // Remove the columns added to items table
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'quantity')) {
                $table->dropColumn('quantity');
            }
            
            if (Schema::hasColumn('items', 'grade')) {
                $table->dropColumn('grade');
            }
            
            if (Schema::hasColumn('items', 'is_available')) {
                $table->dropColumn('is_available');
            }
            
            if (Schema::hasColumn('items', 'inventory_updated_at')) {
                $table->dropColumn('inventory_updated_at');
            }
        });
    }
};
