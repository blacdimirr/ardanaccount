<?php

namespace App\Http\Controllers;

use App\Models\Oferta;
use App\Models\ProcesoCompra;
use Illuminate\Http\Request;

class OfertaCompraController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->can('compras_proceso_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $ofertas = Oferta::with('procesoCompra.requisicion')
            ->where('created_by', \Auth::user()->creatorId())
            ->orderByDesc('id')
            ->get();

        return view('compras.ofertas.index', compact('ofertas'));
    }

    public function create()
    {
        if (!\Auth::user()->can('compras_proceso_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $procesos = ProcesoCompra::with('requisicion')
            ->where('created_by', \Auth::user()->creatorId())
            ->orderByDesc('id')
            ->get();

        return view('compras.ofertas.create', compact('procesos'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('compras_proceso_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'proceso_compra_id' => 'required|integer|exists:procesos_compra,id',
            'proveedor' => 'required|string|max:255',
            'monto_ofertado' => 'required|numeric|min:0',
            'fecha_oferta' => 'required|date',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }

        $proceso = ProcesoCompra::where('created_by', \Auth::user()->creatorId())
            ->findOrFail($request->proceso_compra_id);

        $oferta = Oferta::create([
            'proceso_compra_id' => $proceso->id,
            'proveedor' => $request->proveedor,
            'monto_ofertado' => $request->monto_ofertado,
            'fecha_oferta' => $request->fecha_oferta,
            'estado' => 'presentada',
            'created_by' => \Auth::user()->creatorId(),
        ]);

        return redirect()->route('compras.ofertas.show', $oferta->id)
            ->with('success', __('Offer registered successfully.'));
    }

    public function show(Oferta $oferta)
    {
        if (!\Auth::user()->can('compras_proceso_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($oferta->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $oferta->load('procesoCompra.requisicion');

        return view('compras.ofertas.show', compact('oferta'));
    }

    public function edit(Oferta $oferta)
    {
        if (!\Auth::user()->can('compras_proceso_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($oferta->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return view('compras.ofertas.edit', compact('oferta'));
    }

    public function update(Request $request, Oferta $oferta)
    {
        if (!\Auth::user()->can('compras_proceso_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($oferta->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'proveedor' => 'required|string|max:255',
            'monto_ofertado' => 'required|numeric|min:0',
            'fecha_oferta' => 'required|date',
            'estado' => 'required|string',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }

        $oferta->update([
            'proveedor' => $request->proveedor,
            'monto_ofertado' => $request->monto_ofertado,
            'fecha_oferta' => $request->fecha_oferta,
            'estado' => $request->estado,
        ]);

        return redirect()->route('compras.ofertas.show', $oferta->id)
            ->with('success', __('Offer updated successfully.'));
    }

    public function destroy(Oferta $oferta)
    {
        if (!\Auth::user()->can('compras_proceso_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($oferta->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $oferta->delete();

        return redirect()->route('compras.ofertas.index')->with('success', __('Offer deleted successfully.'));
    }
}
