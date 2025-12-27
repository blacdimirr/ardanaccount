<?php

namespace App\Http\Controllers;

use App\Models\NotaEstadoFinanciero;
use Illuminate\Http\Request;

class NotasEstadosFinancierosController extends Controller
{
    public function index(Request $request)
    {
        if (!\Auth::user()->can('reportes_notas_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $periodo = $request->get('periodo');

        $query = NotaEstadoFinanciero::query();
        if (\Auth::user()->type !== 'super admin') {
            $query->where('created_by', \Auth::user()->creatorId());
        }

        if (!empty($periodo)) {
            $query->whereDate('periodo', $periodo);
        }

        $notas = $query->orderBy('periodo', 'desc')
            ->orderBy('codigo_nota')
            ->get();

        return view('report.notas_estados_financieros.index', compact('notas', 'periodo'));
    }

    public function create()
    {
        if (!\Auth::user()->can('reportes_notas_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $nota = new NotaEstadoFinanciero([
            'estado' => true,
            'periodo' => now()->format('Y-m-d'),
        ]);

        return view('report.notas_estados_financieros.create', compact('nota'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('reportes_notas_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $data = $this->validateData($request);
        $data['created_by'] = \Auth::user()->creatorId();

        NotaEstadoFinanciero::create($data);

        return redirect()->route('report.notas.index')->with('success', __('Nota creada correctamente.'));
    }

    public function edit(NotaEstadoFinanciero $nota)
    {
        if (!$this->canManage($nota)) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        return view('report.notas_estados_financieros.edit', compact('nota'));
    }

    public function update(Request $request, NotaEstadoFinanciero $nota)
    {
        if (!$this->canManage($nota)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $data = $this->validateData($request);
        $nota->update($data);

        return redirect()->route('report.notas.index')->with('success', __('Nota actualizada correctamente.'));
    }

    public function destroy(NotaEstadoFinanciero $nota)
    {
        if (!$this->canManage($nota)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $nota->delete();

        return redirect()->route('report.notas.index')->with('success', __('Nota eliminada correctamente.'));
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'codigo_nota' => 'required|string|max:50',
            'titulo' => 'required|string|max:191',
            'contenido' => 'required|string',
            'periodo' => 'required|date',
            'estado' => 'required|boolean',
        ]);
    }

    protected function canManage(NotaEstadoFinanciero $nota): bool
    {
        if (!\Auth::user()->can('reportes_notas_manage')) {
            return false;
        }

        if (\Auth::user()->type === 'super admin') {
            return true;
        }

        return in_array($nota->created_by, [\Auth::user()->creatorId(), 0], true);
    }
}
