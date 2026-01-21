<?php

return [
    'defaults' => [
        'created_by' => env('HISTORICO_CREATED_BY', 1),
        'category_id' => env('HISTORICO_CATEGORY_ID', 1),
        'product_id' => env('HISTORICO_PRODUCT_ID', 1),
        'customer_name' => env('HISTORICO_CUSTOMER_NAME', 'HISTORICO INGRESOS'),
        'customer_email' => env('HISTORICO_CUSTOMER_EMAIL', 'historico.ingresos@example.com'),
        'bank_account_id' => env('HISTORICO_BANK_ACCOUNT_ID', 0),
    ],
    'payment_method_map' => [
        'CHEQUE' => 0,
        'TRANSFERENCIA' => 0,
        'LIBRAMIENTO' => 0,
        'EFECTIVO' => 0,
    ],
    'accounting_defaults' => [
        'bank_account_code' => env('HISTORICO_BANK_ACCOUNT_CODE', '110102'),
        'payables_account_code' => env('HISTORICO_PAYABLES_ACCOUNT_CODE', '2103020001'),
        'expense_account_code' => env('HISTORICO_EXPENSE_ACCOUNT_CODE', '510102'),
        'income_ars_account_code' => env('HISTORICO_INCOME_ARS_ACCOUNT_CODE', '410201'),
        'income_gob_account_code' => env('HISTORICO_INCOME_GOB_ACCOUNT_CODE', '410401'),
        'income_other_account_code' => env('HISTORICO_INCOME_OTHER_ACCOUNT_CODE', '410298'),
    ],
    'accounting_rules' => [
        [
            'document_type' => 'income',
            'match_field' => 'origen',
            'match_type' => 'contains',
            'match_value' => 'ARS',
            'debit_account_code' => '110102',
            'credit_account_code' => '410201',
            'description' => 'Ingresos ARS',
        ],
        [
            'document_type' => 'income',
            'match_field' => 'origen',
            'match_type' => 'contains',
            'match_value' => 'GOBIERNO',
            'debit_account_code' => '110102',
            'credit_account_code' => '410401',
            'description' => 'Transferencias Gobierno Central',
        ],
        [
            'document_type' => 'income',
            'match_field' => 'origen',
            'match_type' => 'contains',
            'match_value' => 'OTROS',
            'debit_account_code' => '110102',
            'credit_account_code' => '410298',
            'description' => 'Otros ingresos',
        ],
    ],
    'sheet_keywords' => [
        'pagos' => ['CHEQUE', 'LIBRAMIENTO', 'TRANSFER', 'SUPLIDOR', 'BENEFICIARIO', 'MONTO', 'FECHA'],
        'ordenes_compra' => ['ORDEN', 'COMPRA', 'SUPLIDOR', 'MONTO', 'FECHA', 'OBJETO', 'DETALLE'],
        'ingresos' => ['ORIGEN', 'INGR', 'MONTO', 'FECHA', 'REFERENCIA', 'DEPOSITO', 'TRANSFER'],
    ],
];
