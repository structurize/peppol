<?php
namespace Structurize\Peppol\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public function structurizeApiKey(): Attribute
    {
        return new Attribute(
            get: fn() => $this->structurize_api_key,
        );
    }
}