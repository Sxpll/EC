<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatMessagesTable extends Migration
{
    public function up()
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_id');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // Dodanie user_id
            $table->text('message');
            $table->boolean('is_read')->default(false); // Dodanie is_read
            $table->timestamps();

            $table->foreign('chat_id')->references('id')->on('user_chats')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Klucz obcy do users
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_messages');
    }
}
