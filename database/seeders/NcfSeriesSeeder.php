<?php

namespace Database\Seeders;

use App\Models\NcfSeries;
use App\Models\NcfType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class NcfSeriesSeeder extends Seeder
{
    public function run(): void
    {
        $validFrom = Carbon::today();
        $validTo = $validFrom->copy()->addYear();

        $seriesByType = [
            'B01' => 'CF-AUTO',
            'B02' => 'CONSUMO-AUTO',
            'B14' => 'ESPECIAL-AUTO',
            'B15' => 'GOB-AUTO',
            'B16' => 'EXPORT-AUTO',
        ];

        foreach ($seriesByType as $typeCode => $seriesName) {
            $typeId = NcfType::where('code', $typeCode)->value('id');

            if (!$typeId) {
                continue;
            }

            NcfSeries::updateOrCreate(
                [
                    'ncf_type_id' => $typeId,
                    'series' => $seriesName,
                ],
                [
                    'start_number' => 1,
                    'end_number' => 5000,
                    'current_number' => null,
                    'valid_from' => $validFrom,
                    'valid_to' => $validTo,
                    'status' => 'activo',
                    'created_by' => 0,
                ]
            );
        }
    }
}
