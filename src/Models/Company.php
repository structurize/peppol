<?php
namespace Structurize\Peppol\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('peppol.tables.companies', 'companies');
    }
}