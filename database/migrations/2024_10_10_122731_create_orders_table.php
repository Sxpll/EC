<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->decimal('total', 10, 2);
            $table->string('status')->default('pending');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->text('customer_address');
            $table->string('pickup_code', 6)->nullable();
            $table->unsignedBigInteger('discount_code_id')->nullable();
            $table->decimal('discount_amount', 8, 2)->default(0);
            $table->unsignedBigInteger('status_id')->default(1);
            $table->timestamps();

            // Klucze obce
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('discount_code_id')->references('id')->on('discount_codes')->onDelete('set null');
            $table->foreign('status_id')->references('id')->on('order_statuses')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
