<?php
namespace Structurize\Peppol\Services;

use Structurize\Structurize\Bricks\PeppolDocuments;
use Structurize\Structurize\Bricks\PeppolParticipants;
use Structurize\Structurize\Bricks\PeppolRegister;
use Structurize\Structurize\Bricks\PeppolSend;
use Structurize\Structurize\Bricks\PeppolUnRegister;
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

    public function validateApiKey(?string $apiKey = null): bool
    {
        $_ENV['STRUCTURIZE_API_KEY'] = $apiKey;
        $user = $this->getUser();
        if(array_key_exists('message', $user) && $user['message'] === 'Unauthenticated.') {
            return false;
        }

        return true;
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
        return (array)(new PeppolSend($filename, $stream))->run('true')->output;
    }
    public function getSupportedDocuments($identifier)
    {
        return (array)(new PeppolDocuments($identifier))->run('true')->output;
    }

    public function getParticipant($identifier)
    {
        return (array)(new PeppolParticipants($identifier))->run('true')->output;
    }

    public function registerParticipant($data)
    {
        return (array)(new PeppolRegister($data['email'], $data['firstname'], $data['lastname'], $data['identifier']))->run('true')->output;
    }

    public function unregisterParticipant($identifier)
    {
        return (array)(new PeppolUnRegister($identifier))->run('true')->output;
    }
    private function init() : void
    {
        if (!is_null($this->apikey)) {
            return;
        }

        $api_key = config('peppol.api_key');
        if(config('peppol.multi_tenant.enabled')){
            $tenant_model = config('peppol.multi_tenant.tenant_model');
            $tenant_attribute = config('peppol.multi_tenant.tenant_attribute');
            $api_key = app($tenant_model)->$tenant_attribute();
        }
        $this->apikey = $api_key;
        $_ENV['STRUCTURIZE_API_KEY'] = $this->apikey;

    }
}
