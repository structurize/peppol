<?php

namespace Structurize\Peppol\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('peppol.tables.invoices', 'invoices');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, config('peppol.table-fields.invoices.company_id', 'company_id'));
    }
}