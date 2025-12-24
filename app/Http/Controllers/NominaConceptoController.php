<?php

namespace App\Http\Controllers;

use App\Models\NominaConcepto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NominaConceptoController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->can('nomina_conceptos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $conceptos = NominaConcepto::where('created_by', \Auth::user()->creatorId())->get();

        return view('nomina.conceptos.index', compact('conceptos'));
    }

    public function create()
    {
        if (!\Auth::user()->can('nomina_conceptos_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $tipos = NominaConcepto::$tipos;

        return view('nomina.conceptos.create', compact('tipos'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('nomina_conceptos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'codigo' => [
                    'required',
                    Rule::unique('nomina_conceptos')->where(function ($query) {
                        return $query->where('created_by', \Auth::user()->creatorId());
                    }),
                ],
                'nombre' => 'required',
                'tipo' => 'required',
                'naturaleza' => 'required',
                'monto' => 'nullable|numeric',
                'aplica_isr' => 'nullable|boolean',
                'aplica_tss' => 'nullable|boolean',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $concepto = new NominaConcepto();
        $concepto->codigo = $request->codigo;
        $concepto->nombre = $request->nombre;
        $concepto->tipo = $request->tipo;
        $concepto->naturaleza = $request->naturaleza;
        $concepto->monto = $request->input('monto', 0);
        $concepto->aplica_isr = $request->boolean('aplica_isr');
        $concepto->aplica_tss = $request->boolean('aplica_tss');
        $concepto->created_by = \Auth::user()->creatorId();
        $concepto->save();

        return redirect()->route('nomina-conceptos.index')->with('success', __('Payroll concept successfully created.'));
    }

    public function edit($id)
    {
        if (!\Auth::user()->can('nomina_conceptos_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $concepto = NominaConcepto::find($id);
        if ($concepto->created_by != \Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $tipos = NominaConcepto::$tipos;

        return view('nomina.conceptos.edit', compact('concepto', 'tipos'));
    }

    public function update(Request $request, $id)
    {
        if (!\Auth::user()->can('nomina_conceptos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $concepto = NominaConcepto::find($id);
        if ($concepto->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'codigo' => [
                    'required',
                    Rule::unique('nomina_conceptos')
                        ->ignore($concepto->id)
                        ->where(function ($query) {
                            return $query->where('created_by', \Auth::user()->creatorId());
                        }),
                ],
                'nombre' => 'required',
                'tipo' => 'required',
                'naturaleza' => 'required',
                'monto' => 'nullable|numeric',
                'aplica_isr' => 'nullable|boolean',
                'aplica_tss' => 'nullable|boolean',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $concepto->codigo = $request->codigo;
        $concepto->nombre = $request->nombre;
        $concepto->tipo = $request->tipo;
        $concepto->naturaleza = $request->naturaleza;
        $concepto->monto = $request->input('monto', 0);
        $concepto->aplica_isr = $request->boolean('aplica_isr');
        $concepto->aplica_tss = $request->boolean('aplica_tss');
        $concepto->save();

        return redirect()->route('nomina-conceptos.index')->with('success', __('Payroll concept successfully updated.'));
    }

    public function destroy($id)
    {
        if (!\Auth::user()->can('nomina_conceptos_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $concepto = NominaConcepto::find($id);
        if ($concepto->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $concepto->delete();

        return redirect()->route('nomina-conceptos.index')->with('success', __('Payroll concept successfully deleted.'));
    }
}
