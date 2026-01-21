<?php

namespace App\Console\Commands;

use App\Services\HistoricoContableMigrationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateHistoricoContable extends Command
{
    protected $signature = 'migrate:historico
        {--month= : Mes a migrar (e.g. "ENERO 2025" o "01-2025")}
        {--all : Migrar todos los meses detectados}
        {--dry-run : Solo staging y validación, sin cargar a producción}
        {--chunk=500 : Tamaño de chunk de lectura}
        {--created-by= : Usuario creador (created_by)}
        {--category-id= : category_id por defecto}
        {--product-id= : product_id para detalle de OC}';

    protected $description = 'Migración histórica contable (pagos, OC, ingresos) desde Excel a staging y producción.';

    public function handle(HistoricoContableMigrationService $service): int
    {
        $rootPath = base_path('HistoricoContable');
        $dryRun = (bool) $this->option('dry-run');
        $chunkSize = (int) $this->option('chunk') ?: 500;
        $createdBy = $this->option('created-by') ?: config('historico_contable.defaults.created_by');
        $categoryId = $this->option('category-id') ?: config('historico_contable.defaults.category_id');
        $productId = $this->option('product-id') ?: config('historico_contable.defaults.product_id');

        $months = [];
        if ($this->option('all')) {
            $months = $service->listMonthFolders($rootPath);
        } else {
            $monthInput = (string) $this->option('month');
            if (!$monthInput) {
                $this->error('Debes indicar --month= o usar --all.');
                return self::FAILURE;
            }
            $resolved = $service->resolveMonthFolder($rootPath, $monthInput);
            if (!$resolved) {
                $this->error('No se encontró la carpeta para el mes: ' . $monthInput);
                return self::FAILURE;
            }
            $months = [$resolved];
        }

        foreach ($months as $monthFolder) {
            $this->info('Procesando mes: ' . $monthFolder);
            $monthPath = $rootPath . '/' . $monthFolder;

            $fileInfo = $service->findMatrizFile($monthPath);
            if (!$fileInfo['selected']) {
                $this->warn('No se encontró archivo MATRIZ para ' . $monthFolder);
                continue;
            }

            $filePath = $fileInfo['selected'];
            $sheetNames = $service->listSheetNames($filePath);
            $sheets = $service->detectSheets($sheetNames);

            if (!$sheets['pagos'] || !$sheets['ordenes_compra'] || !$sheets['ingresos']) {
                $this->warn('No se detectaron todas las hojas requeridas para ' . $monthFolder);
            }

            $batchId = DB::table('migration_batches')->insertGetId([
                'month_folder' => $monthFolder,
                'source_file' => basename($filePath),
                'status' => $dryRun ? 'DRY_RUN' : 'RUNNING',
                'started_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $logFile = storage_path('logs/historico_contable_' . str_replace(' ', '_', $monthFolder) . '.log');
            Log::build(['driver' => 'single', 'path' => $logFile])->info('Inicio migración', [
                'month' => $monthFolder,
                'file' => $filePath,
                'dry_run' => $dryRun,
            ]);

            $stageCounts = $service->stageMonth($rootPath, $monthFolder, $filePath, $sheets, $dryRun, $chunkSize);
            $validation = $service->validateMonth($monthFolder);

            $errorFiles = $service->exportErrors($monthFolder, storage_path('app/historico_contable'));

            if ($dryRun) {
                DB::table('migration_batches')->where('id', $batchId)->update([
                    'status' => 'DRY_RUN',
                    'finished_at' => now(),
                    'totals_json' => json_encode([
                        'staging' => $stageCounts,
                        'validation' => $validation,
                        'error_files' => $errorFiles,
                    ], JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]);
                $this->info('Dry-run completado: ' . $monthFolder);
                continue;
            }

            $imports = $service->importMonth($monthFolder, $batchId, [
                'created_by' => $createdBy,
                'category_id' => $categoryId,
                'product_id' => $productId,
            ]);

            DB::table('migration_batches')->where('id', $batchId)->update([
                'status' => 'SUCCESS',
                'finished_at' => now(),
                'totals_json' => json_encode([
                    'staging' => $stageCounts,
                    'validation' => $validation,
                    'imports' => $imports,
                    'error_files' => $errorFiles,
                ], JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);

            $this->info('Migración completada: ' . $monthFolder);
        }

        return self::SUCCESS;
    }
}
