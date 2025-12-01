<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class add_company_peppol_fields extends Migration {
    public function up()
    {
        $tableName = config('peppol.tables.companies', 'companies');
        $booleanField = config('peppol.table-fields.companies.peppol_connected', 'peppol_connected');
        $stringField = config('peppol.table-fields.companies.peppol_scheme_id', 'peppol_scheme_id');

        Schema::table($tableName, function (Blueprint $table) use ($tableName,$booleanField, $stringField) {
            if(!Schema::hasColumn($tableName, $booleanField)) {
                $table->boolean($booleanField)->default(0);
            }
            if(!Schema::hasColumn($tableName, $stringField)) {
                $table->string($stringField)->nullable();
            }
        });
    }

    public function down()
    {
        $tableName = config('peppol.tables.companies', 'companies');
        $booleanField = config('peppol.table-fields.companies.peppol_connected', 'peppol_connected');
        $stringField = config('peppol.table-fields.companies.peppol_scheme_id', 'peppol_scheme_id');

        Schema::table($tableName, function (Blueprint $table) use ($tableName,$booleanField, $stringField) {
            if(Schema::hasColumn($tableName, $booleanField)) {
                $table->dropColumn($booleanField);
            }
            if(Schema::hasColumn($tableName, $stringField)) {
                $table->dropColumn($stringField);
            }
        });
    }
};