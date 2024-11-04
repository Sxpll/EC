<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountCodeUserTable extends Migration
{
    public function up()
    {
        Schema::create('discount_code_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('discount_code_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('discount_code_id')->references('id')->on('discount_codes')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['discount_code_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('discount_code_user');
    }
}
