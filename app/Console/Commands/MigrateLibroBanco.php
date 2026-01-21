<?php

namespace App\Console\Commands;

use App\Services\HistoricoContableMigrationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateLibroBanco extends Command
{
    protected $signature = 'migrate:libro-banco
        {--month= : Mes a migrar (e.g. "ENERO 2025" o "01-2025")}
        {--all : Migrar todos los meses detectados}
        {--dry-run : Solo staging y validación, sin cargar a producción}
        {--chunk=500 : Tamaño de chunk de lectura}
        {--cuenta-recaudadora-id= : Cuenta recaudadora por defecto}';

    protected $description = 'Migración del libro banco (LIBRO BANCO) desde Excel a staging y movimientos bancarios.';

    public function handle(HistoricoContableMigrationService $service): int
    {
        $rootPath = base_path('HistoricoContable');
        $dryRun = (bool) $this->option('dry-run');
        $chunkSize = (int) $this->option('chunk') ?: 500;
        $cuentaRecaudadoraId = $this->option('cuenta-recaudadora-id') ?: config('historico_contable.defaults.cuenta_recaudadora_id');

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
            $this->info('Procesando libro banco: ' . $monthFolder);
            $monthPath = $rootPath . '/' . $monthFolder;

            $bankInfo = $service->findBankFile($monthPath);
            if (!$bankInfo['selected']) {
                $this->warn('No se encontró archivo de banco con hoja LIBRO BANCO para ' . $monthFolder);
                continue;
            }

            $filePath = $bankInfo['selected'];
            $sheetName = $service->resolveLibroBancoSheet($filePath);
            if (!$sheetName) {
                $this->warn('No se detectó la hoja LIBRO BANCO en ' . basename($filePath));
                continue;
            }

            if (!empty($bankInfo['duplicates'])) {
                $this->warn('Se detectaron archivos de banco adicionales en ' . $monthFolder . ': ' . implode(', ', array_map('basename', $bankInfo['duplicates'])));
            }

            $batchId = DB::table('migration_batches')->insertGetId([
                'month_folder' => $monthFolder,
                'source_file' => basename($filePath),
                'status' => $dryRun ? 'DRY_RUN' : 'RUNNING',
                'started_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $logFile = storage_path('logs/libro_banco_' . str_replace(' ', '_', $monthFolder) . '.log');
            Log::build(['driver' => 'single', 'path' => $logFile])->info('Inicio migración libro banco', [
                'month' => $monthFolder,
                'file' => $filePath,
                'dry_run' => $dryRun,
            ]);

            $stageCount = $service->stageLibroBanco($monthFolder, $filePath, $sheetName, $batchId, $chunkSize);
            $validation = $service->validateLibroBanco($monthFolder);
            $errorFiles = $service->exportErrors($monthFolder, storage_path('app/historico_contable'));

            if ($dryRun) {
                DB::table('migration_batches')->where('id', $batchId)->update([
                    'status' => 'DRY_RUN',
                    'finished_at' => now(),
                    'totals_json' => json_encode([
                        'staging' => ['libro_banco' => $stageCount],
                        'validation' => $validation,
                        'error_files' => $errorFiles,
                    ], JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]);
                $this->info('Dry-run libro banco completado: ' . $monthFolder);
                continue;
            }

            $imports = $service->importLibroBanco($monthFolder, $batchId, [
                'cuenta_recaudadora_id' => $cuentaRecaudadoraId,
            ]);

            DB::table('migration_batches')->where('id', $batchId)->update([
                'status' => 'SUCCESS',
                'finished_at' => now(),
                'totals_json' => json_encode([
                    'staging' => ['libro_banco' => $stageCount],
                    'validation' => $validation,
                    'imports' => ['libro_banco' => $imports],
                    'error_files' => $errorFiles,
                ], JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);

            $this->info('Migración libro banco completada: ' . $monthFolder);
        }

        return self::SUCCESS;
    }
}
