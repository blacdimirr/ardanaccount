<?php

namespace App\Http\Controllers;

use App\Exports\NominaIr3Export;
use App\Exports\NominaIr4Export;
use App\Services\NominaFiscalReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class NominaReporteFiscalController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->can('nomina_ir3_generate') && !\Auth::user()->can('nomina_ir4_generate')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $currentYear = (int) Carbon::now()->format('Y');
        $years = collect(range($currentYear, $currentYear - 5))
            ->mapWithKeys(fn($year) => [$year => $year]);
        $months = collect([
            1 => __('Enero'),
            2 => __('Febrero'),
            3 => __('Marzo'),
            4 => __('Abril'),
            5 => __('Mayo'),
            6 => __('Junio'),
            7 => __('Julio'),
            8 => __('Agosto'),
            9 => __('Septiembre'),
            10 => __('Octubre'),
            11 => __('Noviembre'),
            12 => __('Diciembre'),
        ]);

        return view('nomina.reportes_fiscales.index', compact('months', 'years'));
    }

    public function generarIr3(Request $request, NominaFiscalReportService $service)
    {
        if (!\Auth::user()->can('nomina_ir3_generate')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'mes' => 'required|integer|min:1|max:12',
            'anio' => 'required|integer|min:2000|max:2100',
            'formato' => 'required|string|in:excel,txt',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $creatorId = \Auth::user()->creatorId();
        $rows = $service->generarIr3((int) $request->mes, (int) $request->anio, $creatorId);

        if ($request->formato === 'txt') {
            $filename = "ir3-{$request->anio}-{$request->mes}.txt";

            return $this->downloadTxt($rows, $filename);
        }

        return Excel::download(
            new NominaIr3Export($rows),
            "ir3-{$request->anio}-{$request->mes}.xlsx"
        );
    }

    public function generarIr4(Request $request, NominaFiscalReportService $service)
    {
        if (!\Auth::user()->can('nomina_ir4_generate')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'anio' => 'required|integer|min:2000|max:2100',
            'formato' => 'required|string|in:excel,txt',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $creatorId = \Auth::user()->creatorId();
        $rows = $service->generarIr4((int) $request->anio, $creatorId);

        if ($request->formato === 'txt') {
            $filename = "ir4-{$request->anio}.txt";

            return $this->downloadTxt($rows, $filename);
        }

        return Excel::download(
            new NominaIr4Export($rows),
            "ir4-{$request->anio}.xlsx"
        );
    }

    private function downloadTxt($rows, string $filename)
    {
        $lines = collect([
            ['Documento', 'Empleado', 'Tipo contribuyente', 'ISR retenido'],
        ])->merge($rows->map(function ($row) {
            return [
                $row['documento'],
                $row['empleado'],
                $row['tipo_contribuyente'],
                number_format($row['isr'], 2, '.', ''),
            ];
        }));

        $content = $lines
            ->map(fn($line) => implode("\t", $line))
            ->implode("\n");

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
