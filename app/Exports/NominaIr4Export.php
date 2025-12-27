<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NominaIr4Export implements FromCollection, WithHeadings
{
    private Collection $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function collection(): Collection
    {
        return $this->rows->map(function ($row) {
            return [
                $row['documento'],
                $row['empleado'],
                $row['tipo_contribuyente'],
                $row['isr'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Documento',
            'Empleado',
            'Tipo contribuyente',
            'ISR retenido',
        ];
    }
}
