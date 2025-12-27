<?php

namespace App\Exports\Concerns;

use App\Models\Utility;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

trait WithCompanyHeader
{
    protected int $companyHeaderRows = 4;
    protected int $companyHeaderSpacing = 1;

    public function startCell(): string
    {
        return 'A' . $this->companyHeaderStartRow();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->applyCompanyHeader($event);
            },
        ];
    }

    public function drawings(): array
    {
        $logoPath = $this->resolveCompanyLogoPath();

        if (!$logoPath) {
            return [];
        }

        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo institucional');
        $drawing->setPath($logoPath);
        $drawing->setHeight(60);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(5);
        $drawing->setOffsetY(5);

        return [$drawing];
    }

    protected function companyHeaderStartRow(): int
    {
        return $this->companyHeaderRows + $this->companyHeaderSpacing + 1;
    }

    protected function applyCompanyHeader(AfterSheet $event): void
    {
        $sheet = $event->sheet->getDelegate();
        $highestColumn = $sheet->getHighestColumn();
        $settings = Utility::settings();

        $companyName = $settings['company_name'] ?: config('app.name');
        $address = $this->buildCompanyAddress($settings);
        $telephone = $settings['company_telephone'] ?? '';
        $rnc = $settings['registration_number'] ?? '';
        $userName = Auth::user()?->name ?? 'Sistema';
        $generatedAt = now()->format('d/m/Y H:i');

        $rows = [
            $companyName,
            $address,
            trim(implode(' | ', array_filter([
                $telephone !== '' ? 'Teléfono: ' . $telephone : '',
                $rnc !== '' ? 'RNC: ' . $rnc : '',
            ]))),
            'Generado por: ' . $userName . ' | Fecha: ' . $generatedAt,
        ];

        foreach ($rows as $index => $value) {
            $rowNumber = $index + 1;
            $sheet->mergeCells("A{$rowNumber}:{$highestColumn}{$rowNumber}");
            $sheet->setCellValue("A{$rowNumber}", $value);
        }

        $sheet->getStyle("A1:A{$this->companyHeaderRows}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A1:A{$this->companyHeaderRows}")
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getRowDimension(1)->setRowHeight(45);
    }

    protected function buildCompanyAddress(array $settings): string
    {
        $parts = array_filter([
            $settings['company_address'] ?? '',
            $settings['company_city'] ?? '',
            $settings['company_state'] ?? '',
            $settings['company_zipcode'] ?? '',
            $settings['company_country'] ?? '',
        ]);

        return implode(', ', $parts);
    }

    protected function resolveCompanyLogoPath(): ?string
    {
        $logoFile = Utility::getValByName('company_logo_dark') ?: 'logo-dark.png';
        $storagePath = storage_path('app/public/uploads/logo/' . $logoFile);
        $publicPath = public_path('storage/uploads/logo/' . $logoFile);

        if (file_exists($storagePath)) {
            return $storagePath;
        }

        if (file_exists($publicPath)) {
            return $publicPath;
        }

        return null;
    }
}
