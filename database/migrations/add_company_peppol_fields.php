<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(config('peppol.tables.companies', 'companies'), function (Blueprint $table) {
            $table->boolean(config('peppol.table-fields.companies.peppol_connected', 'peppol_connected'))->default(0);
            $table->string(config('peppol.table-fields.companies.peppol_scheme_id', 'peppol_scheme_id'))->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(config('peppol.tables.companies', 'companies'), function (Blueprint $table) {
            $table->dropColumn(config('peppol.table-fields.companies.peppol_connected', 'peppol_connected'));
            $table->dropColumn(config('peppol.table-fields.companies.peppol_scheme_id', 'peppol_scheme_id'));
        });
    }
};