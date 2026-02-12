<?php

namespace App\Console\Commands;

use App\Services\HistoricoContableJournalService;
use App\Services\HistoricoContableMigrationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateHistoricoAsientos extends Command
{
    protected $signature = 'migrate:historico:asientos
        {--month= : Mes para generar asientos (e.g. "ENERO 2025" o "01-2025")}
        {--batch-id= : Batch específico}
        {--created-by= : Usuario creador (created_by)}
        {--matriz-file= : Ruta absoluta opcional al archivo Matriz}';

    protected $description = 'Genera asientos contables históricos desde documentos migrados y hoja CUENTA T VS.';

    public function handle(HistoricoContableJournalService $service, HistoricoContableMigrationService $migrationService): int
    {
        $createdBy = (int) ($this->option('created-by') ?: config('historico_contable.defaults.created_by'));
        $batchId = $this->option('batch-id');
        $resolvedMonth = null;

        if (!$batchId) {
            $monthInput = (string) $this->option('month');
            if (!$monthInput) {
                $this->error('Debes indicar --month= o --batch-id=');
                return self::FAILURE;
            }

            $resolvedMonth = $migrationService->resolveMonthFolder(base_path('HistoricoContable'), $monthInput) ?? $monthInput;

            $batch = DB::table('migration_batches')
                ->where('month_folder', $resolvedMonth)
                ->orderByDesc('id')
                ->first();

            if (!$batch) {
                $this->error('No se encontró batch para el mes: ' . $resolvedMonth);
                return self::FAILURE;
            }

            $batchId = $batch->id;
        } else {
            $batch = DB::table('migration_batches')->where('id', (int) $batchId)->first();
            if (!$batch) {
                $this->error('No se encontró el batch: ' . $batchId);
                return self::FAILURE;
            }
            $resolvedMonth = $batch->month_folder;
        }

        $matrizFile = $this->option('matriz-file');
        if (!$matrizFile) {
            $monthPath = base_path('HistoricoContable/' . $resolvedMonth);
            $matrizSelection = $migrationService->findMatrizFile($monthPath);
            $matrizFile = $matrizSelection['selected'] ?? null;
        }

        if (!$matrizFile || !is_file($matrizFile)) {
            $this->error('No se encontró archivo MATRIZ para el mes: ' . $resolvedMonth);
            return self::FAILURE;
        }

        $summary = $service->generateForBatch((int) $batchId, [
            'created_by' => $createdBy,
            'matriz_file' => $matrizFile,
            'cuenta_t_vs_sheet' => 'CUENTA T VS',
            'bank_account_code' => '1101010001',
            'withholding_account_code' => '210306',
        ]);

        $this->info('Asientos generados para batch ' . $batchId);
        $this->line('Matriz usada: ' . $matrizFile);
        $this->line(json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }
}
