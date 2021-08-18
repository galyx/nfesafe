<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocXmlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doc_xmls', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('company_id');
            $table->string('doc_key');
            $table->string('nsu');
            $table->string('event_type')->nullable();
            $table->string('event_sequence')->nullable();
            $table->string('note_status')->nullable();
            $table->string('reason')->nullable();
            $table->string('protocol_number');
            $table->string('document_template');
            $table->string('grade_series');
            $table->string('note_number');
            $table->date('issue_date');
            $table->date('receipt_date')->nullable();
            $table->string('organ_code')->nullable();
            $table->string('issuer_cnpj');
            $table->string('issuer_name')->nullable();
            $table->string('issuer_state')->nullable();
            $table->string('recipient_cnpj')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_state')->nullable();
            $table->string('app_version')->nullable();
            $table->text('event_description')->nullable();
            $table->string('amount')->nullable();
            $table->dateTime('event_time_date')->nullable();
            $table->longText('xml_received')->nullable();
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
        Schema::dropIfExists('doc_xmls');
    }
}
