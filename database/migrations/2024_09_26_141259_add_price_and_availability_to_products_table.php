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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 8, 2); // Cena z dokładnością do dwóch miejsc po przecinku
            $table->enum('availability', ['available', 'available_in_7_days', 'available_in_14_days', 'unavailable'])->default('available');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price', 'availability']);
        });
    }
};
