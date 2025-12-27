<?php

namespace App\Http\Controllers;

use App\Models\CuentaRecaudadora;
use App\Models\MovimientoBancario;
use App\Services\MovimientoBancarioImportService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class MovimientoBancarioController extends Controller
{
    public function index(Request $request)
    {
        if (!\Auth::user()->can('tesoreria_extractos_import')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $creatorId = \Auth::user()->creatorId();
        $cuentasQuery = CuentaRecaudadora::query()->where('activo', true);
        if (!\Auth::user()->type || \Auth::user()->type !== 'super admin') {
            $cuentasQuery->where('created_by', $creatorId);
        }
        $cuentas = $cuentasQuery->orderBy('banco')->orderBy('numero_cuenta')->get();

        $movimientosQuery = MovimientoBancario::with('cuentaRecaudadora')->orderBy('fecha', 'desc');
        if (!\Auth::user()->type || \Auth::user()->type !== 'super admin') {
            $movimientosQuery->whereIn('cuenta_recaudadora_id', $cuentas->pluck('id'));
        }

        if ($request->filled('cuenta_recaudadora_id')) {
            $movimientosQuery->where('cuenta_recaudadora_id', $request->cuenta_recaudadora_id);
        }

        $movimientos = $movimientosQuery->get();
        $preview = $request->session()->get('extracto_import');

        return view('tesoreria.extractos.index', compact('cuentas', 'movimientos', 'preview'));
    }

    public function preview(Request $request, MovimientoBancarioImportService $service)
    {
        if (!\Auth::user()->can('tesoreria_extractos_import')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'cuenta_recaudadora_id' => [
                    'required',
                    'integer',
                    'min:1',
                    $this->cuentaRecaudadoraRule(),
                ],
                'archivo' => 'required|file|mimes:csv,txt,ofx',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->route('tesoreria.extractos.index')->with('error', $messages->first());
        }

        $movimientos = $service->parseAndMap($request->file('archivo'));
        if (empty($movimientos)) {
            return redirect()->route('tesoreria.extractos.index')->with('error', __('No movements were detected in the file.'));
        }

        $request->session()->put('extracto_import', [
            'cuenta_recaudadora_id' => (int) $request->cuenta_recaudadora_id,
            'origen_archivo' => $request->file('archivo')->getClientOriginalName(),
            'movimientos' => $movimientos,
        ]);

        return redirect()->route('tesoreria.extractos.index')->with('success', __('Bank statement preview generated.'));
    }

    public function confirm(Request $request, MovimientoBancarioImportService $service)
    {
        if (!\Auth::user()->can('tesoreria_extractos_import')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $payload = $request->session()->get('extracto_import');
        if (!$payload) {
            return redirect()->route('tesoreria.extractos.index')->with('error', __('No statement import is pending.'));
        }

        $service->storeMovimientos(
            $payload['cuenta_recaudadora_id'],
            $payload['movimientos'],
            $payload['origen_archivo']
        );

        $request->session()->forget('extracto_import');

        return redirect()->route('tesoreria.extractos.index')->with('success', __('Bank statement movements imported successfully.'));
    }

    private function cuentaRecaudadoraRule(): Exists
    {
        $rule = Rule::exists('cuentas_recaudadoras', 'id')->where('activo', true);

        if (!\Auth::user()->type || \Auth::user()->type !== 'super admin') {
            $rule->where('created_by', \Auth::user()->creatorId());
        }

        return $rule;
    }
}
