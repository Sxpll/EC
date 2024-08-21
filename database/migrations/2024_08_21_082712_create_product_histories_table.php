<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('product_histories', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('admin_id');
        $table->string('admin_name');
        $table->string('action');
        $table->unsignedBigInteger('product_id');
        $table->string('field');
        $table->text('old_value')->nullable();
        $table->text('new_value')->nullable();
        $table->timestamps();

        // Dodanie klucza obcego
        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
    });
}

};
