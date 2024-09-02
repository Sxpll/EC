<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPathToProductCategoryHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_category_history', function (Blueprint $table) {
            $table->string('path')->nullable()->after('category_id'); // Dodaje kolumnę 'path' po 'category_id'
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
            $table->dropColumn('path'); // Usuwa kolumnę 'path' przy rollbacku
        });
    }
}
