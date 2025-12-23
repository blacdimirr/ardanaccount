<?php

namespace App\Http\Controllers;

use App\Models\Requisicion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class RequisicionController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->can('compras_requisicion_create')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $requisiciones = Requisicion::where('created_by', \Auth::user()->creatorId())
            ->orderByDesc('id')
            ->get();

        return view('compras.requisiciones.index', compact('requisiciones'));
    }

    public function create()
    {
        if (!\Auth::user()->can('compras_requisicion_create')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return view('compras.requisiciones.create');
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('compras_requisicion_create')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'area_solicitante' => 'required|string|max:255',
            'fecha_requisicion' => 'required|date',
            'descripcion' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }

        Requisicion::create([
            'area_solicitante' => $request->area_solicitante,
            'descripcion' => $request->descripcion,
            'fecha_requisicion' => $request->fecha_requisicion,
            'estado' => 'borrador',
            'created_by' => \Auth::user()->creatorId(),
        ]);

        return redirect()->route('compras.requisiciones.index')->with('success', __('Requisition created successfully.'));
    }

    public function show(Requisicion $requisicion)
    {
        if (!\Auth::user()->can('compras_requisicion_create')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($requisicion->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return view('compras.requisiciones.show', compact('requisicion'));
    }

    public function edit(Requisicion $requisicion)
    {
        if (!\Auth::user()->can('compras_requisicion_create')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($requisicion->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return view('compras.requisiciones.edit', compact('requisicion'));
    }

    public function update(Request $request, Requisicion $requisicion)
    {
        if (!\Auth::user()->can('compras_requisicion_create')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($requisicion->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'area_solicitante' => 'required|string|max:255',
            'fecha_requisicion' => 'required|date',
            'descripcion' => 'nullable|string',
            'estado' => 'required|string',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }

        $requisicion->update([
            'area_solicitante' => $request->area_solicitante,
            'fecha_requisicion' => $request->fecha_requisicion,
            'descripcion' => $request->descripcion,
            'estado' => $request->estado,
        ]);

        return redirect()->route('compras.requisiciones.show', $requisicion->id)
            ->with('success', __('Requisition updated successfully.'));
    }

    public function destroy(Requisicion $requisicion)
    {
        if (!\Auth::user()->can('compras_requisicion_create')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($requisicion->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $requisicion->delete();

        return redirect()->route('compras.requisiciones.index')->with('success', __('Requisition deleted successfully.'));
    }

    public function print(Requisicion $requisicion)
    {
        if (!\Auth::user()->can('compras_requisicion_create')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($requisicion->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $pdf = Pdf::loadView('compras.requisiciones.print', compact('requisicion'));

        return $pdf->download('requisicion-' . $requisicion->id . '.pdf');
    }
}
