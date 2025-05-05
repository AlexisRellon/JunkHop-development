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
        // Create bid subscriptions table for recurring bids
        Schema::create('bid_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->string('merchant_id');
            $table->string('junkshop_id');
            $table->foreignId('item_id')->constrained();
            $table->decimal('quantity', 10, 2);
            $table->decimal('price_per_kg', 10, 2);
            $table->string('frequency'); // 'weekly', 'biweekly', 'monthly'
            $table->date('next_renewal_date');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('max_renewals')->nullable();
            $table->integer('renewals_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->json('renewal_settings')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('merchant_id')->references('ulid')->on('merchants')->onDelete('cascade');
            $table->foreign('junkshop_id')->references('ulid')->on('junkshops')->onDelete('cascade');
        });

        // Create a table to track bid renewals
        Schema::create('bid_renewal_history', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('bid_subscription_id')->constrained();
            $table->string('new_bid_ulid')->nullable();
            $table->date('renewal_date');
            $table->string('status'); // 'success', 'failed', 'skipped'
            $table->text('status_details')->nullable();
            $table->json('renewal_data')->nullable();
            $table->timestamps();
        });

        // Add auto_renewal fields to the bids table
        Schema::table('bids', function (Blueprint $table) {
            $table->boolean('allow_auto_renewal')->default(false)->after('is_bulk_order');
            $table->foreignId('bid_subscription_id')->nullable()->after('allow_auto_renewal');
            $table->dateTime('auto_renewed_at')->nullable()->after('bid_subscription_id');
        });

        // Create merchant subscription preferences table
        Schema::create('merchant_subscription_preferences', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_id');
            $table->boolean('default_allow_auto_renewal')->default(false);
            $table->decimal('max_price_increase_percent', 5, 2)->default(5.00);
            $table->boolean('notify_before_renewal')->default(true);
            $table->integer('renewal_notification_days')->default(3);
            $table->json('renewal_settings')->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('merchant_id')->references('ulid')->on('merchants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove foreign key constraints first
        Schema::table('bids', function (Blueprint $table) {
            $table->dropColumn(['allow_auto_renewal', 'bid_subscription_id', 'auto_renewed_at']);
        });

        // Drop the tables
        Schema::dropIfExists('bid_renewal_history');
        Schema::dropIfExists('bid_subscriptions');
        Schema::dropIfExists('merchant_subscription_preferences');
    }
};
