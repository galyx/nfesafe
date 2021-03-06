<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_companies', function (Blueprint $table) {
            $table->id();
            $table->string('cnpj');
            $table->string('corporate_name');
            $table->string('fantasy_name');
            $table->string('address');
            $table->string('number');
            $table->string('adrres2');
            $table->string('city');
            $table->string('state');
            $table->string('post_code');
            $table->string('ie');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_companies');
    }
}
