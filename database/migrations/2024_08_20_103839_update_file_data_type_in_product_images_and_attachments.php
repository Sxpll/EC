<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFileDataTypeInProductImagesAndAttachments extends Migration
{
    public function up()
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->binary('file_data')->change();
        });

        Schema::table('product_attachments', function (Blueprint $table) {
            $table->binary('file_data')->change();
        });
    }

    public function down()
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->text('file_data')->change(); // Przywróć, jeśli wcześniej był text
        });

        Schema::table('product_attachments', function (Blueprint $table) {
            $table->text('file_data')->change(); // Przywróć, jeśli wcześniej był text
        });
    }
}
