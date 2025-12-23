<?php

namespace App\Http\Controllers;

use App\Models\Adjudicacion;
use App\Models\ContratoCompra;
use App\Models\ProductServiceCategory;
use App\Services\BudgetExecutionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ContratoCompraController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->can('compras_contratos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $contratos = ContratoCompra::with(['adjudicacion.procesoCompra.requisicion', 'adjudicacion.oferta'])
            ->where('created_by', \Auth::user()->creatorId())
            ->orderByDesc('id')
            ->get();

        return view('compras.contratos.index', compact('contratos'));
    }

    public function create()
    {
        if (!\Auth::user()->can('compras_contratos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $adjudicaciones = Adjudicacion::with('oferta')
            ->where('created_by', \Auth::user()->creatorId())
            ->orderByDesc('id')
            ->get();

        $categorias = ProductServiceCategory::where('created_by', \Auth::user()->creatorId())
            ->where('type', 'expense')
            ->orderBy('name')
            ->get();

        return view('compras.contratos.create', compact('adjudicaciones', 'categorias'));
    }

    public function store(Request $request, BudgetExecutionService $budgetService)
    {
        if (!\Auth::user()->can('compras_contratos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'adjudicacion_id' => 'required|integer|exists:adjudicaciones,id',
            'partida_presupuestaria_id' => 'nullable|integer|exists:product_service_categories,id',
            'proveedor' => 'required|string|max:255',
            'monto_contrato' => 'required|numeric|min:0',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }

        $adjudicacion = Adjudicacion::where('created_by', \Auth::user()->creatorId())
            ->findOrFail($request->adjudicacion_id);

        $categoryId = $request->partida_presupuestaria_id ?: $adjudicacion->partida_presupuestaria_id;
        $compromisoAplicado = false;

        if ($categoryId) {
            $budget = $budgetService->findBudgetForDate(\Auth::user()->creatorId(), $request->fecha_inicio);

            if ($adjudicacion->compromiso_aplicado && $adjudicacion->partida_presupuestaria_id) {
                $delta = [];
                $previousAmount = (float) $adjudicacion->monto_adjudicado;
                $newAmount = (float) $request->monto_contrato;

                if ((int) $adjudicacion->partida_presupuestaria_id !== (int) $categoryId) {
                    $delta[(int) $adjudicacion->partida_presupuestaria_id] = -1 * $previousAmount;
                    $delta[(int) $categoryId] = $newAmount;
                } else {
                    $delta[(int) $categoryId] = $newAmount - $previousAmount;
                }

                $budgetService->ensureCommitmentAvailability($budget, array_filter($delta, fn ($value) => $value > 0));
                $budgetService->applyCommitmentDelta($budget, $delta);
                $compromisoAplicado = (bool) $budget;
            } else {
                $categoryAmounts = [(int) $categoryId => (float) $request->monto_contrato];
                $budgetService->ensureCommitmentAvailability($budget, $categoryAmounts);
                $budgetService->applyCommitment($budget, $categoryAmounts);
                $compromisoAplicado = (bool) $budget;
            }
        }

        $contrato = ContratoCompra::create([
            'adjudicacion_id' => $adjudicacion->id,
            'partida_presupuestaria_id' => $categoryId,
            'proveedor' => $request->proveedor,
            'monto_contrato' => $request->monto_contrato,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'estado' => 'vigente',
            'compromiso_aplicado' => $compromisoAplicado,
            'created_by' => \Auth::user()->creatorId(),
        ]);

        $adjudicacion->update(['estado' => 'contratado']);

        return redirect()->route('compras.contratos.show', $contrato->id)
            ->with('success', __('Contract created successfully.'));
    }

    public function show(ContratoCompra $contrato)
    {
        if (!\Auth::user()->can('compras_contratos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($contrato->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $contrato->load(['adjudicacion.procesoCompra.requisicion', 'adjudicacion.oferta', 'partidaPresupuestaria']);

        return view('compras.contratos.show', compact('contrato'));
    }

    public function edit(ContratoCompra $contrato)
    {
        if (!\Auth::user()->can('compras_contratos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($contrato->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $categorias = ProductServiceCategory::where('created_by', \Auth::user()->creatorId())
            ->where('type', 'expense')
            ->orderBy('name')
            ->get();

        return view('compras.contratos.edit', compact('contrato', 'categorias'));
    }

    public function update(Request $request, ContratoCompra $contrato)
    {
        if (!\Auth::user()->can('compras_contratos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($contrato->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'partida_presupuestaria_id' => 'nullable|integer|exists:product_service_categories,id',
            'proveedor' => 'required|string|max:255',
            'monto_contrato' => 'required|numeric|min:0',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'estado' => 'required|string',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }

        $contrato->update([
            'partida_presupuestaria_id' => $request->partida_presupuestaria_id,
            'proveedor' => $request->proveedor,
            'monto_contrato' => $request->monto_contrato,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'estado' => $request->estado,
        ]);

        return redirect()->route('compras.contratos.show', $contrato->id)
            ->with('success', __('Contract updated successfully.'));
    }

    public function destroy(ContratoCompra $contrato)
    {
        if (!\Auth::user()->can('compras_contratos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($contrato->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $contrato->delete();

        return redirect()->route('compras.contratos.index')->with('success', __('Contract deleted successfully.'));
    }

    public function print(ContratoCompra $contrato)
    {
        if (!\Auth::user()->can('compras_contratos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($contrato->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $contrato->load(['adjudicacion.procesoCompra.requisicion', 'adjudicacion.oferta', 'partidaPresupuestaria']);

        $pdf = Pdf::loadView('compras.contratos.print', compact('contrato'));

        return $pdf->download('contrato-compra-' . $contrato->id . '.pdf');
    }
}
