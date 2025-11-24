<?php
namespace Structurize\Peppol\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table;
    protected $connection = 'mysql';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('peppol.tables.companies', 'companies');
        $this->connection = config('peppol.database_connection', 'mysql');
    }
}