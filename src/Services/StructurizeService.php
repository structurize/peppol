<?php
namespace Structurize\Peppol\Services;

use Structurize\Structurize\Bricks\PeppolDocuments;
use Structurize\Structurize\Bricks\PeppolSend;
use Structurize\Structurize\Bricks\UblFromInvoice;
use \Structurize\Structurize\User;

class StructurizeService
{
    public ?string $apikey = null;
    public function __construct(){}
    public function getApiKey() : ?string
    {
        $this->init();
        return $this->apikey;
    }
    public function getUser() : array
    {
        $this->init();
        if (!empty($this->apikey)) {
            $user = new User();
            $info = $user->info();
            if (!is_null($info) && !is_string($info)) {
                return get_object_vars($info);
            }
        }
        return [];
    }
    public function makeUblDocument($invoice)
    {
        $this->init();
        return (new UblFromInvoice($invoice))->run('true')->output;
    }
    public function sendUblDocument($filename, $stream) : array
    {
        $this->init();
        return (array)(new PeppolSend(filename: $filename, ubl: $stream))->run('true')->output;
    }
    public function getSupportedDocuments($identifier)
    {
        return (array)(new PeppolDocuments($identifier))->run('true')->output;
    }
    private function init() : void
    {
        if (!is_null($this->apikey)) {
            return;
        }

        $this->apikey = config('peppol.api_key');
        $_ENV['STRUCTURIZE_API_KEY'] = $this->apikey;


    }
}
