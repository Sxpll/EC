<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductAttachmentsTable extends Migration
{
    public function up()
    {
        Schema::create('product_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->binary('file_data'); // Przechowywanie pliku binarnego
            $table->string('mime_type'); // Typ MIME (np. application/pdf)
            $table->string('file_name'); // Nazwa pliku (np. instrukcja.pdf)
            $table->timestamps();

            // Relacja z tabelą products
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_attachments');
    }
}
