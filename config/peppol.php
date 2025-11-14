<?php
return [
    'api_key' => 'STRUCTURIZE_API_KEY',
    'tables' => [
        'companies' => 'companies',
        'invoices' => 'invoices',
        'invoice_logging' => 'peppol_invoice_logging',
    ],
    'services' => [
        'general-invoice' => \Structurize\Peppol\Services\GeneralInvoiceService::class
    ],
    'table-fields' => [
        'companies' => [
            'id' => 'id',
            'vat_number' => 'vat_number',
            'peppol_connected' => 'peppol_connected',
            'peppol_scheme_id' => 'peppol_scheme_id',
        ],
        'invoices' => [
            'id' => 'id',
            'number' => 'number',
            'company_id' => 'company_id',
            'peppol_sent' => 'peppol_sent',
            'peppol_sent_at' => 'peppol_sent_at',
        ],
    ],
    'multi_tenant' => [
        'enabled' => false,
        'tenant_model' => \Structurize\Peppol\Models\Setting::class,
        'tenant_attribute' => 'structurize_api_key',
    ]
];
