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
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
