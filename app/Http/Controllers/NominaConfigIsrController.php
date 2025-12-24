<?php

namespace App\Http\Controllers;

use App\Models\ConfigIsrHonorario;
use App\Models\ConfigIsrTramo;
use Illuminate\Http\Request;

class NominaConfigIsrController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->can('nomina_config_isr_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $creatorId = \Auth::user()->creatorId();

        $tramos = ConfigIsrTramo::where('created_by', $creatorId)
            ->orderBy('rango_desde')
            ->get();

        $honorariosConfig = ConfigIsrHonorario::firstOrCreate(
            ['created_by' => $creatorId],
            ['retencion_honorarios' => 0]
        );

        return view('nomina.config_isr.index', compact('tramos', 'honorariosConfig'));
    }

    public function create()
    {
        if (!\Auth::user()->can('nomina_config_isr_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        return view('nomina.config_isr.create');
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('nomina_config_isr_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'rango_desde' => 'required|numeric|min:0',
                'rango_hasta' => 'nullable|numeric|min:0|gte:rango_desde',
                'tasa' => 'required|numeric|min:0|max:100',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        ConfigIsrTramo::create([
            'rango_desde' => $request->rango_desde,
            'rango_hasta' => $request->rango_hasta ?: null,
            'tasa' => $request->tasa,
            'created_by' => \Auth::user()->creatorId(),
        ]);

        return redirect()->route('nomina.config_isr.index')->with('success', __('ISR bracket successfully created.'));
    }

    public function edit($id)
    {
        if (!\Auth::user()->can('nomina_config_isr_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $tramo = ConfigIsrTramo::find($id);
        if (!$tramo || $tramo->created_by != \Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        return view('nomina.config_isr.edit', compact('tramo'));
    }

    public function update(Request $request, $id)
    {
        if (!\Auth::user()->can('nomina_config_isr_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $tramo = ConfigIsrTramo::find($id);
        if (!$tramo || $tramo->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'rango_desde' => 'required|numeric|min:0',
                'rango_hasta' => 'nullable|numeric|min:0|gte:rango_desde',
                'tasa' => 'required|numeric|min:0|max:100',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $tramo->rango_desde = $request->rango_desde;
        $tramo->rango_hasta = $request->rango_hasta ?: null;
        $tramo->tasa = $request->tasa;
        $tramo->save();

        return redirect()->route('nomina.config_isr.index')->with('success', __('ISR bracket successfully updated.'));
    }

    public function destroy($id)
    {
        if (!\Auth::user()->can('nomina_config_isr_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $tramo = ConfigIsrTramo::find($id);
        if (!$tramo || $tramo->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $tramo->delete();

        return redirect()->route('nomina.config_isr.index')->with('success', __('ISR bracket successfully deleted.'));
    }

    public function updateHonorarios(Request $request)
    {
        if (!\Auth::user()->can('nomina_config_isr_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'retencion_honorarios' => 'required|numeric|min:0|max:100',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $config = ConfigIsrHonorario::firstOrCreate(
            ['created_by' => \Auth::user()->creatorId()],
            ['retencion_honorarios' => 0]
        );

        $config->retencion_honorarios = $request->retencion_honorarios;
        $config->save();

        return redirect()->route('nomina.config_isr.index')->with('success', __('Honorarios retention updated successfully.'));
    }
}
