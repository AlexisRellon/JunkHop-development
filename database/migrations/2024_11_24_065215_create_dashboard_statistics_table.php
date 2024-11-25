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
        Schema::create('dashboard_statistics', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->integer('total_users');
            $table->integer('online_users');
            $table->integer('total_junkshops');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_statistics');
    }
};
