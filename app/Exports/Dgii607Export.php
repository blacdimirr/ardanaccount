<?php

namespace App\Exports;

use App\Services\Dgii607Service;
use App\Services\DgiiFormatter;
use App\Exports\Concerns\WithCompanyHeader;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;

class Dgii607Export implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell, WithDrawings
{
    use WithCompanyHeader;

    public function __construct(
        protected int $year,
        protected int $month,
        protected int $creatorId,
        protected Dgii607Service $service
    ) {
    }

    public function collection(): Collection
    {
        $rows = [];
        $invoices = $this->service->getSalesForPeriod($this->year, $this->month, $this->creatorId);

        foreach ($invoices as $invoice) {
            $customer = $invoice->customer;

            $rows[] = [
                'rnc_cedula' => $customer?->tax_number ?? '',
                'tipo_id' => DgiiFormatter::resolveIdType($customer?->tax_number),
                'ncf' => $invoice->ncf_number,
                'fecha_comprobante' => $invoice->issue_date,
                'fecha_pago' => $invoice->due_date,
                'monto_facturado' => $invoice->getTotal(),
                'itbis_facturado' => $invoice->getTotalTax(),
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
        ];
    }
}
