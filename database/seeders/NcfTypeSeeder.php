<?php

namespace Database\Seeders;

use App\Models\NcfType;
use Illuminate\Database\Seeder;

class NcfTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'B01', 'description' => 'Factura de Crédito Fiscal'],
            ['code' => 'B02', 'description' => 'Factura de Consumo'],
            ['code' => 'B14', 'description' => 'Comprobante para Regímenes Especiales'],
            ['code' => 'B15', 'description' => 'Comprobante Gubernamental'],
            ['code' => 'B16', 'description' => 'Comprobante para Exportaciones'],
        ];

        foreach ($types as $type) {
            NcfType::updateOrCreate(
                ['code' => $type['code']],
                [
                    'description' => $type['description'],
                    'created_by' => 0,
                ]
            );
        }
    }
}
