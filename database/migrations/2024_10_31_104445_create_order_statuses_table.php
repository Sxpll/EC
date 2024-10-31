<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\OrderStatus;

class CreateOrderStatusesTable extends Migration
{
    public function up()
    {
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->timestamps();
        });

        // Dodanie domyślnych statusów po utworzeniu tabeli
        OrderStatus::insert([
            ['name' => 'W realizacji', 'code' => 'in_progress'],
            ['name' => 'W drodze', 'code' => 'on_the_way'],
            ['name' => 'Dostarczono', 'code' => 'delivered'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('order_statuses');
    }
}
