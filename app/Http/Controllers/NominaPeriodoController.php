<?php

namespace App\Http\Controllers;

use App\Models\NominaPeriodo;
use Illuminate\Http\Request;

class NominaPeriodoController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->can('nomina_periodos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $periodos = NominaPeriodo::where('created_by', \Auth::user()->creatorId())->get();

        return view('nomina.periodos.index', compact('periodos'));
    }

    public function create()
    {
        if (!\Auth::user()->can('nomina_periodos_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $estados = NominaPeriodo::$estados;

        return view('nomina.periodos.create', compact('estados'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('nomina_periodos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'nombre' => 'required',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'estado' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $periodo = new NominaPeriodo();
        $periodo->nombre = $request->nombre;
        $periodo->fecha_inicio = $request->fecha_inicio;
        $periodo->fecha_fin = $request->fecha_fin;
        $periodo->estado = $request->estado;
        $periodo->created_by = \Auth::user()->creatorId();
        $periodo->save();

        return redirect()->route('nomina-periodos.index')->with('success', __('Payroll period successfully created.'));
    }

    public function edit($id)
    {
        if (!\Auth::user()->can('nomina_periodos_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $periodo = NominaPeriodo::find($id);
        if ($periodo->created_by != \Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $estados = NominaPeriodo::$estados;

        return view('nomina.periodos.edit', compact('periodo', 'estados'));
    }

    public function update(Request $request, $id)
    {
        if (!\Auth::user()->can('nomina_periodos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $periodo = NominaPeriodo::find($id);
        if ($periodo->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'nombre' => 'required',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'estado' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $periodo->nombre = $request->nombre;
        $periodo->fecha_inicio = $request->fecha_inicio;
        $periodo->fecha_fin = $request->fecha_fin;
        $periodo->estado = $request->estado;
        $periodo->save();

        return redirect()->route('nomina-periodos.index')->with('success', __('Payroll period successfully updated.'));
    }

    public function destroy($id)
    {
        if (!\Auth::user()->can('nomina_periodos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $periodo = NominaPeriodo::find($id);
        if ($periodo->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $periodo->delete();

        return redirect()->route('nomina-periodos.index')->with('success', __('Payroll period successfully deleted.'));
    }
}
