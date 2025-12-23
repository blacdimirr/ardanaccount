<?php

namespace App\Exports;

use App\Models\Pac;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PacExport implements FromCollection, WithHeadings
{
    protected Pac $pac;

    public function __construct(Pac $pac)
    {
        $this->pac = $pac;
    }

    public function collection(): Collection
    {
        $pac = $this->pac->load([
            'items.partidaPresupuestaria',
            'items.objetoGasto',
            'items.fuenteFinanciamiento',
        ]);

        $rows = [];

        foreach ($pac->items as $item) {
            $rows[] = [
                'anio' => $pac->anio,
                'pac_descripcion' => $pac->descripcion,
                'item_descripcion' => $item->descripcion,
                'partida_presupuestaria' => $item->partidaPresupuestaria->name ?? '',
                'objeto_gasto' => $item->objetoGasto->description ?? '',
                'fuente_financiamiento' => $item->fuenteFinanciamiento->description ?? '',
                'tipo_procedimiento' => $item->tipo_procedimiento,
                'monto_estimado' => $item->monto_estimado,
            ];
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'Año',
            'Descripción PAC',
            'Descripción Ítem',
            'Partida Presupuestaria',
            'Objeto de Gasto',
            'Fuente de Financiamiento',
            'Tipo de Procedimiento',
            'Monto Estimado',
        ];
    }
}
