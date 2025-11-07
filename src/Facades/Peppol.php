<?php

namespace Structurize\Peppol\Facades;

use Illuminate\Support\Facades\Facade;
use Structurize\Peppol\Services\PeppolService;

class Peppol extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PeppolService::class;
    }
}
