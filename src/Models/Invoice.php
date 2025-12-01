<?php

namespace Structurize\Peppol\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table;
    protected $connection = 'mysql';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = \Config::get('peppol.tables.invoices', 'invoices');
        $this->connection = \Config::get('peppol.database_connection', 'mysql');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, \Config::get('peppol.table-fields.invoices.company_id', 'company_id'));
    }
}