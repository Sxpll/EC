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
    Schema::create('user_histories', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('admin_id');
        $table->unsignedBigInteger('user_id');
        $table->string('action'); // add, update, delete
        $table->json('changes'); // JSON column to store changes
        $table->timestamps();

        // Foreign keys
        $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
}

};
