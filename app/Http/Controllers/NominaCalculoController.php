<?php

namespace App\Http\Controllers;

use App\Exports\NominaAportesExport;
use App\Models\Empleado;
use App\Models\NominaPeriodo;
use App\Services\NominaAportesSsService;
use App\Services\NominaIsrService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class NominaCalculoController extends Controller
{
    public function index(Request $request, NominaAportesSsService $service, NominaIsrService $isrService)
    {
        if (!\Auth::user()->can('nomina_periodos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $creatorId = \Auth::user()->creatorId();
        $periodos = NominaPeriodo::where('created_by', $creatorId)->get();

        $periodoSeleccionado = null;
        $calculos = collect();

        if ($request->filled('nomina_periodo_id')) {
            $periodoSeleccionado = $periodos->firstWhere('id', $request->nomina_periodo_id);

            if ($periodoSeleccionado) {
                $config = $service->getConfig($creatorId);
                $empleados = Empleado::where('created_by', $creatorId)->get();

                $calculos = $empleados->map(function ($empleado) use ($service, $isrService, $config, $periodoSeleccionado, $creatorId) {
                    $baseImponible = $service->baseImponible($periodoSeleccionado->id, $empleado->id, $creatorId);
                    $aportes = $service->calcularAportes($baseImponible, $config);
                    $isr = $isrService->calcularIsr($baseImponible, $empleado, $creatorId);

                    return [
                        'empleado' => $empleado,
                        'base_imponible' => $baseImponible,
                        'isr' => $isr,
                        'tss_empleado' => $aportes['tss_empleado'],
                        'infotep_empleado' => $aportes['infotep_empleado'],
                        'idoppril_empleado' => $aportes['idoppril_empleado'],
                        'tss_empleador' => $aportes['tss_empleador'],
                        'infotep_empleador' => $aportes['infotep_empleador'],
                        'idoppril_empleador' => $aportes['idoppril_empleador'],
                    ];
                });
            }
        }

        return view('nomina.calculos.index', compact('periodos', 'periodoSeleccionado', 'calculos'));
    }

    public function calcular(Request $request, NominaAportesSsService $service, NominaIsrService $isrService)
    {
        if (!\Auth::user()->can('nomina_periodos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'nomina_periodo_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $creatorId = \Auth::user()->creatorId();
        $periodo = NominaPeriodo::where('created_by', $creatorId)->find($request->nomina_periodo_id);

        if (!$periodo) {
            return redirect()->back()->with('error', __('Payroll period not found.'));
        }

        $empleados = Empleado::where('created_by', $creatorId)->get();

        foreach ($empleados as $empleado) {
            $resultado = $service->registrarAportesEmpleado($periodo->id, $empleado->id, $creatorId);
            $isrService->registrarIsrEmpleado($periodo->id, $empleado, $creatorId, $resultado['base_imponible']);
        }

        return redirect()
            ->route('nomina.calculos.index', ['nomina_periodo_id' => $periodo->id])
            ->with('success', __('Social security contributions and ISR calculated successfully.'));
    }

    public function export(Request $request, NominaAportesSsService $service, NominaIsrService $isrService)
    {
        if (!\Auth::user()->can('nomina_periodos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'nomina_periodo_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $creatorId = \Auth::user()->creatorId();
        $periodo = NominaPeriodo::where('created_by', $creatorId)->find($request->nomina_periodo_id);

        if (!$periodo) {
            return redirect()->back()->with('error', __('Payroll period not found.'));
        }

        return Excel::download(
            new NominaAportesExport($periodo->id, $creatorId, $service, $isrService),
            'nomina-aportes-isr.xlsx'
        );
    }
}
