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
        Schema::table('discount_codes', function (Blueprint $table) {
            $table->boolean('is_single_use')->default(true);
        });
    }

    public function down()
    {
        Schema::table('discount_codes', function (Blueprint $table) {
            $table->dropColumn('is_single_use');
        });
    }
};
