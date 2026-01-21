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
        ];
    }

    public function stageMonth(string $rootPath, string $monthFolder, string $filePath, array $sheets, bool $dryRun, int $chunkSize = 500): array
    {
        $results = [
            'pagos' => 0,
            'ordenes_compra' => 0,
            'ingresos' => 0,
        ];

        if ($sheets['pagos']) {
            $results['pagos'] = $this->stagePagos($monthFolder, $filePath, $sheets['pagos'], $chunkSize);
        }
        if ($sheets['ordenes_compra']) {
            $results['ordenes_compra'] = $this->stageOrdenesCompra($monthFolder, $filePath, $sheets['ordenes_compra'], $chunkSize);
        }
        if ($sheets['ingresos']) {
            $results['ingresos'] = $this->stageIngresos($monthFolder, $filePath, $sheets['ingresos'], $chunkSize);
        }

        if ($dryRun) {
            return $results;
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

    private function stagePagos(string $monthFolder, string $filePath, string $sheetName, int $chunkSize): int
    {
        $headerRow = $this->detectHeaderRow($filePath, $sheetName, config('historico_contable.sheet_keywords.pagos'));
        if (!$headerRow) {
            return 0;
        }

        $headers = $this->readRow($filePath, $sheetName, $headerRow);
        $headerMap = $this->buildHeaderMap($headers);

        $rowsInserted = 0;
        $this->iterateRows($filePath, $sheetName, $headerRow + 1, $chunkSize, function ($row, $rowNumber) use ($monthFolder, $filePath, $sheetName, $headerMap, &$rowsInserted) {
            $mapped = $this->mapPagosRow($row, $headerMap);
            if (!$mapped['has_data']) {
                return;
            }
            $hash = sha1(implode('|', [
                $monthFolder,
                $mapped['fecha'] ?? '',
                $mapped['referencia'] ?? '',
                $mapped['suplidor'] ?? '',
                $mapped['monto'] ?? '',
                $mapped['metodo'] ?? '',
            ]));

            $data = [
                'source_month_folder' => $monthFolder,
                'source_file' => basename($filePath),
                'source_sheet' => $sheetName,
                'source_row_number' => $rowNumber,
                'raw_json' => $this->safeJsonEncode($mapped['raw']),
                'hash' => $hash,
                'status' => self::STATUS_NEW,
                'error_message' => null,
                'fecha' => $mapped['fecha'],
                'monto' => $mapped['monto'],
                'referencia' => $mapped['referencia'],
                'suplidor' => $mapped['suplidor'],
                'metodo' => $mapped['metodo'],
                'cheque' => $mapped['cheque'],
                'transferencia' => $mapped['transferencia'],
                'libramiento' => $mapped['libramiento'],
                'concepto' => $mapped['concepto'],
                'orden_compra' => $mapped['orden_compra'],
                'banco' => $mapped['banco'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $inserted = DB::table('staging_pagos_emitidos')->insertOrIgnore($data);
            if ($inserted) {
                $rowsInserted++;
            }
        });

        return $rowsInserted;
    }

    private function stageOrdenesCompra(string $monthFolder, string $filePath, string $sheetName, int $chunkSize): int
    {
        $headerRow = $this->detectHeaderRow($filePath, $sheetName, config('historico_contable.sheet_keywords.ordenes_compra'));
        if (!$headerRow) {
            return 0;
        }

        $headers = $this->readRow($filePath, $sheetName, $headerRow);
        $headerMap = $this->buildHeaderMap($headers);

        $rowsInserted = 0;
        $this->iterateRows($filePath, $sheetName, $headerRow + 1, $chunkSize, function ($row, $rowNumber) use ($monthFolder, $filePath, $sheetName, $headerMap, &$rowsInserted) {
            $mapped = $this->mapOrdenesCompraRow($row, $headerMap);
            if (!$mapped['has_data']) {
                return;
            }

            $hash = sha1(implode('|', [
                $monthFolder,
                $mapped['fecha'] ?? '',
                $mapped['numero_oc'] ?? '',
                $mapped['suplidor'] ?? '',
                $mapped['monto'] ?? '',
            ]));

            $data = [
                'source_month_folder' => $monthFolder,
                'source_file' => basename($filePath),
                'source_sheet' => $sheetName,
                'source_row_number' => $rowNumber,
                'raw_json' => $this->safeJsonEncode($mapped['raw']),
                'hash' => $hash,
                'status' => self::STATUS_NEW,
                'error_message' => null,
                'fecha' => $mapped['fecha'],
                'monto' => $mapped['monto'],
                'numero_oc' => $mapped['numero_oc'],
                'suplidor' => $mapped['suplidor'],
                'detalle' => $mapped['detalle'],
                'estado' => $mapped['estado'],
                'unidad' => $mapped['unidad'],
                'rubro' => $mapped['rubro'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $inserted = DB::table('staging_ordenes_compra')->insertOrIgnore($data);
            if ($inserted) {
                $rowsInserted++;
            }
        });

        return $rowsInserted;
    }

    private function stageIngresos(string $monthFolder, string $filePath, string $sheetName, int $chunkSize): int
    {
        $headerRow = $this->detectHeaderRow($filePath, $sheetName, config('historico_contable.sheet_keywords.ingresos'));
        if (!$headerRow) {
            return 0;
        }

        $headers = $this->readRow($filePath, $sheetName, $headerRow);
        $headerMap = $this->buildHeaderMap($headers);

        $rowsInserted = 0;
        $this->iterateRows($filePath, $sheetName, $headerRow + 1, $chunkSize, function ($row, $rowNumber) use ($monthFolder, $filePath, $sheetName, $headerMap, &$rowsInserted) {
            $mapped = $this->mapIngresosRow($row, $headerMap);
            if (!$mapped['has_data']) {
                return;
            }

            $hash = sha1(implode('|', [
                $monthFolder,
                $mapped['fecha'] ?? '',
                $mapped['origen'] ?? '',
                $mapped['referencia'] ?? '',
                $mapped['monto'] ?? '',
            ]));

            $data = [
                'source_month_folder' => $monthFolder,
                'source_file' => basename($filePath),
                'source_sheet' => $sheetName,
                'source_row_number' => $rowNumber,
                'raw_json' => $this->safeJsonEncode($mapped['raw']),
                'hash' => $hash,
                'status' => self::STATUS_NEW,
                'error_message' => null,
                'fecha' => $mapped['fecha'],
                'monto' => $mapped['monto'],
                'origen' => $mapped['origen'],
                'referencia' => $mapped['referencia'],
                'observacion' => $mapped['observacion'],
                'banco' => $mapped['banco'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

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

        // Matriz ENERO 2025 (and similar): headers are at fixed rows per sheet
        if (str_contains($s, 'RELACION CHEQUES EMITIDOS') || str_contains($s, 'RELACION DE CHEQUES EMITIDOS') || str_contains($s, 'RELACION DE PAGOS EMITIDOS') || str_contains($s, 'RELACION PAGOS EMITIDOS')) {
            return 9;
        }

        if (str_contains($s, 'RELACION ORDENES DE COMPRAS') || str_contains($s, 'RELACION DE ORDENES DE COMPRAS')) {
            return 4;
        }

        if (str_contains($s, 'INGRESOS SEGUN ORIGEN')) {
            return 6;
        }

        return null;
    }

private function detectHeaderRow(string $filePath, string $sheetName, array $keywords): ?int
    {
        if ($fixed = $this->fixedHeaderRow($sheetName)) {
            return $fixed;
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
                $bestRow = $idx + 1;
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

        // If header detection failed for any reason, fall back to fixed column positions (A-E)
        // for the "INGRESOS SEGUN ORIGEN" sheet in the Matriz.
        $fecha = null;
        $origen = null;
        $referencia = null;
        $monto = null;

        if (empty($headerMap)) {
            $origen = $row[0] ?? null;
            $ars = $row[1] ?? null;
            $origen = $origen ?: $ars;

            $fecha = $this->parseDate($row[2] ?? null);
            $referencia = $row[3] ?? null;
            $monto = $this->parseAmount($row[4] ?? null);
        } else {
            $fecha = $this->parseDate($this->valueByHeaders($row, $headerMap, [
                'FECHA',
                'FECHA DEPOSITO',
                'FECHA DEPÓSITO',
            ]));

            $origen = $this->valueByHeaders($row, $headerMap, ['ORIGEN', 'ARS']);

            // Header in Matriz: "No. De Documento de referencia"
            $referencia = $this->valueByHeaders($row, $headerMap, [
                'NO DE DOCUMENTO DE REFERENCIA',
                'NO. DE DOCUMENTO DE REFERENCIA',
                'REFERENCIA',
                'DOCUMENTO',
            ]);

            // Header in Matriz: "Valor transferido segun No. De Transferencia o Cheque"
            $monto = $this->parseAmount($this->valueByHeaders($row, $headerMap, [
                'VALOR TRANSFERIDO SEGUN NO DE TRANSFERENCIA O CHEQUE',
                'VALOR DE TRANSFERENCIA O CHEQUE',
                'VALOR TRANSFERIDO',
                'MONTO',
                'VALOR',
            ]));
        }

        $observacion = $this->valueByHeaders($row, $headerMap, ['OBSERVACION', 'CONCEPTO', 'DETALLE']);
        $banco = $this->valueByHeaders($row, $headerMap, ['BANCO', 'CUENTA']);

        $origenTexto = $this->sanitizeText($origen);

        // Ignore subtotal/total rows that often have amounts (formulas) but no date.
        $normOrigen = $origenTexto ? $this->normalize($origenTexto) : '';
        $esSubtotal = $normOrigen !== '' && (
            str_contains($normOrigen, 'SUBTOTAL') ||
            str_contains($normOrigen, 'SUB TOTAL') ||
            $normOrigen === 'TOTAL' ||
            str_contains($normOrigen, 'TOTAL GENERAL') ||
            str_contains($normOrigen, 'ANTICIPOS FINANCIEROS') ||
            str_contains($normOrigen, 'FONDO 100') ||
            str_contains($normOrigen, 'FONDOS ASISTENCIAL')
        );


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


    private function valueByHeaders(array $row, array $headerMap, array $keys)
    {
        foreach ($keys as $key) {
            $normalized = $this->normalize($key);

            if (!array_key_exists($normalized, $headerMap)) {
                continue;
            }

            $value = $row[$headerMap[$normalized]] ?? null;

            // Keep native types (DateTime, numbers) so parseDate/parseAmount can work reliably.
            if ($value instanceof \DateTimeInterface) {
                return $value;
            }

            if (is_int($value) || is_float($value)) {
                return $value;
            }

            if ($value !== null) {
                $str = trim((string) $value);
                if ($str !== '') {
                    return $str;
                }
            }
        }

        return null;
    }

    private function mapRawRow(array $row, array $headerMap): array
    {
        // If we couldn't build a header map, keep the raw row by numeric index so we can debug later.
        if (empty($headerMap)) {
            return $row;
        }

        $raw = [];
        foreach ($headerMap as $header => $index) {
            $raw[$header] = $row[$index] ?? null;
        }
        return $raw;
    }


    private function readRow(string $filePath, string $sheetName, int $rowNumber): array
    {
        $rows = $this->readRows($filePath, $sheetName, $rowNumber, 1);
        return $rows[0] ?? [];
    }

    private function readRows(string $filePath, string $sheetName, int $startRow, int $chunkSize): array
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly([$sheetName]);

        $spreadsheet = $reader->load($filePath);
        $worksheet = $spreadsheet->getSheetByName($sheetName);

        if (!$worksheet) {
            return [];
        }

        $highestRow = (int) $worksheet->getHighestRow();
        if ($startRow > $highestRow) {
            return [];
        }

        $endRow = min($highestRow, $startRow + $chunkSize - 1);
        $highestColumn = $worksheet->getHighestColumn();

        // Read ONLY the requested range. This avoids row-number drift caused by Worksheet::toArray()
        // iterating from row 1..highestRow even when a read filter is used.
        $range = sprintf('A%d:%s%d', $startRow, $highestColumn, $endRow);

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
        $value = trim((string) $value);
        $value = mb_strtoupper($value, 'UTF-8');
        $value = str_replace(['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ'], ['A', 'E', 'I', 'O', 'U', 'U', 'N'], $value);

        // Remove punctuation/symbols and normalize whitespace (helps with headers like "No. De Documento ...")
        $value = preg_replace('/[^A-Z0-9]+/u', ' ', $value);
        $value = preg_replace('/\s+/u', ' ', $value);

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

    private function safeJsonEncode($value): string
    {
        // Avoid empty raw_json when there are invalid UTF-8 characters coming from Excel,
        // and ensure DateTime objects are serializable.
        $normalize = function ($v) use (&$normalize) {
            if ($v instanceof \DateTimeInterface) {
                return (new \Carbon\Carbon($v))->format('Y-m-d');
            }
            if (is_array($v)) {
                $out = [];
                foreach ($v as $k => $vv) {
                    $out[$k] = $normalize($vv);
                }
                return $out;
            }
            if (is_object($v)) {
                // Best-effort stringify for unexpected objects
                return method_exists($v, '__toString') ? (string) $v : get_class($v);
            }
            return $v;
        };

        try {
            $json = json_encode($normalize($value), JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            return $json === false ? '[]' : $json;
        } catch (\Throwable $e) {
            return '[]';
        }
    }

    private function parseDate($value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Already a DateTime object (common when using PhpSpreadsheet)
        if ($value instanceof \DateTimeInterface) {
            return (new \Carbon\Carbon($value))->format('Y-m-d');
        }

        $str = trim((string) $value);
        if ($str === '') {
            return null;
        }

        // Excel numeric date serial
        if (is_numeric($value)) {
            try {
                if (class_exists(\PhpOffice\PhpSpreadsheet\Shared\Date::class)) {
                    $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value);
                    return \Carbon\Carbon::instance($dt)->format('Y-m-d');
                }
            } catch (\Throwable $e) {
                // continue to other parsing strategies
            }
        }

        // Normalize separators
        $str = str_replace(['.', '-', ' '], ['/', '/', '/'], $str);

        // Try common formats (add 2-digit year too)
        $formats = ['d/m/Y', 'd/m/y', 'm/d/Y', 'm/d/y', 'Y/m/d', 'Y/d/m'];
        foreach ($formats as $fmt) {
            try {
                $dt = \Carbon\Carbon::createFromFormat($fmt, $str);
                if ($dt !== false) {
                    return $dt->format('Y-m-d');
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // Last resort: Carbon parser
        try {
            return \Carbon\Carbon::parse($str)->format('Y-m-d');
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
}
