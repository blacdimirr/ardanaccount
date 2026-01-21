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
        {--created-by= : Usuario creador (created_by)}';

    protected $description = 'Genera asientos contables históricos desde documentos migrados.';

    public function handle(HistoricoContableJournalService $service, HistoricoContableMigrationService $migrationService): int
    {
        $createdBy = (int) ($this->option('created-by') ?: config('historico_contable.defaults.created_by'));
        $batchId = $this->option('batch-id');

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
        }

        $summary = $service->generateForBatch((int) $batchId, ['created_by' => $createdBy]);

        $this->info('Asientos generados para batch ' . $batchId);
        $this->line(json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }
}
