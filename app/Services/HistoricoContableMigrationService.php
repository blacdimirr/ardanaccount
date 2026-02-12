<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\BillProduct;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Revenue;
use App\Models\Vender;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class HistoricoContableMigrationService
{
    private const STATUS_NEW = 'NEW';
    private const STATUS_VALIDATED = 'VALIDATED';
    private const STATUS_ERROR = 'ERROR';
    private const STATUS_IMPORTED = 'IMPORTED';

    public function listMonthFolders(string $rootPath): array
    {
        $folders = array_filter(glob($rootPath . '/*'), 'is_dir');
        return array_map('basename', $folders);
    }

    public function resolveMonthFolder(string $rootPath, string $input): ?string
    {
        $normalized = Str::upper(trim($input));
        $folders = $this->listMonthFolders($rootPath);

        foreach ($folders as $folder) {
            if (Str::upper($folder) === $normalized) {
                return $folder;
            }
        }

        if (preg_match('/^(\\d{2})[-\\/](\\d{4})$/', $normalized, $m)) {
            $monthName = $this->monthNumberToSpanish((int) $m[1]);
            if ($monthName) {
                $candidate = $monthName . ' ' . $m[2];
                foreach ($folders as $folder) {
                    if (Str::upper($folder) === $candidate) {
                        return $folder;
                    }
                }
            }
        }

        return null;
    }

    public function findMatrizFile(string $monthPath): array
    {
        $files = glob($monthPath . '/*');
        $matrizFiles = [];
        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }
            if (preg_match('/^matriz/i', basename($file))) {
                $matrizFiles[] = $file;
            }
        }

        if (!$matrizFiles) {
            return ['selected' => null, 'duplicates' => []];
        }

        usort($matrizFiles, fn($a, $b) => filemtime($b) <=> filemtime($a));
        $selected = array_shift($matrizFiles);

        return [
            'selected' => $selected,
            'duplicates' => $matrizFiles,
        ];
    }

    public function listSheetNames(string $filePath): array
    {
        $reader = IOFactory::createReaderForFile($filePath);
        return $reader->listWorksheetNames($filePath);
    }

    public function detectSheets(array $sheetNames): array
    {
        $normalized = [];
        foreach ($sheetNames as $name) {
            $normalized[$this->normalize($name)] = $name;
        }

        return [
            'pagos' => $this->detectSheetByKeywords($normalized, ['RELACION DE CHEQUES EMITIDOS', 'RELACION CHEQUES EMITIDOS', 'RELACION DE PAGOS EMITIDOS']),
            'ordenes_compra' => $this->detectSheetByKeywords($normalized, ['RELACION DE ORDENES DE COMPRAS', 'RELACION ORDENES DE COMPRAS']),
            'ingresos' => $this->detectSheetByKeywords($normalized, ['INGR SEGUN ORIGEN', 'INGRE SEGUN ORIGEN', 'INGRESO SEGUN ORIGEN', 'INGRESOS SEGUN ORIGEN']),
            // Hoja usada para generar asientos contables por cuentas afectadas (débitos) y control de valor/retención
            'cuenta_t_vs' => $this->detectSheetByKeywords($normalized, ['CUENTA T VS']),
        ];
    }

        public function stageMonth(string $rootPath, string $monthFolder, string $filePath, array $sheets, bool $dryRun, int $chunkSize = 500): array
    {
        $results = [
            'pagos' => 0,
            'ordenes_compra' => 0,
            'ingresos' => 0,
        ];

        if (!empty($sheets['pagos'])) {
            $results['pagos'] = $this->stagePagos($monthFolder, $filePath, $sheets['pagos'], $chunkSize, $dryRun);
        }
        if (!empty($sheets['ordenes_compra'])) {
            $results['ordenes_compra'] = $this->stageOrdenesCompra($monthFolder, $filePath, $sheets['ordenes_compra'], $chunkSize, $dryRun);
        }
        if (!empty($sheets['ingresos'])) {
            $results['ingresos'] = $this->stageIngresos($monthFolder, $filePath, $sheets['ingresos'], $chunkSize, $dryRun);
        }

        return $results;
    }


    public function validateMonth(string $monthFolder): array
    {
        return [
            'pagos' => $this->validatePagos($monthFolder),
            'ordenes_compra' => $this->validateOrdenesCompra($monthFolder),
            'ingresos' => $this->validateIngresos($monthFolder),
        ];
    }

    public function importMonth(string $monthFolder, int $batchId, array $options = []): array
    {
        return [
            'pagos' => $this->importPagos($monthFolder, $batchId, $options),
            'ordenes_compra' => $this->importOrdenesCompra($monthFolder, $batchId, $options),
            'ingresos' => $this->importIngresos($monthFolder, $batchId, $options),
        ];
    }

    public function rollbackMonth(int $batchId): void
    {
        DB::transaction(function () use ($batchId) {
            BillProduct::where('migration_batch_id', $batchId)->delete();
            Bill::where('migration_batch_id', $batchId)->delete();
            Payment::where('migration_batch_id', $batchId)->delete();
            Revenue::where('migration_batch_id', $batchId)->delete();
        });
    }

    public function exportErrors(string $monthFolder, string $storagePath): array
    {
        $files = [];
        $files[] = $this->exportErrorCsv('staging_pagos_emitidos', $monthFolder, $storagePath, 'pagos');
        $files[] = $this->exportErrorCsv('staging_ordenes_compra', $monthFolder, $storagePath, 'ordenes_compra');
        $files[] = $this->exportErrorCsv('staging_ingresos_origen', $monthFolder, $storagePath, 'ingresos');
        return array_values(array_filter($files));
    }

        private function stagePagos(string $monthFolder, string $filePath, string $sheetName, int $chunkSize, bool $dryRun = false): int
    {
        $headerRow = $this->detectHeaderRow($filePath, $sheetName, config('historico_contable.sheet_keywords.pagos'));
        if (!$headerRow) {
            return 0;
        }

        $headers = $this->readRow($filePath, $sheetName, $headerRow);
        $headerMap = $this->buildHeaderMap($headers);

        $rowsInserted = 0;

        $this->iterateRows($filePath, $sheetName, $headerRow + 1, $chunkSize, function ($row, $rowNumber) use ($monthFolder, $filePath, $sheetName, $headerMap, &$rowsInserted, $dryRun) {
            $mapped = $this->mapPagosRow($row, $headerMap);
            if (!$mapped['has_data']) {
                return;
            }

            $raw = $this->mapRawRow($row, $headerMap);
            $rawJson = $this->safeJsonEncode($raw);

            // Make hash stable and UNIQUE per excel row to avoid insertOrIgnore collisions when mapping fails.
            $hash = sha1(implode('|', [$monthFolder, $sheetName, (string) $rowNumber, $rawJson]));

            $data = [
                'hash' => $hash,
                'source_month_folder' => $monthFolder,
                'source_file' => basename($filePath),
                'source_sheet' => $sheetName,
                'source_row_number' => $rowNumber,
                'raw_json' => $rawJson,

                'fecha' => $mapped['fecha'],
                'referencia' => $mapped['referencia'],
                'monto' => $mapped['monto'],
                'suplidor' => $mapped['suplidor'],
                'concepto' => $mapped['concepto'],
                'libramiento' => $mapped['libramiento'],
                'cheque' => $mapped['cheque'],
                'transferencia' => $mapped['transferencia'],
                'banco' => $mapped['banco'],

                'status' => 'NEW',
                'error_message' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($dryRun) {
                $rowsInserted++;
                return;
            }

            $inserted = DB::table('staging_pagos_emitidos')->insertOrIgnore($data);
            if ($inserted) {
                $rowsInserted++;
            }
        });

        return $rowsInserted;
    }


        private function stageOrdenesCompra(string $monthFolder, string $filePath, string $sheetName, int $chunkSize, bool $dryRun = false): int
    {
        $headerRow = $this->detectHeaderRow($filePath, $sheetName, config('historico_contable.sheet_keywords.ordenes_compra'));
        if (!$headerRow) {
            return 0;
        }

        $headers = $this->readRow($filePath, $sheetName, $headerRow);
        $headerMap = $this->buildHeaderMap($headers);

        $rowsInserted = 0;

        $this->iterateRows($filePath, $sheetName, $headerRow + 1, $chunkSize, function ($row, $rowNumber) use ($monthFolder, $filePath, $sheetName, $headerMap, &$rowsInserted, $dryRun) {
            $mapped = $this->mapOrdenesCompraRow($row, $headerMap);
            if (!$mapped['has_data']) {
                return;
            }

            $raw = $this->mapRawRow($row, $headerMap);
            $rawJson = $this->safeJsonEncode($raw);
            $hash = sha1(implode('|', [$monthFolder, $sheetName, (string) $rowNumber, $rawJson]));

            $data = [
                'hash' => $hash,
                'source_month_folder' => $monthFolder,
                'source_file' => basename($filePath),
                'source_sheet' => $sheetName,
                'source_row_number' => $rowNumber,
                'raw_json' => $rawJson,
                'fecha' => $mapped['fecha'] ?? null,
                'numero_oc' => $mapped['numero_oc'] ?? null,
                'monto' => $mapped['monto'] ?? null,
                'suplidor' => $mapped['suplidor'] ?? null,
               'concepto' => $mapped['concepto'] ?? ($mapped['detalle'] ?? null),
               'rnc' => $mapped['rnc'] ?? null,
                'status' => 'NEW',
                'error_message' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($dryRun) {
                $rowsInserted++;
                return;
            }

            $inserted = DB::table('staging_ordenes_compra')->insertOrIgnore($data);
            if ($inserted) {
                $rowsInserted++;
            }
        });

        return $rowsInserted;
    }


        private function stageIngresos(string $monthFolder, string $filePath, string $sheetName, int $chunkSize, bool $dryRun = false): int
    {
        $headerRow = $this->detectHeaderRow($filePath, $sheetName, config('historico_contable.sheet_keywords.ingresos'));
        if (!$headerRow) {
            return 0;
        }

        $headers = $this->readRow($filePath, $sheetName, $headerRow);
        $headerMap = $this->buildHeaderMap($headers);

        $rowsInserted = 0;

        $this->iterateRows($filePath, $sheetName, $headerRow + 1, $chunkSize, function ($row, $rowNumber) use ($monthFolder, $filePath, $sheetName, $headerMap, &$rowsInserted, $dryRun) {
            $mapped = $this->mapIngresosRow($row, $headerMap);
            if (!$mapped['has_data']) {
                return;
            }

            $raw = $this->mapRawRow($row, $headerMap);
            $rawJson = $this->safeJsonEncode($raw);
            $hash = sha1(implode('|', [$monthFolder, $sheetName, (string) $rowNumber, $rawJson]));

            $data = [
                'hash' => $hash,
                'source_month_folder' => $monthFolder,
                'source_file' => basename($filePath),
                'source_sheet' => $sheetName,
                'source_row_number' => $rowNumber,
                'raw_json' => $rawJson,

                'fecha' => $mapped['fecha'],
                'origen' => $mapped['origen'],
                'referencia' => $mapped['referencia'],
                'monto' => $mapped['monto'],
                'observacion' => $mapped['observacion'],

                'status' => 'NEW',
                'error_message' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($dryRun) {
                $rowsInserted++;
                return;
            }

            $inserted = DB::table('staging_ingresos_origen')->insertOrIgnore($data);
            if ($inserted) {
                $rowsInserted++;
            }
        });

        return $rowsInserted;
    }


    private function validatePagos(string $monthFolder): array
    {
        $total = 0;
        $errors = 0;

        DB::table('staging_pagos_emitidos')
            ->where('source_month_folder', $monthFolder)
            ->where('status', self::STATUS_NEW)
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$total, &$errors) {
                foreach ($rows as $row) {
                    $total++;
                    $error = null;
                    if (!$row->fecha) {
                        $error = 'Fecha requerida';
                    } elseif ($row->monto === null || $row->monto == 0) {
                        $error = 'Monto requerido';
                    } elseif (!$row->cheque && !$row->transferencia && !$row->libramiento) {
                        $error = 'Referencia de pago requerida (cheque/transferencia/libramiento)';
                    }

                    $status = $error ? self::STATUS_ERROR : self::STATUS_VALIDATED;
                    if ($error) {
                        $errors++;
                    }

                    DB::table('staging_pagos_emitidos')
                        ->where('id', $row->id)
                        ->update([
                            'status' => $status,
                            'error_message' => $error,
                            'updated_at' => now(),
                        ]);
                }
            });

        return ['total' => $total, 'errors' => $errors];
    }

    private function validateOrdenesCompra(string $monthFolder): array
    {
        $total = 0;
        $errors = 0;

        DB::table('staging_ordenes_compra')
            ->where('source_month_folder', $monthFolder)
            ->where('status', self::STATUS_NEW)
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$total, &$errors) {
                foreach ($rows as $row) {
                    $total++;
                    $error = null;
                    if (!$row->fecha) {
                        $error = 'Fecha requerida';
                    } elseif (!$row->numero_oc) {
                        $error = 'Número de OC requerido';
                    } elseif ($row->monto === null || $row->monto == 0) {
                        $error = 'Monto requerido';
                    }

                    $status = $error ? self::STATUS_ERROR : self::STATUS_VALIDATED;
                    if ($error) {
                        $errors++;
                    }

                    DB::table('staging_ordenes_compra')
                        ->where('id', $row->id)
                        ->update([
                            'status' => $status,
                            'error_message' => $error,
                            'updated_at' => now(),
                        ]);
                }
            });

        return ['total' => $total, 'errors' => $errors];
    }

    private function validateIngresos(string $monthFolder): array
    {
        $total = 0;
        $errors = 0;

        DB::table('staging_ingresos_origen')
            ->where('source_month_folder', $monthFolder)
            ->where('status', self::STATUS_NEW)
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$total, &$errors) {
                foreach ($rows as $row) {
                    $total++;
                    $error = null;
                    if (!$row->fecha) {
                        $error = 'Fecha requerida';
                    } elseif ($row->monto === null || $row->monto == 0) {
                        $error = 'Monto requerido';
                    } elseif (!$row->origen) {
                        $error = 'Origen requerido';
                    }

                    $status = $error ? self::STATUS_ERROR : self::STATUS_VALIDATED;
                    if ($error) {
                        $errors++;
                    }

                    DB::table('staging_ingresos_origen')
                        ->where('id', $row->id)
                        ->update([
                            'status' => $status,
                            'error_message' => $error,
                            'updated_at' => now(),
                        ]);
                }
            });

        return ['total' => $total, 'errors' => $errors];
    }

    private function importPagos(string $monthFolder, int $batchId, array $options): int
    {
        $createdBy = $options['created_by'] ?? config('historico_contable.defaults.created_by');
        $categoryId = $options['category_id'] ?? config('historico_contable.defaults.category_id');
        $count = 0;

        DB::table('staging_pagos_emitidos')
            ->where('source_month_folder', $monthFolder)
            ->where('status', self::STATUS_VALIDATED)
            ->orderBy('id')
            ->chunkById(200, function ($rows) use (&$count, $batchId, $createdBy, $categoryId) {
                foreach ($rows as $row) {
                    $vendorId = $this->resolveVendorId($row->suplidor, $createdBy);
                    $paymentMethod = $this->resolvePaymentMethod($row->metodo);

                    $payment = Payment::create([
                        'date' => $row->fecha,
                        'amount' => $row->monto ?? 0,
                        'account_id' => $this->resolveBankAccountId($row->banco),
                        'vender_id' => $vendorId,
                        'description' => $row->concepto ?? '',
                        'category_id' => $categoryId,
                        'payment_method' => $paymentMethod,
                        'reference' => $row->referencia,
                        'created_by' => $createdBy,
                        'migration_batch_id' => $batchId,
                        'staging_id' => $row->id,
                        'source_hash' => $row->hash,
                    ]);

                    DB::table('staging_pagos_emitidos')
                        ->where('id', $row->id)
                        ->update([
                            'status' => self::STATUS_IMPORTED,
                            'updated_at' => now(),
                        ]);

                    $count++;
                    Log::info('Pago importado', ['payment_id' => $payment->id, 'staging_id' => $row->id]);
                }
            });

        return $count;
    }

    private function importOrdenesCompra(string $monthFolder, int $batchId, array $options): int
    {
        $createdBy = $options['created_by'] ?? config('historico_contable.defaults.created_by');
        $categoryId = $options['category_id'] ?? config('historico_contable.defaults.category_id');
        $productId = $options['product_id'] ?? config('historico_contable.defaults.product_id');
        $count = 0;

        DB::table('staging_ordenes_compra')
            ->where('source_month_folder', $monthFolder)
            ->where('status', self::STATUS_VALIDATED)
            ->orderBy('id')
            ->chunkById(200, function ($rows) use (&$count, $batchId, $createdBy, $categoryId, $productId) {
                foreach ($rows as $row) {
                    $vendorId = $this->resolveVendorId($row->suplidor, $createdBy);

                    $bill = Bill::create([
                        'bill_id' => $row->numero_oc ?: '0',
                        'vender_id' => $vendorId,
                        'bill_date' => $row->fecha,
                        'due_date' => $row->fecha,
                        'order_number' => $row->numero_oc ?: '0',
                        'category_id' => $categoryId,
                        'created_by' => $createdBy,
                        'migration_batch_id' => $batchId,
                        'staging_id' => $row->id,
                        'source_hash' => $row->hash,
                    ]);

                    BillProduct::create([
                        'bill_id' => $bill->id,
                        'product_id' => $productId,
                        'quantity' => 1,
                        'tax' => 0,
                        'discount' => 0,
                        'price' => $row->monto ?? 0,
                        'description' => $row->detalle ?? 'Migración OC',
                        'migration_batch_id' => $batchId,
                        'staging_id' => $row->id,
                        'source_hash' => $row->hash,
                    ]);

                    DB::table('staging_ordenes_compra')
                        ->where('id', $row->id)
                        ->update([
                            'status' => self::STATUS_IMPORTED,
                            'updated_at' => now(),
                        ]);

                    $count++;
                    Log::info('OC importada', ['bill_id' => $bill->id, 'staging_id' => $row->id]);
                }
            });

        return $count;
    }

    private function importIngresos(string $monthFolder, int $batchId, array $options): int
    {
        $createdBy = $options['created_by'] ?? config('historico_contable.defaults.created_by');
        $categoryId = $options['category_id'] ?? config('historico_contable.defaults.category_id');
        $count = 0;

        $customerId = $this->resolveCustomerId($createdBy);

        DB::table('staging_ingresos_origen')
            ->where('source_month_folder', $monthFolder)
            ->where('status', self::STATUS_VALIDATED)
            ->orderBy('id')
            ->chunkById(200, function ($rows) use (&$count, $batchId, $createdBy, $categoryId, $customerId) {
                foreach ($rows as $row) {
                    $revenue = Revenue::create([
                        'date' => $row->fecha,
                        'amount' => $row->monto ?? 0,
                        'account_id' => $this->resolveBankAccountId($row->banco),
                        'customer_id' => $customerId,
                        'category_id' => $categoryId,
                        'payment_method' => 0,
                        'reference' => $row->referencia,
                        'description' => $row->observacion ?? $row->origen ?? 'Ingreso histórico',
                        'created_by' => $createdBy,
                        'migration_batch_id' => $batchId,
                        'staging_id' => $row->id,
                        'source_hash' => $row->hash,
                    ]);

                    DB::table('staging_ingresos_origen')
                        ->where('id', $row->id)
                        ->update([
                            'status' => self::STATUS_IMPORTED,
                            'updated_at' => now(),
                        ]);

                    $count++;
                    Log::info('Ingreso importado', ['revenue_id' => $revenue->id, 'staging_id' => $row->id]);
                }
            });

        return $count;
    }

    private function exportErrorCsv(string $table, string $monthFolder, string $storagePath, string $label): ?string
    {
        $rows = DB::table($table)
            ->where('source_month_folder', $monthFolder)
            ->where('status', self::STATUS_ERROR)
            ->orderBy('id')
            ->get();

        if ($rows->isEmpty()) {
            return null;
        }

        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        $file = $storagePath . '/errores_' . Str::slug($monthFolder) . '_' . $label . '.csv';
        $fp = fopen($file, 'w');
        fputcsv($fp, ['id', 'sheet', 'row', 'error', 'raw_json']);
        foreach ($rows as $row) {
            fputcsv($fp, [$row->id, $row->source_sheet, $row->source_row_number, $row->error_message, $row->raw_json]);
        }
        fclose($fp);

        return $file;
    }

        private function fixedHeaderRow(string $sheetName): ?int
    {
        $s = $this->normalize($sheetName);

        // Based on MATRIZ PARA USO DE CONTABILIDAD 01-2025
        if (str_contains($s, 'INGRESOS SEGUN ORIGEN')) {
            return 6;
        }
        if (str_contains($s, 'RELACION ORDENES DE COMPRAS')) {
            return 4;
        }
        if (str_contains($s, 'RELACION CHEQUES EMITIDOS') || str_contains($s, 'RELACION DE CHEQUES EMITIDOS') || str_contains($s, 'RELACION DE PAGOS EMITIDOS') || str_contains($s, 'RELACION PAGOS EMITIDOS')) {
            return 9;
        }

        return null;
    }


    private function detectHeaderRow(string $filePath, string $sheetName, ?array $keywords): ?int
    {
        // Prefer fixed header row per sheet when known (avoids mis-detection).
        $fixed = $this->fixedHeaderRow($sheetName);
        if ($fixed) {
            return $fixed;
        }

        $keywords = array_values(array_filter((array) $keywords));
        if (empty($keywords)) {
            // Safe defaults if config is missing
            $keywords = [
                'FECHA', 'CHEQUE', 'TRANSFERENCIA', 'LIBRAMIENTO', 'SUPLIDOR', 'BENEFICIARIO',
                'MONTO', 'VALOR', 'ORDEN', 'COMPRA', 'OC', 'ORIGEN', 'CONCEPTO',
            ];
        }

        $rows = $this->readRows($filePath, $sheetName, 1, 60);
        $bestRow = null;
        $bestScore = 0;

        foreach ($rows as $idx => $row) {
            $score = 0;

            foreach ($row as $cell) {
                $value = $this->normalize($cell);
                if ($value === '') {
                    continue;
                }
                foreach ($keywords as $kw) {
                    if (str_contains($value, $kw)) {
                        $score++;
                        break;
                    }
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestRow = $idx + 1; // because readRows starts at row 1 here
            }
        }

        return $bestScore >= 2 ? $bestRow : null;
    }


    private function buildHeaderMap(array $headers): array
    {
        $map = [];
        foreach ($headers as $idx => $header) {
            $normalized = $this->normalize($header);
            if ($normalized !== '') {
                $map[$normalized] = $idx;
            }
        }

        return $map;
    }

    private function mapPagosRow(array $row, array $headerMap): array
    {
        $raw = $this->mapRawRow($row, $headerMap);
        $fecha = $this->parseDate($this->valueByHeaders($row, $headerMap, ['FECHA']));
        $monto = $this->parseAmount($this->valueByHeaders($row, $headerMap, ['VALOR PAGADO', 'MONTO', 'VALOR DE FACTURA', 'VALOR']));
        $beneficiario = $this->valueByHeaders($row, $headerMap, ['BENEFICIARIO', 'SUPLIDOR', 'PROVEEDOR']);
        $libramiento = $this->valueByHeaders($row, $headerMap, ['LIBRAMIENTO']);
        $cheque = $this->valueByHeaders($row, $headerMap, ['CHEQUE']);
        $transferencia = $this->valueByHeaders($row, $headerMap, ['TRANSFERENCIA', 'TRANSFER']);
        $concepto = $this->valueByHeaders($row, $headerMap, ['NO.FACTURAS', 'CONCEPTO', 'DESCRIPCION', 'DETALLE']);
        $banco = $this->valueByHeaders($row, $headerMap, ['BANCO', 'CUENTA', 'CTA', 'CUENTA BANCARIA']);

        $referencia = $cheque ?: ($transferencia ?: $libramiento);
        $metodo = $cheque ? 'CHEQUE' : ($transferencia ? 'TRANSFERENCIA' : ($libramiento ? 'LIBRAMIENTO' : null));

        $ordenCompra = null;
        if ($concepto && preg_match('/\\bHSLM[-\\w]+\\b/i', $concepto, $m)) {
            $ordenCompra = $m[0];
        }

        return [
            'raw' => $raw,
            'has_data' => $this->rowHasData($row),
            'fecha' => $fecha,
            'monto' => $monto,
            'suplidor' => $beneficiario ? trim((string) $beneficiario) : null,
            'libramiento' => $this->sanitizeText($libramiento),
            'cheque' => $this->sanitizeText($cheque),
            'transferencia' => $this->sanitizeText($transferencia),
            'concepto' => $this->sanitizeText($concepto),
            'referencia' => $this->sanitizeText($referencia),
            'metodo' => $metodo,
            'orden_compra' => $ordenCompra,
            'banco' => $this->sanitizeText($banco),
        ];
    }

    private function mapOrdenesCompraRow(array $row, array $headerMap): array
    {
        $raw = $this->mapRawRow($row, $headerMap);
        $fecha = $this->parseDate($this->valueByHeaders($row, $headerMap, ['FECHA', 'FECHA DE PUBLICACION']));
        $numeroOc = $this->valueByHeaders($row, $headerMap, ['#  ORDEN DE COMPRA O SERVICIOS', '# ORDEN DE COMPRA O SERVICIOS', 'ORDEN DE COMPRA', 'NO. ORDEN', 'ORDEN']);
        $suplidor = $this->valueByHeaders($row, $headerMap, ['BENEFICIARIO', 'EMPRESA ADJUDICADA', 'SUPLIDOR', 'PROVEEDOR']);
        $monto = $this->parseAmount($this->valueByHeaders($row, $headerMap, ['MONTO', 'VALOR']));
        $detalle = $this->valueByHeaders($row, $headerMap, ['PROCESO DE COMPRA', 'DETALLE', 'OBJETO', 'DESCRIPCION']);
        $estado = $this->valueByHeaders($row, $headerMap, ['ESTADO', 'STATUS', 'ESTADO DEL PROCEDIMIENTO']);
        $unidad = $this->valueByHeaders($row, $headerMap, ['UNIDAD', 'UNIDAD DE COMPRAS', 'SERVICIO']);
        $rubro = $this->valueByHeaders($row, $headerMap, ['RUBRO', 'CTA', 'CUENTA']);

        return [
            'raw' => $raw,
            'has_data' => $this->rowHasData($row),
            'fecha' => $fecha,
            'numero_oc' => $this->sanitizeText($numeroOc),
            'suplidor' => $this->sanitizeText($suplidor),
            'monto' => $monto,
            'detalle' => $this->sanitizeText($detalle),
            'estado' => $this->sanitizeText($estado),
            'unidad' => $this->sanitizeText($unidad),
            'rubro' => $this->sanitizeText($rubro),
        ];
    }

    private function mapIngresosRow(array $row, array $headerMap): array
    {
        $raw = $this->mapRawRow($row, $headerMap);
        $fecha = $this->parseDate($this->valueByHeaders($row, $headerMap, ['FECHA', 'FECHA DEPOSITO', 'FECHA DEPÓSITO']));
        $origen = $this->valueByHeaders($row, $headerMap, ['ORIGEN', 'ARS']);
        $referencia = $this->valueByHeaders($row, $headerMap, ['REFERENCIA', 'DOCUMENTO', 'NO. DE DOCUMENTO DE REFERENCIA']);
        $monto = $this->parseAmount($this->valueByHeaders($row, $headerMap, ['MONTO', 'VALOR', 'VALOR DE TRANSFERENCIA O CHEQUE']));
        $observacion = $this->valueByHeaders($row, $headerMap, ['OBSERVACION', 'CONCEPTO', 'DETALLE']);
        $banco = $this->valueByHeaders($row, $headerMap, ['BANCO', 'CUENTA']);
        $origenTexto = $this->sanitizeText($origen);
        $esSubtotal = $origenTexto && str_contains($this->normalize($origenTexto), 'SUB');

        return [
            'raw' => $raw,
            'has_data' => $this->rowHasData($row) && !$esSubtotal,
            'fecha' => $fecha,
            'origen' => $this->normalizeIngresoOrigen($origenTexto),
            'referencia' => $this->sanitizeText($referencia),
            'monto' => $monto,
            'observacion' => $this->sanitizeText($observacion),
            'banco' => $this->sanitizeText($banco),
        ];
    }

    private function valueByHeaders(array $row, array $headerMap, array $keys): ?string
    {
        foreach ($keys as $key) {
            $normalized = $this->normalize($key);
            if (array_key_exists($normalized, $headerMap)) {
                $value = $row[$headerMap[$normalized]] ?? null;
                if ($value !== null && trim((string) $value) !== '') {
                    return trim((string) $value);
                }
            }
        }
        return null;
    }

        private function mapRawRow(array $row, array $headerMap): array
    {
        // If headerMap is empty (bad header detection), keep a positional array for debugging.
        if (empty($headerMap)) {
            return array_values($row);
        }

        $raw = [];
        foreach ($headerMap as $header => $index) {
            $raw[$header] = $row[$index] ?? null;
        }
        return $raw;
    }


        private function safeJsonEncode($value): string
    {
        try {
            $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            return $json === false ? '[]' : $json;
        } catch (\Throwable $e) {
            return '[]';
        }
    }

private function readRow(string $filePath, string $sheetName, int $rowNumber): array
    {
        $rows = $this->readRows($filePath, $sheetName, $rowNumber, 1);
        return $rows[0] ?? [];
    }

        private function readRows(string $filePath, string $sheetName, int $startRow, int $chunkSize): array
    {
        // IMPORTANT: Using toArray() after applying a read filter can return rows starting from 1,
        // which breaks the absolute row numbering. Read an explicit range to keep offsets stable.
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly([$sheetName]);

        $filter = new ExcelChunkReadFilter();
        $filter->setRows($startRow, $chunkSize);
        $reader->setReadFilter($filter);

        $spreadsheet = $reader->load($filePath);
        $worksheet = $spreadsheet->getSheetByName($sheetName);

        if (!$worksheet) {
            return [];
        }

        $highestRow = (int) $worksheet->getHighestDataRow();
        $endRow = min($highestRow, $startRow + $chunkSize - 1);
        if ($endRow < $startRow) {
            return [];
        }

        $highestCol = $worksheet->getHighestDataColumn();
        $range = "A{$startRow}:{$highestCol}{$endRow}";

        return $worksheet->rangeToArray($range, null, true, true, false);
    }


    private function iterateRows(string $filePath, string $sheetName, int $startRow, int $chunkSize, callable $callback): void
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $info = $reader->listWorksheetInfo($filePath);
        $totalRows = 0;
        foreach ($info as $sheetInfo) {
            if ($sheetInfo['worksheetName'] === $sheetName) {
                $totalRows = (int) $sheetInfo['totalRows'];
                break;
            }
        }

        for ($row = $startRow; $row <= $totalRows; $row += $chunkSize) {
            $rows = $this->readRows($filePath, $sheetName, $row, $chunkSize);
            foreach ($rows as $offset => $rowData) {
                $absoluteRow = $row + $offset;
                $callback($rowData, $absoluteRow);
            }
        }
    }

    private function rowHasData(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return true;
            }
        }
        return false;
    }

        private function normalize($value): string
    {
        // Normalize headers/labels coming from Excel: uppercase, remove accents, remove punctuation,
        // collapse whitespace (also handles NBSP).
        $value = (string) $value;
        $value = str_replace(["\u{00A0}", "\t", "\r", "\n"], ' ', $value);
        $value = trim($value);
        $value = mb_strtoupper($value, 'UTF-8');
        $value = str_replace(['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ'], ['A', 'E', 'I', 'O', 'U', 'U', 'N'], $value);

        // Replace any non-alphanumeric sequences with a single space.
        $value = preg_replace('/[^A-Z0-9]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }


    private function sanitizeText($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $text = trim((string) $value);
        return $text !== '' ? $text : null;
    }

        private function parseDate($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d');
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        // Excel numeric date
        if (is_numeric($value)) {
            try {
                if (class_exists(\PhpOffice\PhpSpreadsheet\Shared\Date::class)) {
                    $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value);
                    return Carbon::instance($dt)->format('Y-m-d');
                }
            } catch (\Throwable $e) {
                return null;
            }
        }

        $value = str_replace(['.', '-', '\\'], ['/', '/', '/'], $value);

        $formats = ['d/m/Y', 'd/m/y', 'm/d/Y', 'm/d/y', 'Y/m/d', 'Y/d/m'];
        foreach ($formats as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, $value);
                if ($dt !== false) {
                    return $dt->format('Y-m-d');
                }
            } catch (\Throwable $e) {
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }


    private function parseAmount($value): ?float
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }
        $clean = str_replace([' ', ','], ['', ''], (string) $value);
        $clean = str_replace(['$'], '', $clean);
        if (!is_numeric($clean)) {
            return null;
        }
        return round((float) $clean, 2);
    }

    private function normalizeIngresoOrigen(?string $value): ?string
    {
        if (!$value) {
            return null;
        }
        $normalized = $this->normalize($value);
        if (str_contains($normalized, 'ARS')) {
            return 'ARS';
        }
        if (str_contains($normalized, 'GOBIERNO')) {
            return 'GOBIERNO CENTRAL';
        }
        return 'OTROS';
    }

    private function detectSheetByKeywords(array $normalizedNames, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            $normalized = $this->normalize($candidate);
            if (isset($normalizedNames[$normalized])) {
                return $normalizedNames[$normalized];
            }
        }
        foreach ($normalizedNames as $normalized => $original) {
            foreach ($candidates as $candidate) {
                if (str_contains($normalized, $this->normalize($candidate))) {
                    return $original;
                }
            }
        }
        return null;
    }

    private function resolveVendorId(?string $name, int $createdBy): ?int
    {
        if (!$name) {
            return null;
        }
        $vendor = Vender::where('name', $name)->first();
        if ($vendor) {
            return $vendor->id;
        }

        $vendor = Vender::create([
            'vender_id' => 0,
            'name' => $name,
            'email' => Str::slug($name) . '@example.com',
            'password' => bcrypt(Str::random(12)),
            'created_by' => $createdBy,
            'is_active' => 1,
        ]);

        return $vendor->id;
    }

    private function resolveCustomerId(int $createdBy): int
    {
        $name = config('historico_contable.defaults.customer_name');
        $email = config('historico_contable.defaults.customer_email');

        $customer = Customer::where('name', $name)->first();
        if ($customer) {
            return $customer->id;
        }

        $customer = Customer::create([
            'customer_id' => 0,
            'name' => $name,
            'email' => $email,
            'password' => bcrypt(Str::random(12)),
            'created_by' => $createdBy,
            'is_active' => 1,
        ]);

        return $customer->id;
    }

    private function resolveBankAccountId(?string $banco): ?int
    {
        $defaultId = config('historico_contable.defaults.bank_account_id');
        if (!$banco) {
            return $defaultId ?? 0;
        }

        $account = BankAccount::where('bank_name', 'like', '%' . $banco . '%')
            ->orWhere('holder_name', 'like', '%' . $banco . '%')
            ->first();

        return $account ? $account->id : ($defaultId ?? 0);
    }

    private function resolvePaymentMethod(?string $method): int
    {
        if (!$method) {
            return 0;
        }

        $method = $this->normalize($method);
        $map = config('historico_contable.payment_method_map');
        foreach ($map as $label => $value) {
            if (str_contains($method, $this->normalize($label))) {
                return (int) $value;
            }
        }

        return 0;
    }

    private function monthNumberToSpanish(int $month): ?string
    {
        $map = [
            1 => 'ENERO',
            2 => 'FEBRERO',
            3 => 'MARZO',
            4 => 'ABRIL',
            5 => 'MAYO',
            6 => 'JUNIO',
            7 => 'JULIO',
            8 => 'AGOSTO',
            9 => 'SEPTIEMBRE',
            10 => 'OCTUBRE',
            11 => 'NOVIEMBRE',
            12 => 'DICIEMBRE',
        ];

        return $map[$month] ?? null;
    }

    public function migrateAsientos(string $monthFolder, array $options = []): array
    {
        if (!class_exists(\App\Services\HistoricoContableJournalService::class)) {
            throw new \RuntimeException(
                'No se encontró App\\Services\\HistoricoContableJournalService. ' .
                'Asegúrate de crear el archivo app/Services/HistoricoContableJournalService.php y ejecutar composer dump-autoload.'
            );
        }

        $journal = app()->make(\App\Services\HistoricoContableJournalService::class);

        $batchIds = $this->resolveBatchIdsForMonth($monthFolder);
        $summary = [
            'month' => $monthFolder,
            'batch_ids' => $batchIds,
            'payments' => 0,
            'revenues' => 0,
            'bills' => 0,
            'skipped' => 0,
        ];

        foreach ($batchIds as $batchId) {
            $res = $journal->generateForBatch((int) $batchId, $options);
            foreach (['payments','revenues','bills','skipped'] as $k) {
                $summary[$k] += (int) ($res[$k] ?? 0);
            }
        }

        return $summary;
    }

    public function rollbackAsientos(string $monthFolder): array
    {
        if (!class_exists(\App\Services\HistoricoContableJournalService::class)) {
            throw new \RuntimeException(
                'No se encontró App\\Services\\HistoricoContableJournalService. ' .
                'Asegúrate de crear el archivo app/Services/HistoricoContableJournalService.php y ejecutar composer dump-autoload.'
            );
        }

        $journal = app()->make(\App\Services\HistoricoContableJournalService::class);

        $batchIds = $this->resolveBatchIdsForMonth($monthFolder);
        $summary = [
            'month' => $monthFolder,
            'batch_ids' => $batchIds,
            'deleted_entries' => 0,
        ];

        foreach ($batchIds as $batchId) {
            $res = $journal->rollbackBatch((int) $batchId);
            $summary['deleted_entries'] += (int) ($res['deleted_entries'] ?? 0);
        }

        return $summary;
    }

    private function resolveBatchIdsForMonth(string $monthFolder): array
    {
        $batchIds = [];

        $pStageIds = DB::table('staging_pagos_emitidos')
            ->where('source_month_folder', $monthFolder)
            ->pluck('id')
            ->all();

        if (!empty($pStageIds)) {
            $batchIds = array_merge(
                $batchIds,
                Payment::whereIn('staging_id', $pStageIds)->distinct()->pluck('migration_batch_id')->all()
            );
        }

        $rStageIds = DB::table('staging_ingresos_origen')
            ->where('source_month_folder', $monthFolder)
            ->pluck('id')
            ->all();

        if (!empty($rStageIds)) {
            $batchIds = array_merge(
                $batchIds,
                Revenue::whereIn('staging_id', $rStageIds)->distinct()->pluck('migration_batch_id')->all()
            );
        }

        $bStageIds = DB::table('staging_ordenes_compra')
            ->where('source_month_folder', $monthFolder)
            ->pluck('id')
            ->all();

        if (!empty($bStageIds)) {
            $batchIds = array_merge(
                $batchIds,
                Bill::whereIn('staging_id', $bStageIds)->distinct()->pluck('migration_batch_id')->all()
            );
        }

        $batchIds = array_values(array_unique(array_filter($batchIds, fn($x) => $x !== null)));

        sort($batchIds);
        return $batchIds;
    }

}
