<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doc_keys', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('company_id');
            $table->string('doc_key');
            $table->string('issuer_cnpj');
            $table->string('issuer_name')->nullable();
            $table->string('issuer_state')->nullable();
            $table->string('recipient_cnpj')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_state')->nullable();
            $table->string('document_template');
            $table->string('grade_series');
            $table->string('note_number');
            $table->dateTime('issue_date');
            $table->float('amount')->nullable();
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
        Schema::dropIfExists('doc_keys');
    }
}
