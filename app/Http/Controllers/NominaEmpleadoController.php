<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;

class NominaEmpleadoController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->can('nomina_empleados_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $empleados = Empleado::where('created_by', \Auth::user()->creatorId())->get();

        return view('nomina.empleados.index', compact('empleados'));
    }

    public function create()
    {
        if (!\Auth::user()->can('nomina_empleados_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $tiposVinculo = $this->tiposVinculo();

        return view('nomina.empleados.create', compact('tiposVinculo'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('nomina_empleados_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'tipo_vinculo' => 'required',
                'unidad_servicio' => 'required',
                'email' => 'nullable|email',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $empleado = new Empleado();
        $empleado->first_name = $request->first_name;
        $empleado->last_name = $request->last_name;
        $empleado->documento_identidad = $request->documento_identidad;
        $empleado->email = $request->email;
        $empleado->telefono = $request->telefono;
        $empleado->direccion = $request->direccion;
        $empleado->tipo_vinculo = $request->tipo_vinculo;
        $empleado->unidad_servicio = $request->unidad_servicio;
        $empleado->created_by = \Auth::user()->creatorId();
        $empleado->save();

        return redirect()->route('nomina-empleados.index')->with('success', __('Employee successfully created.'));
    }

    public function edit($id)
    {
        if (!\Auth::user()->can('nomina_empleados_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $empleado = Empleado::find($id);
        if ($empleado->created_by != \Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $tiposVinculo = $this->tiposVinculo();

        return view('nomina.empleados.edit', compact('empleado', 'tiposVinculo'));
    }

    public function update(Request $request, $id)
    {
        if (!\Auth::user()->can('nomina_empleados_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $empleado = Empleado::find($id);
        if ($empleado->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'tipo_vinculo' => 'required',
                'unidad_servicio' => 'required',
                'email' => 'nullable|email',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $empleado->first_name = $request->first_name;
        $empleado->last_name = $request->last_name;
        $empleado->documento_identidad = $request->documento_identidad;
        $empleado->email = $request->email;
        $empleado->telefono = $request->telefono;
        $empleado->direccion = $request->direccion;
        $empleado->tipo_vinculo = $request->tipo_vinculo;
        $empleado->unidad_servicio = $request->unidad_servicio;
        $empleado->save();

        return redirect()->route('nomina-empleados.index')->with('success', __('Employee successfully updated.'));
    }

    public function destroy($id)
    {
        if (!\Auth::user()->can('nomina_empleados_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $empleado = Empleado::find($id);
        if ($empleado->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $empleado->delete();

        return redirect()->route('nomina-empleados.index')->with('success', __('Employee successfully deleted.'));
    }

    private function tiposVinculo()
    {
        return [
            'nombrado' => __('Nombrado'),
            'contrato' => __('Contrato'),
            'honorarios' => __('Honorarios'),
        ];
    }
}
