<?php

namespace App\Http\Controllers;

use App\Models\Adjudicacion;
use App\Models\Oferta;
use App\Models\ProcesoCompra;
use App\Models\ProductServiceCategory;
use App\Services\BudgetExecutionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AdjudicacionCompraController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->can('compras_adjudicar')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $adjudicaciones = Adjudicacion::with(['procesoCompra.requisicion', 'oferta'])
            ->where('created_by', \Auth::user()->creatorId())
            ->orderByDesc('id')
            ->get();

        return view('compras.adjudicaciones.index', compact('adjudicaciones'));
    }

    public function create()
    {
        if (!\Auth::user()->can('compras_adjudicar')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $procesos = ProcesoCompra::with(['ofertas', 'requisicion'])
            ->where('created_by', \Auth::user()->creatorId())
            ->orderByDesc('id')
            ->get();

        $categorias = ProductServiceCategory::where('created_by', \Auth::user()->creatorId())
            ->where('type', 'expense')
            ->orderBy('name')
            ->get();

        return view('compras.adjudicaciones.create', compact('procesos', 'categorias'));
    }

    public function store(Request $request, BudgetExecutionService $budgetService)
    {
        if (!\Auth::user()->can('compras_adjudicar')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'proceso_compra_id' => 'required|integer|exists:purchase_processes,id',
            'oferta_id' => 'required|integer|exists:purchase_offers,id',
            'partida_presupuestaria_id' => 'nullable|integer|exists:product_service_categories,id',
            'monto_adjudicado' => 'required|numeric|min:0',
            'fecha_adjudicacion' => 'required|date',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }

        $proceso = ProcesoCompra::where('created_by', \Auth::user()->creatorId())
            ->findOrFail($request->proceso_compra_id);

        $oferta = Oferta::where('created_by', \Auth::user()->creatorId())
            ->where('proceso_compra_id', $proceso->id)
            ->findOrFail($request->oferta_id);

        $compromisoAplicado = false;
        if ($request->filled('partida_presupuestaria_id')) {
            $budget = $budgetService->findBudgetForDate(\Auth::user()->creatorId(), $request->fecha_adjudicacion);
            $categoryAmounts = [(int) $request->partida_presupuestaria_id => (float) $request->monto_adjudicado];

            $budgetService->ensureCommitmentAvailability($budget, $categoryAmounts);
            $budgetService->applyCommitment($budget, $categoryAmounts);
            $compromisoAplicado = (bool) $budget;
        }

        $adjudicacion = Adjudicacion::create([
            'proceso_compra_id' => $proceso->id,
            'oferta_id' => $oferta->id,
            'partida_presupuestaria_id' => $request->partida_presupuestaria_id,
            'monto_adjudicado' => $request->monto_adjudicado,
            'fecha_adjudicacion' => $request->fecha_adjudicacion,
            'estado' => 'aprobado',
            'compromiso_aplicado' => $compromisoAplicado,
            'created_by' => \Auth::user()->creatorId(),
        ]);

        $proceso->update(['estado' => 'adjudicado']);
        $proceso->requisicion?->update(['estado' => 'aprobado']);

        return redirect()->route('compras.adjudicaciones.show', $adjudicacion->id)
            ->with('success', __('Award recorded successfully.'));
    }

    public function show(Adjudicacion $adjudicacion)
    {
        if (!\Auth::user()->can('compras_adjudicar')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($adjudicacion->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $adjudicacion->load(['procesoCompra.requisicion', 'oferta', 'partidaPresupuestaria', 'contrato']);

        return view('compras.adjudicaciones.show', compact('adjudicacion'));
    }

    public function edit(Adjudicacion $adjudicacion)
    {
        if (!\Auth::user()->can('compras_adjudicar')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($adjudicacion->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $categorias = ProductServiceCategory::where('created_by', \Auth::user()->creatorId())
            ->where('type', 'expense')
            ->orderBy('name')
            ->get();

        return view('compras.adjudicaciones.edit', compact('adjudicacion', 'categorias'));
    }

    public function update(Request $request, Adjudicacion $adjudicacion)
    {
        if (!\Auth::user()->can('compras_adjudicar')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($adjudicacion->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'partida_presupuestaria_id' => 'nullable|integer|exists:product_service_categories,id',
            'monto_adjudicado' => 'required|numeric|min:0',
            'fecha_adjudicacion' => 'required|date',
            'estado' => 'required|string',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }

        $adjudicacion->update([
            'partida_presupuestaria_id' => $request->partida_presupuestaria_id,
            'monto_adjudicado' => $request->monto_adjudicado,
            'fecha_adjudicacion' => $request->fecha_adjudicacion,
            'estado' => $request->estado,
        ]);

        return redirect()->route('compras.adjudicaciones.show', $adjudicacion->id)
            ->with('success', __('Award updated successfully.'));
    }

    public function destroy(Adjudicacion $adjudicacion)
    {
        if (!\Auth::user()->can('compras_adjudicar')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($adjudicacion->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $adjudicacion->delete();

        return redirect()->route('compras.adjudicaciones.index')->with('success', __('Award deleted successfully.'));
    }

    public function print(Adjudicacion $adjudicacion)
    {
        if (!\Auth::user()->can('compras_adjudicar')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($adjudicacion->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $adjudicacion->load(['procesoCompra.requisicion', 'oferta', 'partidaPresupuestaria']);

        $pdf = Pdf::loadView('compras.adjudicaciones.print', compact('adjudicacion'));

        return $pdf->download('acta-adjudicacion-' . $adjudicacion->id . '.pdf');
    }
}
