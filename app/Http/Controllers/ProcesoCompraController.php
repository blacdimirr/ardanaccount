<?php

namespace App\Http\Controllers;

use App\Models\ProcesoCompra;
use App\Models\Requisicion;
use Illuminate\Http\Request;

class ProcesoCompraController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->can('compras_proceso_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $procesos = ProcesoCompra::with('requisicion')
            ->where('created_by', \Auth::user()->creatorId())
            ->orderByDesc('id')
            ->get();

        return view('compras.procesos.index', compact('procesos'));
    }

    public function create()
    {
        if (!\Auth::user()->can('compras_proceso_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $requisiciones = Requisicion::where('created_by', \Auth::user()->creatorId())
            ->orderByDesc('id')
            ->get();

        return view('compras.procesos.create', compact('requisiciones'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('compras_proceso_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'requisicion_id' => 'required|integer|exists:requisitions,id',
            'fecha_inicio' => 'required|date',
            'descripcion' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }

        $requisicion = Requisicion::where('created_by', \Auth::user()->creatorId())
            ->findOrFail($request->requisicion_id);

        $proceso = ProcesoCompra::create([
            'requisicion_id' => $requisicion->id,
            'descripcion' => $request->descripcion,
            'fecha_inicio' => $request->fecha_inicio,
            'estado' => 'en_proceso',
            'created_by' => \Auth::user()->creatorId(),
        ]);

        $requisicion->update(['estado' => 'en_proceso']);

        return redirect()->route('compras.procesos.show', $proceso->id)
            ->with('success', __('Purchase process created successfully.'));
    }

    public function show(ProcesoCompra $proceso)
    {
        if (!\Auth::user()->can('compras_proceso_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($proceso->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $proceso->load(['requisicion', 'ofertas', 'adjudicacion']);

        return view('compras.procesos.show', compact('proceso'));
    }

    public function edit(ProcesoCompra $proceso)
    {
        if (!\Auth::user()->can('compras_proceso_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($proceso->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return view('compras.procesos.edit', compact('proceso'));
    }

    public function update(Request $request, ProcesoCompra $proceso)
    {
        if (!\Auth::user()->can('compras_proceso_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($proceso->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'fecha_inicio' => 'required|date',
            'descripcion' => 'nullable|string',
            'estado' => 'required|string',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }

        $proceso->update([
            'fecha_inicio' => $request->fecha_inicio,
            'descripcion' => $request->descripcion,
            'estado' => $request->estado,
        ]);

        return redirect()->route('compras.procesos.show', $proceso->id)
            ->with('success', __('Purchase process updated successfully.'));
    }

    public function destroy(ProcesoCompra $proceso)
    {
        if (!\Auth::user()->can('compras_proceso_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($proceso->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $proceso->delete();

        return redirect()->route('compras.procesos.index')->with('success', __('Purchase process deleted successfully.'));
    }
}
