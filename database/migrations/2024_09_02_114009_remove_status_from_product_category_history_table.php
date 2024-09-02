<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveStatusFromProductCategoryHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_category_history', function (Blueprint $table) {
            $table->dropColumn('status'); // Usunięcie kolumny 'status'
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_category_history', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active'); // Dodanie kolumny 'status' przy rollbacku
        });
    }
}
