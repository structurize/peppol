<?php

namespace Structurize\Peppol\Models;

use Illuminate\Database\Eloquent\Model;

class PeppolLogging extends Model
{
    protected $table;

    protected $fillable = [
        'invoice_id',
        'send_data',
        'success',
        'return_data',
    ];
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('peppol.tables.invoice_logging', 'peppol_invoice_logging');
    }
}