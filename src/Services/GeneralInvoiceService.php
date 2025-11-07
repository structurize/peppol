<?php
namespace Structurize\Peppol\Services;

use Structurize\Structurize\Generator\Invoice;
use Structurize\Structurize\Generator\InvoiceLine;
use Structurize\Structurize\Generator\Tax;

class GeneralInvoiceService
{
    private \Structurize\Peppol\Models\Invoice $invoice;
    /**
     * @var array|string[]
     */
    private array $supplier_data;
    private array $client_data;

    public function __construct(private StructurizeService $structurizeService)
    {
        $this->invoice = new \Structurize\Peppol\Models\Invoice();
        $this->supplier_data = [
            'name' => '',
            'address' => '',
            'city' => '',
            'zipcode' => '',
            'country' => 'BE',
            'vat' => '',
            'iban' => '',
            'bic' => '',
            'peppol_id' => '',
        ];
        $this->client_date = [
            'name' => '',
            'address' => '',
            'city' => '',
            'zipcode' => '',
            'country' => 'BE',
            'vat' => '',
            'iban' => '',
            'bic' => '',
            'contact_name' => '',
            'contact_email' => '',
            'contact_telephone' => '',
            'peppol_id' => '',
        ];

        $this->pdf_stream = null;
    }

    public function setPdfStream($stream): void
    {
        $this->pdf_stream = $stream;
    }
    public function setSupplierData(array $data): void
    {
        $this->supplier_data = $data;
    }

    public function setClientData(array $data): void
    {
        $this->client_data = $data;
    }

    public function generate($invoice, $identifier_overrule = null)
    {
        $this->invoice   = $invoice;
        $general_invoice = new Invoice();

        $general_invoice->setReference($this->invoice->number);
        $general_invoice->setInvoiceNumber($this->invoice->number);

        $general_invoice->setIssueDate($this->invoice->date);
        $general_invoice->setDueDate($this->invoice->due_date);
        $general_invoice->setDocumentType($this->invoice->total_incl < 0 ? 'CreditNote' : 'Invoice');


        $general_invoice = $this->setDocument($general_invoice);
        $general_invoice = $this->setSupplier($general_invoice);
        $general_invoice = $this->setClient($general_invoice);

        $total_excl   = $this->invoice->present()->total_excl;
        $total = $this->invoice->present()->total_incl;

        $general_invoice->setLines($this->getLines());

        $general_invoice->setTotalVatExcl($total_excl);
        $general_invoice->setTotalVatIncl($total);
        $general_invoice = $this->setTaxes($general_invoice);

        if (!is_null($identifier_overrule)) {
            $invoice->setCustomerPeppolIdentifier($identifier_overrule);
        }

        return $this->structurizeService->makeUblDocument($general_invoice);
    }

    private function setSupplier(Invoice $general_invoice)
    {
        $general_invoice->setSupplierName($this->supplier_data['name']);
        $general_invoice->setSupplierAddress(
            street: $this->supplier_data['address'],
            city: $this->supplier_data['city'],
            zipcode: $this->supplier_data['zipcode'],
            country: $this->supplier_data['country']);
        $general_invoice->setSupplierVAT($this->supplier_data['vat']);
        if (filled($this->supplier_data['iban'])) {
                $general_invoice->setSupplierIBAN($this->supplier_data['iban']);
        }
        if (filled($this->supplier_data['bic'])) {
            $general_invoice->setSupplierBIC($this->supplier_data['bic']);
        }

        return $general_invoice;
    }

    public function setClient(Invoice $general_invoice)
    {

        $general_invoice->setCustomerAddress(
            street: (filled($this->client_data['address']) ? $this->client_data['address'] : '-'),
            city: $this->client_data['city'],
            zipcode: $this->client_data['zipcode'],
            country: !is_null($this->client_data['country']) && $this->client_data['country'] != '' ? $this->client_data['country'] : 'BE');
        $general_invoice->setCustomerName($this->client_data['name']);

        $vat = (!is_null($this->client_data['vat']) && trim($this->client_data['vat']) != '' && strpos($this->client_data['vat'], 'BE') === false) ? 'BE' . $this->client_data['vat'] : $this->client_data['vat'];
        $vat = str_replace(' ', '', str_replace('.', '', $vat));

        if (!is_null($vat)) {
            $general_invoice->setCustomerVAT($vat);
        }

        if ($this->client_data['peppol_id'] != '') {
            $general_invoice->setFullPeppolId($this->client_data['peppol_id']);
        }

        $general_invoice->setCustomerContactName($this->client_data['contact_name']);
        if ($this->client_data['contact_email'] != '' && filter_var($this->client_data['contact_email'], FILTER_VALIDATE_EMAIL)) {
            $general_invoice->setCustomerContactElectronicMail($this->client_data['contact_email']);
        }
        if (!is_null($this->client_data['contact_telephone']) && $this->client_data['contact_telephone'] != '') {
            $general_invoice->setCustomerContactTelephone($this->client_data['contact_telephone']);
        }


        return $general_invoice;
    }

    private function getLines()
    {
        $invoiceLines = [];
        $counter      = 0;
        if (count($this->invoice->lines)) {
            foreach ($this->invoice->lines as $lijn) {
                $counter++;
                $item = new InvoiceLine();
                $item->setLineId($counter);
                $item->setName($lijn->name);
                $item->setDescription(nl2br($lijn->description));
                $item->setVatPercentage($lijn->vat_code);
                $item->setAmount($this->getSubtotal($lijn));
                $item->setQuantity($lijn->amount);
                $item->setInfo(nl2br($lijn->description));
                array_push($invoiceLines, $item);
            }
        }

        return $invoiceLines;
    }

    private function setTaxes(Invoice $general_invoice)
    {
        $vats = $taxs = [];


        $vats[0]  = ['vat' => $this->invoice->lines->where('vat_code', 0)->sum('vat'), 'amount' => $this->invoice->lines->where('vat_code', 0)->sum('subtotal')];
        $vats[6]  = ['vat' => $this->invoice->lines->where('vat_code', 6)->sum('vat'), 'amount' => $this->invoice->lines->where('vat_code', 6)->sum('subtotal')];
        $vats[21] = ['vat' => $this->invoice->lines->where('vat_code', 21)->sum('vat'), 'amount' => $this->invoice->lines->where('vat_code', 21)->sum('subtotal')];

        $lines = $general_invoice->getLines();

        foreach ($vats as $key => $vat) {
            $linesWithBtw = array_filter($lines, function ($line) use ($key) {
                return $line->getVatPercentage() == $key;
            });
            if (sizeof($linesWithBtw)) {
                $tax = new Tax();
                $tax->setVat($vat['vat']);
                $tax->setAmount($vat['amount']);
                $tax->setPercentage($key);
                $taxs[] = $tax;
            }
        }

        $general_invoice->setTaxes($taxs);

        return $general_invoice;
    }

    private function setDocument($general_invoice)
    {
        if(!is_null($this->pdf_stream)) {
            $general_invoice->setFileStream(base64_encode($this->pdf_stream));
            $general_invoice->setFileName('factuur_' . $this->invoice->number . '.pdf');
        }

        return $general_invoice;
    }

    private function getSubtotal($line)
    {
        return round($line->subtotal, 2);
    }
}