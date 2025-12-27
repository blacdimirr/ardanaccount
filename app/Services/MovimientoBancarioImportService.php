<?php

namespace App\Services;

use App\Models\MovimientoBancario;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class MovimientoBancarioImportService
{
    public function parseAndMap(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $contents = $file->get();

        if ($extension === 'ofx') {
            return $this->parseOfx($contents);
        }

        return $this->parseCsv($contents);
    }

    public function storeMovimientos(int $cuentaRecaudadoraId, array $movimientos, string $origenArchivo): void
    {
        foreach ($movimientos as $movimiento) {
            MovimientoBancario::create([
                'cuenta_recaudadora_id' => $cuentaRecaudadoraId,
                'fecha' => $movimiento['fecha'],
                'monto' => $movimiento['monto'],
                'descripcion' => $movimiento['descripcion'],
                'referencia' => $movimiento['referencia'],
                'origen_archivo' => $origenArchivo,
            ]);
        }
    }

    private function parseCsv(string $contents): array
    {
        $lines = preg_split('/\\r\\n|\\r|\\n/', trim($contents));
        if (empty($lines)) {
            return [];
        }

        $delimiter = $this->detectDelimiter($lines);
        $rows = [];
        foreach ($lines as $index => $line) {
            $line = $this->normalizeLine($line, $index === 0);
            if (trim($line) === '') {
                continue;
            }
            $rows[] = str_getcsv($line, $delimiter);
        }

        if (empty($rows)) {
            return [];
        }

        $headers = $this->normalizeHeaders(array_shift($rows));
        $hasHeader = $this->hasHeaderRow($headers);
        if (!$hasHeader) {
            $rows = array_merge([$headers], $rows);
            $headers = ['fecha', 'monto', 'descripcion', 'referencia'];
        }

        $mapped = [];
        foreach ($rows as $row) {
            if ($this->isEmptyRow($row)) {
                continue;
            }
            $data = $this->mapCsvRow($headers, $row);
            if ($data === null) {
                continue;
            }
            $mapped[] = $data;
        }

        return $mapped;
    }

    private function detectDelimiter(array $lines): string
    {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $candidates = [
                ',' => substr_count($line, ','),
                ';' => substr_count($line, ';'),
                "\t" => substr_count($line, "\t"),
            ];

            arsort($candidates);
            $delimiter = array_key_first($candidates);

            return $candidates[$delimiter] > 0 ? $delimiter : ',';
        }

        return ',';
    }

    private function normalizeLine(string $line, bool $isFirstLine): string
    {
        if ($isFirstLine) {
            $line = preg_replace('/^\xEF\xBB\xBF/', '', $line);
        }

        return $line;
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header) {
            $header = trim((string) $header);
            $header = Str::lower($header);
            $header = str_replace([' ', '-', '.'], '_', $header);
            $header = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $header);
            return $header;
        }, $headers);
    }

    private function hasHeaderRow(array $headers): bool
    {
        $matches = array_intersect($headers, [
            'fecha',
            'date',
            'monto',
            'amount',
            'descripcion',
            'description',
            'referencia',
            'reference',
        ]);

        return !empty($matches);
    }

    private function mapCsvRow(array $headers, array $row): ?array
    {
        $map = array_combine($headers, $row + array_fill(0, count($headers), null));
        $fecha = $map['fecha'] ?? $map['date'] ?? null;
        $monto = $map['monto'] ?? $map['amount'] ?? null;
        $descripcion = $map['descripcion'] ?? $map['description'] ?? null;
        $referencia = $map['referencia'] ?? $map['reference'] ?? null;

        if (!$fecha || !$monto) {
            return null;
        }

        $fechaFormateada = $this->parseDate($fecha);
        if (!$fechaFormateada) {
            return null;
        }

        return [
            'fecha' => $fechaFormateada,
            'monto' => $this->parseAmount($monto),
            'descripcion' => $descripcion ? trim($descripcion) : null,
            'referencia' => $referencia ? trim($referencia) : null,
        ];
    }

    private function parseOfx(string $contents): array
    {
        $transactions = [];
        if (!preg_match_all('/<STMTTRN>(.*?)<\\/STMTTRN>/s', $contents, $matches)) {
            return $transactions;
        }

        foreach ($matches[1] as $block) {
            $fecha = $this->matchTagValue($block, 'DTPOSTED');
            $monto = $this->matchTagValue($block, 'TRNAMT');
            $descripcion = $this->matchTagValue($block, 'MEMO');
            $referencia = $this->matchTagValue($block, 'FITID');

            if (!$fecha || !$monto) {
                continue;
            }

            $fechaFormateada = $this->parseOfxDate($fecha);
            if (!$fechaFormateada) {
                continue;
            }

            $transactions[] = [
                'fecha' => $fechaFormateada,
                'monto' => $this->parseAmount($monto),
                'descripcion' => $descripcion ? trim($descripcion) : null,
                'referencia' => $referencia ? trim($referencia) : null,
            ];
        }

        return $transactions;
    }

    private function matchTagValue(string $block, string $tag): ?string
    {
        if (preg_match('/<' . preg_quote($tag, '/') . '>([^<\\r\\n]+)/', $block, $match)) {
            return trim($match[1]);
        }

        return null;
    }

    private function parseDate(string $value): ?string
    {
        $value = trim($value);
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $exception) {
            try {
                return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            } catch (\Throwable $exception) {
                return null;
            }
        }
    }

    private function parseOfxDate(string $value): ?string
    {
        $value = trim($value);
        $date = substr($value, 0, 8);
        try {
            return Carbon::createFromFormat('Ymd', $date)->format('Y-m-d');
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function parseAmount($value): string
    {
        $value = trim((string) $value);
        $value = str_replace([' ', ','], ['', ''], $value);
        return (string) number_format((float) $value, 2, '.', '');
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
