<?php

namespace App\Http\Controllers;

use App\Models\ConfigAporteSs;
use Illuminate\Http\Request;

class NominaConfigAportesSsController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->can('nomina_config_ss_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $config = ConfigAporteSs::firstOrCreate(
            ['created_by' => \Auth::user()->creatorId()],
            [
                'tss_empleador' => 0,
                'tss_empleado' => 0,
                'infotep_empleador' => 0,
                'infotep_empleado' => 0,
                'idoppril_empleador' => 0,
                'idoppril_empleado' => 0,
            ]
        );

        return view('nomina.config_ss.index', compact('config'));
    }

    public function update(Request $request)
    {
        if (!\Auth::user()->can('nomina_config_ss_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'tss_empleador' => 'required|numeric|min:0',
            'tss_empleado' => 'required|numeric|min:0',
            'infotep_empleador' => 'required|numeric|min:0',
            'infotep_empleado' => 'required|numeric|min:0',
            'idoppril_empleador' => 'required|numeric|min:0',
            'idoppril_empleado' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        ConfigAporteSs::updateOrCreate(
            ['created_by' => \Auth::user()->creatorId()],
            [
                'tss_empleador' => $request->tss_empleador,
                'tss_empleado' => $request->tss_empleado,
                'infotep_empleador' => $request->infotep_empleador,
                'infotep_empleado' => $request->infotep_empleado,
                'idoppril_empleador' => $request->idoppril_empleador,
                'idoppril_empleado' => $request->idoppril_empleado,
                'created_by' => \Auth::user()->creatorId(),
            ]
        );

        return redirect()->route('nomina.config_ss.index')->with('success', __('Social security parameters updated successfully.'));
    }
}
