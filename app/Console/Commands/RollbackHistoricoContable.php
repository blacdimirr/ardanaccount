<?php

namespace App\Console\Commands;

use App\Services\HistoricoContableJournalService;
use App\Services\HistoricoContableMigrationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RollbackHistoricoContable extends Command
{
    protected $signature = 'migrate:historico:rollback {--month= : Mes a revertir (e.g. "ENERO 2025" o "01-2025")}';

    protected $description = 'Revierte una migración histórica por mes (batch).';

    public function handle(HistoricoContableMigrationService $service, HistoricoContableJournalService $journalService): int
    {
        $monthInput = (string) $this->option('month');
        if (!$monthInput) {
            $this->error('Debes indicar --month=');
            return self::FAILURE;
        }

        $rootPath = base_path('HistoricoContable');
        $resolved = $service->resolveMonthFolder($rootPath, $monthInput) ?? $monthInput;

        $batch = DB::table('migration_batches')
            ->where('month_folder', $resolved)
            ->orderByDesc('id')
            ->first();

        if (!$batch) {
            $this->error('No se encontró batch para el mes: ' . $resolved);
            return self::FAILURE;
        }

        DB::transaction(function () use ($service, $journalService, $batch) {
            $journalService->rollbackBatch($batch->id);
            $service->rollbackMonth($batch->id);
            DB::table('migration_batches')
                ->where('id', $batch->id)
                ->update([
                    'status' => 'ROLLED_BACK',
                    'finished_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        $this->info('Rollback completado para batch ' . $batch->id . ' (' . $resolved . ').');
        return self::SUCCESS;
    }
}
