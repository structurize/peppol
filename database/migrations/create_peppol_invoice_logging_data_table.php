<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class create_peppol_invoice_logging_data_table extends Migration {
    public function up()
    {
        $tableName = \Config::get('peppol.tables.invoice_logging_data', 'peppol_invoice_logging_data');
              
        if(Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('peppol_invoice_logging_id');
            $table->longText('send_data')->nullable();
            $table->timestamps();        
            
            $table->index('peppol_invoice_logging_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists(\Config::get('peppol.tables.invoice_logging_data', 'peppol_invoice_logging_data'));
    }
};