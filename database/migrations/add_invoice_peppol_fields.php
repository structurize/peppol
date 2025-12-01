<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class add_invoice_peppol_fields extends Migration {
    public function up()
    {
        $tableName = \Config::get('peppol.tables.invoices', 'invoices');
        $booleanField = \Config::get('peppol.table-fields.invoices.peppol_sent', 'peppol_sent');
        $datetimeField = \Config::get('peppol.table-fields.invoices.peppol_sent_at', 'peppol_sent_at');

        Schema::table($tableName, function (Blueprint $table) use ($tableName, $booleanField, $datetimeField) {
            if(!Schema::hasColumn($tableName, $booleanField)) {
                $table->boolean($booleanField)->default(0);
            }
            if(!Schema::hasColumn($tableName, $datetimeField)) {
                $table->datetime($datetimeField)->nullable();
            }
        });
    }

    public function down()
    {
        $tableName = \Config::get('peppol.tables.invoices', 'invoices');
        $booleanField = \Config::get('peppol.table-fields.invoices.peppol_sent', 'peppol_sent');
        $datetimeField = \Config::get('peppol.table-fields.invoices.peppol_sent_at', 'peppol_sent_at');

        Schema::table($tableName, function (Blueprint $table) use ($tableName, $booleanField, $datetimeField) {
            if(Schema::hasColumn($tableName, $booleanField)) {
                $table->dropColumn($booleanField);
            }
            if(Schema::hasColumn($tableName, $datetimeField)) {
                $table->dropColumn($datetimeField);
            }
        });
    }
};