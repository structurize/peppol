<?php

namespace Structurize\Peppol\Services;

use Illuminate\Support\Facades\Log;
use Structurize\Peppol\Models\Company;
use Structurize\Peppol\Models\Invoice;
use Structurize\Peppol\Models\PeppolLogging;

class PeppolService
{
    private StructurizeService $structurizeService;

    public function __construct(StructurizeService $structurizeService)
    {
        $this->structurizeService = $structurizeService;
    }

    public function sendInvoice($invoice)
    {
        $answer = $this->getSendUbl($invoice);
        if ($answer['answer']->status == 'RECIPIENT_NOT_IN_PEPPOL') {
            $answer = $this->getSendUbl($invoice, '0208');
        }
        return $answer;
    }

    public function sendUbl($stream, $filename, $invoice)
    {
        $company_id               = config('peppol.table-fields.companies.id', 'id');
        $invoice_id               = config('peppol.table-fields.invoices.id', 'id');
        $invoice_peppol_sent      = config('peppol.table-fields.invoices.peppol_sent', 'peppol_sent');
        $invoice_peppol_sent_at   = config('peppol.table-fields.invoices.peppol_sent_at', 'peppol_sent_at');
        $invoice_company_id       = config('peppol.table-fields.invoices.company_id', 'company_id');
        $company_peppol_connected = config('peppol.table-fields.companies.peppol_connected', 'peppol_connected');
        $company_peppol_scheme_id = config('peppol.table-fields.companies.peppol_scheme_id', 'peppol_scheme_id');

        if ($this->canPeppol($invoice->{$invoice_id})) {
            $answer = $this->structurizeService->sendUblDocument($filename, $stream);
            PeppolLogging::create([
                'invoice_id' => $invoice->{$invoice_id},
                'send_data' => $stream,
                'success' => $answer['success'],
                'return_data' => json_encode($answer['answer']) ?? null
            ]);

            if ($answer['success']) {
                Invoice::where($invoice_id, $invoice->{$invoice_id})->update([$invoice_peppol_sent => 1, $invoice_peppol_sent_at => date('Y-m-d H:i:s')]);
            } elseif ($answer['answer']->status != 'RECIPIENT_NOT_IN_PEPPOL') {
                Log::error('Peppol send error for invoice ' . $invoice->{$invoice_id} . ': ' . json_encode($answer['answer']));
            } else {
                Company::where($company_id, $invoice->{$invoice_company_id})->update([$company_peppol_connected => 0, $company_peppol_scheme_id => '']);
            }

            return $answer;
        }
        return ['success' => false, 'message' => 'Peppol not active'];
    }

    public function canPeppol($invoice_id)
    {
        if (!is_null($invoice_id)) {
            $company_peppol_connected = config('peppol.table-fields.companies.peppol_connected', 'peppol_connected');
            $company_peppol_scheme_id = config('peppol.table-fields.companies.peppol_scheme_id', 'peppol_scheme_id');
            $company_vat_number = config('peppol.table-fields.companies.vat_number', 'vat_number');

            $invoice = Invoice::find($invoice_id);
            if (!is_null($invoice) && !is_null($invoice->company)) {
                if (!is_null($invoice->company->{$company_peppol_connected}) && $invoice->company->{$company_peppol_scheme_id}) {
                    return true;
                }
                return $this->checkIdentifiers($invoice->company->{$company_vat_number}, $invoice->company);
            }
        }
    }

    public function checkIdentifiers($vat, $firma = null)
    {
        $identifiers = $this->getPEPPOLIdentifiers($vat);
        if (sizeof($identifiers)) {
            foreach ($identifiers as $identifier) {
                $can_send = $this->canSendInvoices($identifier, $firma);
                if ($can_send) {
                    return true;
                }
            }
        }
        return false;
    }

