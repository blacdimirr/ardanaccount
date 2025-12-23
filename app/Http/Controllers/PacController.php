<?php

namespace App\Http\Controllers;

use App\Exports\PacExport;
use App\Models\Budget;
use App\Models\ClasificadorObjetoGasto;
use App\Models\FundingSource;
use App\Models\Pac;
use App\Models\PacItem;
use App\Models\ProductServiceCategory;
use App\Models\Utility;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;

class PacController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->can('compras_pac_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $pacs = Pac::where('created_by', \Auth::user()->creatorId())
            ->orderByDesc('anio')
            ->get();

        return view('pac.index', compact('pacs'));
    }

    public function create()
    {
        if (!\Auth::user()->can('compras_pac_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $years = $this->yearList();

        return view('pac.create', compact('years'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('compras_pac_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'anio' => 'required|integer|min:2000|max:2100',
            'descripcion' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }

        $pac = Pac::create([
            'anio' => $request->anio,
            'descripcion' => $request->descripcion,
            'created_by' => \Auth::user()->creatorId(),
        ]);

        return redirect()->route('pac.show', Crypt::encrypt($pac->id))
            ->with('success', __('PAC created successfully.'));
    }

    public function show($id)
    {
        if (!\Auth::user()->can('compras_pac_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        try {
            $pacId = Crypt::decrypt($id);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('PAC not found.'));
        }

        $pac = Pac::with([
            'items.partidaPresupuestaria',
            'items.objetoGasto',
            'items.fuenteFinanciamiento',
        ])
            ->where('created_by', \Auth::user()->creatorId())
            ->findOrFail($pacId);

        $creatorId = \Auth::user()->creatorId();

        $categories = ProductServiceCategory::where('created_by', $creatorId)
            ->where('type', 'expense')
            ->orderBy('name')
            ->get();

        $objects = ClasificadorObjetoGasto::where(function ($query) use ($creatorId) {
            $query->where('created_by', $creatorId)
                ->orWhere('created_by', 0);
        })
            ->where('active', true)
            ->orderBy('code')
            ->get();

        $fundingSources = FundingSource::where(function ($query) use ($creatorId) {
            $query->where('created_by', $creatorId)
                ->orWhere('created_by', 0);
        })
            ->where('active', true)
            ->orderBy('code')
            ->get();

        return view('pac.show', compact('pac', 'categories', 'objects', 'fundingSources'));
    }

    public function edit($id)
    {
        if (!\Auth::user()->can('compras_pac_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        try {
            $pacId = Crypt::decrypt($id);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('PAC not found.'));
        }

        $pac = Pac::where('created_by', \Auth::user()->creatorId())->findOrFail($pacId);
        $years = $this->yearList();

        return view('pac.edit', compact('pac', 'years'));
    }

    public function update(Request $request, $id)
    {
        if (!\Auth::user()->can('compras_pac_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $pac = Pac::where('created_by', \Auth::user()->creatorId())->findOrFail($id);

        $validator = \Validator::make($request->all(), [
            'anio' => 'required|integer|min:2000|max:2100',
            'descripcion' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }

        $pac->update([
            'anio' => $request->anio,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->route('pac.show', Crypt::encrypt($pac->id))
            ->with('success', __('PAC updated successfully.'));
    }

    public function destroy($id)
    {
        if (!\Auth::user()->can('compras_pac_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $pac = Pac::where('created_by', \Auth::user()->creatorId())->findOrFail($id);
        $pac->delete();

        return redirect()->route('pac.index')->with('success', __('PAC deleted successfully.'));
    }

    public function storeItem(Request $request, Pac $pac)
    {
        if (!\Auth::user()->can('compras_pac_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($pac->created_by !== \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'descripcion' => 'required|string|max:255',
            'partida_presupuestaria_id' => 'required|integer|exists:product_service_categories,id',
            'objeto_gasto_id' => 'required|integer|exists:clasificador_objeto_gasto,id',
            'fuente_financiamiento_id' => 'required|integer|exists:fuentes_financiamiento,id',
            'tipo_procedimiento' => 'required|string|max:100',
            'monto_estimado' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }

        $budget = Budget::where('created_by', \Auth::user()->creatorId())
            ->where('from', $pac->anio)
            ->first();

        if ($budget) {
            $pimTotals = $budget->monto_pim ?? [];
            if (empty($pimTotals) && !empty($budget->monto_pia)) {
                $pimTotals = $budget->monto_pia;
            }

            $pimValue = data_get($pimTotals, 'expense.' . $request->partida_presupuestaria_id);
            if (is_numeric($pimValue) && $request->monto_estimado > $pimValue) {
                return redirect()->back()->with('error', __('Estimated amount exceeds the PIM for this budget line.'));
            }
        }

        PacItem::create([
            'pac_id' => $pac->id,
            'descripcion' => $request->descripcion,
            'partida_presupuestaria_id' => $request->partida_presupuestaria_id,
            'objeto_gasto_id' => $request->objeto_gasto_id,
            'fuente_financiamiento_id' => $request->fuente_financiamiento_id,
            'tipo_procedimiento' => $request->tipo_procedimiento,
            'monto_estimado' => $request->monto_estimado,
        ]);

        return redirect()->back()->with('success', __('PAC item added successfully.'));
    }

    public function updateItem(Request $request, Pac $pac, PacItem $item)
    {
        if (!\Auth::user()->can('compras_pac_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($pac->created_by !== \Auth::user()->creatorId() || $item->pac_id !== $pac->id) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'descripcion' => 'required|string|max:255',
            'partida_presupuestaria_id' => 'required|integer|exists:product_service_categories,id',
            'objeto_gasto_id' => 'required|integer|exists:clasificador_objeto_gasto,id',
            'fuente_financiamiento_id' => 'required|integer|exists:fuentes_financiamiento,id',
            'tipo_procedimiento' => 'required|string|max:100',
            'monto_estimado' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }

        $budget = Budget::where('created_by', \Auth::user()->creatorId())
            ->where('from', $pac->anio)
            ->first();

        if ($budget) {
            $pimTotals = $budget->monto_pim ?? [];
            if (empty($pimTotals) && !empty($budget->monto_pia)) {
                $pimTotals = $budget->monto_pia;
            }

            $pimValue = data_get($pimTotals, 'expense.' . $request->partida_presupuestaria_id);
            if (is_numeric($pimValue) && $request->monto_estimado > $pimValue) {
                return redirect()->back()->with('error', __('Estimated amount exceeds the PIM for this budget line.'));
            }
        }

        $item->update([
            'descripcion' => $request->descripcion,
            'partida_presupuestaria_id' => $request->partida_presupuestaria_id,
            'objeto_gasto_id' => $request->objeto_gasto_id,
            'fuente_financiamiento_id' => $request->fuente_financiamiento_id,
            'tipo_procedimiento' => $request->tipo_procedimiento,
            'monto_estimado' => $request->monto_estimado,
        ]);

        return redirect()->back()->with('success', __('PAC item updated successfully.'));
    }

    public function destroyItem(Pac $pac, PacItem $item)
    {
        if (!\Auth::user()->can('compras_pac_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($pac->created_by !== \Auth::user()->creatorId() || $item->pac_id !== $pac->id) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $item->delete();

        return redirect()->back()->with('success', __('PAC item deleted successfully.'));
    }

    public function report($id)
    {
        if (!\Auth::user()->can('compras_pac_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        try {
            $pacId = Crypt::decrypt($id);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('PAC not found.'));
        }

        $pac = Pac::with([
            'items.partidaPresupuestaria',
            'items.objetoGasto',
            'items.fuenteFinanciamiento',
        ])
            ->where('created_by', \Auth::user()->creatorId())
            ->findOrFail($pacId);

        $settings = Utility::settingsById($pac->created_by);
        $companyName = $settings['company_name'] ?? config('app.name');

        $pdf = Pdf::loadView('pac.report', compact('pac', 'companyName'));

        return $pdf->download('pac_' . $pac->anio . '.pdf');
    }

    public function export($id)
    {
        if (!\Auth::user()->can('compras_pac_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        try {
            $pacId = Crypt::decrypt($id);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('PAC not found.'));
        }

        $pac = Pac::where('created_by', \Auth::user()->creatorId())->findOrFail($pacId);

        return Excel::download(new PacExport($pac), 'pac_' . $pac->anio . '.xlsx');
    }

    protected function yearList(): array
    {
        $startingYear = date('Y', strtotime('+5 year'));
        $endingYear = date('Y');
        $years = [];

        foreach (range($endingYear, $startingYear) as $year) {
            $years[$year] = $year;
        }

        return $years;
    }
}
