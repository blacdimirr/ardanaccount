<?php

namespace App\Exports;

use App\Services\Dgii606Service;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class Dgii606Export implements FromCollection, WithHeadings
{
    public function __construct(
        protected int $year,
        protected int $month,
        protected int $creatorId,
        protected Dgii606Service $service
    ) {
    }

    public function collection()
    {
        $rows = [];
        $bills = $this->service->getPurchasesForPeriod($this->year, $this->month, $this->creatorId);

        foreach ($bills as $bill) {
            $vendor = $bill->vender;
            $itbisBilled = $bill->itbis_billed_total ?? 0;
            $rows[] = [
                'rnc_cedula' => $vendor?->tax_number ?? '',
                'tipo_id' => $this->resolveIdType($vendor?->tax_number),
                'ncf' => $bill->ncf_number,
                'fecha_comprobante' => $bill->bill_date,
                'fecha_pago' => $bill->due_date,
                'monto_facturado' => $bill->getTotal(),
                'itbis_facturado' => $itbisBilled,
                'itbis_retenido' => $bill->itbis_withheld_total ?? 0,
                'isr_retenido' => $bill->isr_withheld_total ?? 0,
                'tipo_bien_servicio' => $bill->supplier_type ?? optional($bill->category)->name,
            ];
        }

        return new Collection($rows);
    }

    public function headings(): array
    {
        return [
            'RNC o Cédula',
            'Tipo Id',
            'NCF',
            'Fecha Comprobante',
            'Fecha Pago',
            'Monto Facturado',
            'ITBIS Facturado',
            'ITBIS Retenido',
            'ISR Retenido',
            'Tipo Bien/Servicio',
        ];
    }

    protected function resolveIdType(?string $taxNumber): string
    {
        if (!$taxNumber) {
            return '';
        }

        // DGII: 1 = RNC, 2 = Cédula (heuristic by length)
        return strlen(preg_replace('/\D/', '', $taxNumber)) === 9 ? '1' : '2';
    }
}
