<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(config('peppol.tables.invoices', 'invoices'), function (Blueprint $table) {
            $table->boolean(config('peppol.table-fields.invoices.peppol_sent', 'peppol_sent'))->default(0);
            $table->datetime(config('peppol.table-fields.invoices.peppol_sent_at', 'peppol_sent_at'))->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(config('peppol.tables.invoices', 'invoices'), function (Blueprint $table) {
            $table->dropColumn(config('peppol.table-fields.invoices.peppol_sent', 'peppol_sent'));
            $table->dropColumn(config('peppol.table-fields.invoices.peppol_sent_at', 'peppol_sent_at'));
        });
    }
};