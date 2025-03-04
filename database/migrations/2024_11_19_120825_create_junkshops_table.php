<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJunkshopsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::dropIfExists('junkshops');
        Schema::create('junkshops', function (Blueprint $table) {
            $table->id();
            $table->string('ulid')->unique();
            $table->string('name');
            $table->string('contact');
            $table->text('description')->nullable();
            $table->string('address');
            $table->string('user_id');
            $table->foreign('user_id')->references('ulid')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('junkshops');
    }
}
