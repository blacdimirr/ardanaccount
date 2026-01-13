<?php

namespace App\Services;

use App\Models\MovimientoBancario;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MovimientoBancarioImportService
{
    /**
     * Procesa el archivo y retorna movimientos mapeados.
     * Soporta: CSV, OFX, XLSX/XLS
     */
    public function parseAndMap(string $contents, string $extension): array
    {
        $extension = strtolower($extension);

        // OFX
        if ($extension === 'ofx') {
            $rows = $this->parseOfx($contents);
            return $this->mapRows($rows);
        }

        // XLSX / XLS
        if (in_array($extension, ['xlsx', 'xls'], true)) {
            $rows = $this->parseSpreadsheet($contents, $extension);
            return $this->mapRows($rows);
        }

        // CSV por defecto
        $rows = $this->parseCsv($contents);
        return $this->mapRows($rows);
    }


        public function storeMovimientos(int $cuentaRecaudadoraId, array $movimientos, string $origenArchivo): void
         {
        foreach ($movimientos as $movimiento) {
        MovimientoBancario::create([
            'cuenta_recaudadora_id' => $cuentaRecaudadoraId,
            'fecha' => $movimiento['fecha'] ?? null,
            'monto' => $movimiento['monto'] ?? 0,
            'descripcion' => $movimiento['descripcion'] ?? '',
            'referencia' => $movimiento['referencia'] ?? null,
            'origen_archivo' => $origenArchivo,
         ]);
         }
         }


    /**
     * Parse CSV -> array de arrays (cada fila).
     */
    private function parseCsv(string $contents): array
    {
        $lines = preg_split("/\r\n|\n|\r/", trim($contents));
        if (!$lines || count($lines) < 2) {
            return [];
        }

        // Detecta separador común
        $firstLine = $lines[0] ?? '';
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

        $headers = str_getcsv(array_shift($lines), $delimiter);
        $headers = array_map(fn($h) => trim((string)$h), $headers);

        $rows = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $cols = str_getcsv($line, $delimiter);

            // pad para evitar notice por columnas faltantes
            $cols = array_pad($cols, count($headers), null);

            $row = [];
            foreach ($headers as $i => $key) {
                $row[$key] = $cols[$i] ?? null;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Parse OFX -> array de filas normalizadas.
     * Ajusta aquí según tu formato OFX real.
     */
    private function parseOfx(string $contents): array
    {
        $rows = [];

        // Parse simple por tags (STMTTRN). Si ya tienes tu parser, conserva el tuyo.
        $parts = preg_split('/<STMTTRN>/i', $contents);
        if (!$parts || count($parts) < 2) {
            return [];
        }

        foreach (array_slice($parts, 1) as $chunk) {
            $date = $this->matchOfxTag($chunk, 'DTPOSTED');
            $amount = $this->matchOfxTag($chunk, 'TRNAMT');
            $memo = $this->matchOfxTag($chunk, 'MEMO');
            $fitid = $this->matchOfxTag($chunk, 'FITID');

            $rows[] = [
                'fecha' => $date,
                'monto' => $amount,
                'descripcion' => $memo,
                'referencia' => $fitid,
            ];
        }

        return $rows;
    }

    private function matchOfxTag(string $chunk, string $tag): ?string
    {
        if (preg_match('/<' . preg_quote($tag, '/') . '>\s*([^<\r\n]+)/i', $chunk, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    /**
     * Parse XLSX/XLS desde contenido binario.
     * Requiere phpoffice/phpspreadsheet.
     */
    private function parseSpreadsheet(string $contents, string $extension): array
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            throw new \RuntimeException("PhpSpreadsheet no está instalado. Ejecuta: composer require phpoffice/phpspreadsheet");
        }

        $tmp = tempnam(sys_get_temp_dir(), 'mov_');
        $file = $tmp . '.' . $extension;
        file_put_contents($file, $contents);

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        @unlink($file);
        @unlink($tmp);

        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray(null, true, true, true);
        if (!$data || count($data) < 2) {
            return [];
        }

        // Primera fila como headers
        $headerRow = array_shift($data);
        $headers = [];
        foreach ($headerRow as $col => $header) {
            $h = trim((string)$header);
            if ($h !== '') {
                $headers[$col] = $h;
            }
        }

        $rows = [];
        foreach ($data as $row) {
            // Si la fila está totalmente vacía, skip
            $nonEmpty = false;
            foreach ($headers as $col => $h) {
                if (isset($row[$col]) && trim((string)$row[$col]) !== '') {
                    $nonEmpty = true;
                    break;
                }
            }
            if (!$nonEmpty) {
                continue;
            }

            $mapped = [];
            foreach ($headers as $col => $h) {
                $mapped[$h] = $row[$col] ?? null;
            }
            $rows[] = $mapped;
        }

        return $rows;
    }

    /**
     * Mapea filas crudas a estructura estándar que usa tu app.
     * Aquí debes alinear keys según tus columnas reales.
     */
    private function mapRows(array $rows): array
    {
        $movs = [];

        foreach ($rows as $row) {
            // Intento de encontrar campos comunes (ajusta según tu layout de Excel/CSV)
            $fechaRaw = $row['fecha'] ?? $row['Fecha'] ?? $row['FECHA'] ?? $row['Date'] ?? null;
            $descRaw  = $row['descripcion'] ?? $row['Descripción'] ?? $row['Descripcion'] ?? $row['Concepto'] ?? $row['Memo'] ?? null;
            $montoRaw = $row['monto'] ?? $row['Monto'] ?? $row['Importe'] ?? $row['Amount'] ?? null;
            $refRaw   = $row['referencia'] ?? $row['Referencia'] ?? $row['Ref'] ?? $row['FITID'] ?? null;

            $fecha = $this->parseDate($fechaRaw);
            $monto = $this->parseAmount($montoRaw);
            $descripcion = $descRaw !== null ? trim((string)$descRaw) : null;

            // Si no hay movimiento mínimo (fecha + monto), lo saltamos
            if (!$fecha || $monto === null) {
                continue;
            }

            $movs[] = [
                'fecha' => $fecha,
                'descripcion' => $descripcion ?? '',
                'monto' => $monto,
                'referencia' => $refRaw !== null ? trim((string)$refRaw) : null,
            ];
        }

        return $movs;
    }

    /**
     * Parseo robusto de fechas.
     * - Excel serial (numérico)
     * - d/m/Y
     * - Y-m-d
     * - Carbon::parse fallback
     */
    private function parseDate($value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Excel serial date (numeric)
        if (is_numeric($value)) {
            $n = (float) $value;

            // Guard: evita interpretar montos como fecha
            if ($n > 20000) {
                try {
                    if (class_exists(\PhpOffice\PhpSpreadsheet\Shared\Date::class)) {
                        $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($n);
                        return Carbon::instance($dt)->format('Y-m-d');
                    }
                } catch (\Throwable $e) {
                    // continúa
                }
            }
        }

        $s = trim((string) $value);
        if ($s === '') {
            return null;
        }

        // Normaliza separadores
        $s = str_replace(['.', '-'], ['/', '/'], $s);

        // d/m/Y
        try {
            return Carbon::createFromFormat('d/m/Y', $s)->format('Y-m-d');
        } catch (\Throwable $e) {
            // continúa
        }

        // Y/m/d
        try {
            return Carbon::createFromFormat('Y/m/d', $s)->format('Y-m-d');
        } catch (\Throwable $e) {
            // continúa
        }

        // fallback
        try {
            return Carbon::parse($s)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseAmount($value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $s = trim((string) $value);
        if ($s === '') {
            return null;
        }

        // Quita símbolos y espacios
        $s = str_replace(['RD$', '$', ' '], '', $s);

        // Si viene con separador de miles y decimal estilo latino: 1.234,56
        if (preg_match('/^\-?\d{1,3}(\.\d{3})*,\d+$/', $s)) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } else {
            // estilo US: 1,234.56
            $s = str_replace(',', '', $s);
        }

        if (!is_numeric($s)) {
            return null;
        }

        return (float) $s;
    }
}
