<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserChatsTable extends Migration
{
    public function up()
    {
        Schema::table('user_chats', function (Blueprint $table) {
            $table->boolean('is_taken')->default(false);
        });
    }

    public function down()
    {
        Schema::table('user_chats', function (Blueprint $table) {
            $table->dropColumn('is_taken');
        });
    }
}
