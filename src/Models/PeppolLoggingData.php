<?php

namespace Structurize\Peppol\Models;

use Illuminate\Database\Eloquent\Model;

class PeppolLoggingData extends Model
{
    protected $table;
    protected $connection = 'mysql';

    protected $fillable = [
        'peppol_invoice_logging_id',
        'send_data',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = \Config::get('peppol.tables.invoice_logging_data', 'peppol_invoice_logging_data');
        $this->connection = \Config::get('peppol.database_connection', 'mysql');
    }

    public function logging()
    {
        return $this->belongsTo(PeppolLogging::class, 'peppol_invoice_logging_id');
    }
}
