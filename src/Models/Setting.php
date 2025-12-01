<?php
namespace Structurize\Peppol\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $connection = 'mysql';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = \Config::get('peppol.database_connection', 'mysql');
    }
    public function structurizeApiKey()
    {
        return new Attribute(
            function () {
                return $this->structurize_api_key;
            }
        );
    }
}