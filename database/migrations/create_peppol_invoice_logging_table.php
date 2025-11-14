<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tableName = config('peppol.tables.invoice_logging', 'peppol_invoice_logging');
        if(Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->boolean('success')->default(0);
            $table->unsignedBigInteger('invoice_id')->default(0);
            $table->text('send_data')->nullable();
            $table->text('return_data')->nullable();
            $table->timestamps();

            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('peppol.tables.invoice_logging', 'peppol_invoice_logging'));
    }
};