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
        Schema::table('bids', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('expiry_date');
            $table->date('end_date')->nullable()->after('start_date');
            $table->decimal('starting_bid', 10, 2)->nullable()->after('price_per_kg');
            $table->decimal('current_bid', 10, 2)->nullable()->after('starting_bid');
            $table->string('current_bidder_id')->nullable()->after('current_bid');
            $table->boolean('is_bidding_enabled')->default(false)->after('is_bulk_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bids', function (Blueprint $table) {
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
            $table->dropColumn('starting_bid');
            $table->dropColumn('current_bid');
            $table->dropColumn('current_bidder_id');
            $table->dropColumn('is_bidding_enabled');
        });
    }
};
