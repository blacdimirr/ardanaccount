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
    'sheet_keywords' => [
        'pagos' => ['CHEQUE', 'LIBRAMIENTO', 'TRANSFER', 'SUPLIDOR', 'BENEFICIARIO', 'MONTO', 'FECHA'],
        'ordenes_compra' => ['ORDEN', 'COMPRA', 'SUPLIDOR', 'MONTO', 'FECHA', 'OBJETO', 'DETALLE'],
        'ingresos' => ['ORIGEN', 'INGR', 'MONTO', 'FECHA', 'REFERENCIA', 'DEPOSITO', 'TRANSFER'],
    ],
];
