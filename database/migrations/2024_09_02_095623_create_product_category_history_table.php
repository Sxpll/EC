<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductCategoryHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_category_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Klucz obcy do tabeli produktów
            $table->foreignId('category_id')->constrained()->onDelete('cascade'); // Klucz obcy do tabeli kategorii
            $table->enum('status', ['active', 'inactive'])->default('active'); // Status przypisania kategorii
            $table->timestamp('assigned_at')->useCurrent(); // Data przypisania kategorii
            $table->timestamp('removed_at')->nullable(); // Data usunięcia lub deaktywacji kategorii
            $table->timestamps(); // Standardowe znaczniki czasu
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_category_history');
    }
}
