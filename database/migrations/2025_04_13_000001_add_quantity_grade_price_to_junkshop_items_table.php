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
        Schema::table('junkshop_items', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->default(0);
            $table->string('grade')->nullable();
            $table->decimal('price', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('junkshop_items', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'grade', 'price']);
        });
    }
};
