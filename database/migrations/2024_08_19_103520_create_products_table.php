<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->unsignedBigInteger('category_id')->nullable(); // Zmienione na nullable
            $table->boolean('isActive')->default(true);
            $table->decimal('price', 8, 2)->nullable(); // Cena z dokładnością do dwóch miejsc po przecinku
            $table->enum('availability', ['available', 'available_in_7_days', 'available_in_14_days', 'unavailable'])->default('available');
            $table->timestamps();

            // Relacja do tabeli kategorii
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}
