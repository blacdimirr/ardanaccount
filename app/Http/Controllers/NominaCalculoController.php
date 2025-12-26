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
        $conceptosIsr = 0;
        $conceptosTss = 0;

        if ($request->filled('nomina_periodo_id')) {
            $periodoSeleccionado = $periodos->firstWhere('id', $request->nomina_periodo_id);

            if ($periodoSeleccionado) {
                $config = $service->getConfig($creatorId);
                $empleados = Empleado::where('created_by', $creatorId)->get();
                $conceptosIsr = $service->montoConceptosIsr($creatorId, $periodoSeleccionado->id);
                $conceptosTss = $service->montoConceptosTss($creatorId, $periodoSeleccionado->id);

                $calculos = $empleados->map(function ($empleado) use ($service, $isrService, $config, $creatorId, $conceptosIsr, $conceptosTss) {
                    $salario = (float) $empleado->salario;
                    $baseIsr = $salario + $conceptosIsr;
                    $baseTss = $salario + $conceptosTss;
                    $aportes = $service->calcularAportes($baseTss, $config);
                    $isr = $isrService->calcularIsr($baseIsr, $empleado, $creatorId);

                    return [
                        'empleado' => $empleado,
                        'salario' => $salario,
                        'conceptos_isr' => $conceptosIsr,
                        'conceptos_tss' => $conceptosTss,
                        'base_isr' => $baseIsr,
                        'base_tss' => $baseTss,
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

        return view(
            'nomina.calculos.index',
            compact('periodos', 'periodoSeleccionado', 'calculos', 'conceptosIsr', 'conceptosTss')
        );
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
            $resultado = $service->registrarAportesEmpleado($periodo->id, $empleado, $creatorId);
            $isrService->registrarIsrEmpleado($periodo->id, $empleado, $creatorId, $resultado['base_imponible_isr']);
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
