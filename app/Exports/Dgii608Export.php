<?php

namespace App\Exports;

use App\Services\Dgii608Service;
use App\Services\DgiiFormatter;
use App\Exports\Concerns\WithCompanyHeader;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;

class Dgii608Export implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell, WithDrawings
{
    use WithCompanyHeader;

    public function __construct(
        protected int $year,
        protected int $month,
        protected int $creatorId,
        protected Dgii608Service $service
    ) {
    }

    public function collection(): Collection
    {
        $rows = [];
        $creditNotes = $this->service->getAnulationsForPeriod($this->year, $this->month, $this->creatorId);

        foreach ($creditNotes as $creditNote) {
            $invoice = $creditNote->invoice;
            $customer = $invoice?->customer;

            $rows[] = [
                'rnc_cedula' => $customer?->tax_number ?? '',
                'tipo_id' => DgiiFormatter::resolveIdType($customer?->tax_number),
                'ncf_modificado' => $invoice?->ncf_number,
                'fecha_anulacion' => $creditNote->date,
                'monto_anulado' => $creditNote->amount,
                'motivo' => '01', // Genérico: Corrección de información (puede ajustarse por campos futuros)
            ];
        }

        return new Collection($rows);
    }

    public function headings(): array
    {
        return [
            'RNC o Cédula',
            'Tipo Id',
            'NCF Modificado',
            'Fecha Anulación',
            'Monto Anulado',
            'Código de Motivo',
        ];
    }
}
