<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCodeToDiscountCodesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('discount_codes', function (Blueprint $table) {
            // Dodajemy kolumnę 'code' jako nullable
            $table->string('code')->nullable()->after('id');
        });

        // Uzupełniamy istniejące rekordy unikalnymi kodami
        $discountCodes = \App\Models\DiscountCode::whereNull('code')->get();

        foreach ($discountCodes as $discountCode) {
            // Generujemy unikalny kod
            do {
                $plainCode = Str::upper(Str::random(8));
                $exists = \App\Models\DiscountCode::where('code', $plainCode)->exists();
            } while ($exists);

            // Aktualizujemy rekord
            $discountCode->code = $plainCode;
            $discountCode->save();
        }

        // Ustawiamy kolumnę 'code' jako niepustą i dodajemy indeks unikalny
        Schema::table('discount_codes', function (Blueprint $table) {
            $table->string('code')->nullable(false)->change();
            $table->unique('code');
        });
    }

    public function down()
    {
        Schema::table('discount_codes', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });
    }
}
