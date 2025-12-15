<?php

namespace Database\Seeders;

use App\Models\NcfType;
use Illuminate\Database\Seeder;

class NcfTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['code' => 'B01', 'description' => 'Factura de Crédito Fiscal'],
            ['code' => 'B02', 'description' => 'Factura Consumo'],
            ['code' => 'B14', 'description' => 'Regímenes Especiales Gubernamentales'],
            ['code' => 'B15', 'description' => 'Regímenes Especiales de Tributación'],
        ];

        foreach ($types as $type) {
            NcfType::updateOrCreate(
                ['code' => $type['code']],
                ['description' => $type['description']]
            );
        }
    }
}
