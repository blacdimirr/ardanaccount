<?php

namespace App\Console\Commands;

use App\Services\HistoricoContableMigrationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RollbackMigrationBatch extends Command
{
    protected $signature = 'migrate:rollback {--month= : Mes a revertir (e.g. "ENERO 2025" o "01-2025")}';

    protected $description = 'Revierte una migración histórica (documentos y libro banco) por mes.';

    public function handle(HistoricoContableMigrationService $service): int
    {
        $rootPath = base_path('HistoricoContable');
        $monthInput = (string) $this->option('month');
        if (!$monthInput) {
            $this->error('Debes indicar --month=');
            return self::FAILURE;
        }

        $resolved = $service->resolveMonthFolder($rootPath, $monthInput);
        if (!$resolved) {
            $this->error('No se encontró la carpeta para el mes: ' . $monthInput);
            return self::FAILURE;
        }

        $batch = DB::table('migration_batches')
            ->where('month_folder', $resolved)
            ->orderByDesc('id')
            ->first();

        if (!$batch) {
            $this->error('No se encontró batch para el mes: ' . $resolved);
            return self::FAILURE;
        }

        DB::transaction(function () use ($service, $batch) {
            $service->rollbackMonth($batch->id);
            $service->rollbackLibroBanco($batch->id);
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