    private function canSendInvoices($identifier, $company = null): bool
    {
        $company_id = config('peppol.table-fields.companies.id', 'id');
        $company_peppol_connected = config('peppol.table-fields.companies.peppol_connected', 'peppol_connected');
        $company_peppol_scheme_id = config('peppol.table-fields.companies.peppol_scheme_id', 'peppol_scheme_id');

        $answer = $this->structurizeService->getSupportedDocuments($identifier);
        if (filled($answer) && isset($answer['documentTypes'])) {
            foreach ($answer['documentTypes'] as $documentType) {
                if (str_contains($documentType, 'Invoice')) {
                    if (!is_null($company)) {
                        Company::where($company_id, $company->{$company_id})->update([$company_peppol_connected => 1, $company_peppol_scheme_id => ($company->{$company_peppol_scheme_id} != '' ? $company->{$company_peppol_scheme_id} : $answer['peppolIdentifier'])], false);
                    }
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param mixed $invoice
     * @return array
     */
    private function getSendUbl(mixed $invoice, $identifier_overrule = null): array
    {
        $invoice_number = config('peppol.table-fields.invoices.number', 'number');
        $general_invoice_service = app(config('peppol.services.general-invoice', GeneralInvoiceService::class));
        $ubl                     = $general_invoice_service->generate($invoice, $identifier_overrule);
        $filename                = 'UBL_' . $invoice->{$invoice_number} . '.xml';

        $answer = $this->sendUbl($ubl, $filename, $invoice);
        return $answer;
    }

    public function getPEPPOLIdentifiers($vatNumber, $only_scheme = false)
    {
        $vatNumber     = strtoupper($vatNumber);
        $vatNumber     = str_replace('.', '', str_replace(" ", "", $vatNumber));
        $identifiers   = [];
        $vatIdentifier = $this->getVATPEPPOLIdentifier($vatNumber, $only_scheme);
        if ($vatIdentifier != null) {
            $identifiers[] = $vatIdentifier;
        }
        $otherIdentifier = $this->getOtherPEPPOLIdentifier($vatNumber, $only_scheme);
        if ($otherIdentifier != null) {
            $identifiers[] = $otherIdentifier;
        }
        return $identifiers;
    }

    private function getOtherPEPPOLIdentifier($vatNumber, $only_scheme = false)
    {
        if (preg_match("/^BE\d{10}$/", $vatNumber)) {
            return $only_scheme ? "0208" : "0208:" . substr($vatNumber, 2, 10);
        } else {
            return null;
        }
    }

    private function getVATPEPPOLIdentifier($vatNumber, $only_scheme = false)
    {
        $schemeId = $this->getPEPPOLSchemeId($vatNumber);
        if ($schemeId != null) {
            return $only_scheme ? $schemeId : $schemeId . ":" . strtolower($vatNumber);
        } else {
            return null;
        }
    }

    private function getPEPPOLSchemeId($vatNumber)
    {
        $countryCode = substr($vatNumber, 0, 2);
        switch ($countryCode) {
            case "AD":
                return "9922";
            case "AL":
                return "9923";
            case "BA":
                return "9924";
            case "BE":
                return "9925";
            case "BG":
                return "9926";
            case "CH":
                return "9927";
            case "CY":
                return "9928";
            case "CZ":
                return "9929";
            case "DE":
                return "9930";
            case "EE":
                return "9931";
            case "GB":
                return "9932";
            case "GR":
                return "9933";
            case "HR":
                return "9934";
            case "IE":
                return "9935";
            case "LI":
                return "9936";
            case "LT":
                return "9937";
            case "LU":
                return "9938";
            case "LV":
                return "9939";
            case "MC":
                return "9940";
            case "ME":
                return "9941";
            case "MK":
                return "9942";
            case "MT":
                return "9943";
            case "NL":
                return "9944";
            case "PO":
                return "9945";
            case "PT":
                return "9946";
            case "RO":
                return "9947";
            case "RS":
                return "9948";
            case "SI":
                return "9949";
            case "SK":
                return "9950";
            case "SM":
                return "9951";
            case "TR":
                return "9952";
            case "VA":
                return "9953";
            case "SE":
                return "9955";
            case "FR":
                return "9957";
            default:
                return null;
        }
    }

}