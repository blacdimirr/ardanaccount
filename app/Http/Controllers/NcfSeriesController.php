<?php

namespace App\Http\Controllers;

use App\Models\NcfSeries;
use App\Models\NcfType;
use Illuminate\Http\Request;

class NcfSeriesController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->can('config_ncf_series_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $series = NcfSeries::with('type')
            ->where(function ($query) {
                $query->where('created_by', \Auth::user()->creatorId())
                    ->orWhere('created_by', 0);
            })
            ->orderByDesc('created_at')
            ->get();

        return view('ncf_series.index', compact('series'));
    }

    public function create()
    {
        if (!\Auth::user()->can('config_ncf_series_manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $ncfTypes = $this->ncfTypeOptions();
        $ncfSeries = new NcfSeries();

        return view('ncf_series.create', compact('ncfTypes', 'ncfSeries'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('config_ncf_series_manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validated = $this->validateSeries($request);

        try {
            NcfSeries::create($validated + [
                'created_by' => \Auth::user()->creatorId(),
                'current_number' => $validated['current_number'] ?? null,
            ]);
        } catch (\Throwable $exception) {
            return redirect()->back()
                ->withInput()
                ->with('error', __('No se pudo guardar la serie de NCF: :message', ['message' => $exception->getMessage()]));
        }

        return redirect()->route('ncf-series.index')->with('success', __('Serie de NCF creada correctamente.'));
    }

    public function edit(NcfSeries $ncfSeries)
    {
        if (!$this->canManage($ncfSeries)) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $ncfTypes = $this->ncfTypeOptions($ncfSeries->ncf_type_id);

        return view('ncf_series.edit', compact('ncfSeries', 'ncfTypes'));
    }

    public function update(Request $request, NcfSeries $ncfSeries)
    {
        if (!$this->canManage($ncfSeries)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validated = $this->validateSeries($request, $ncfSeries);

        try {
            $ncfSeries->update($validated);
        } catch (\Throwable $exception) {
            return redirect()->back()
                ->withInput()
                ->with('error', __('No se pudo actualizar la serie de NCF: :message', ['message' => $exception->getMessage()]));
        }

        return redirect()->route('ncf-series.index')->with('success', __('Serie de NCF actualizada.'));
    }

    public function destroy(NcfSeries $ncfSeries)
    {
        if (!$this->canManage($ncfSeries)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $ncfSeries->active = false;
        $ncfSeries->save();

        return redirect()->route('ncf-series.index')->with('success', __('Serie de NCF desactivada.'));
    }

    protected function validateSeries(Request $request, ?NcfSeries $existingSeries = null): array
    {
        $validator = \Validator::make($request->all(), [
            'ncf_type_id' => 'required|exists:ncf_types,id',
            'series' => 'required|string|max:20',
            'start_number' => 'required|integer|min:1',
            'end_number' => 'required|integer|min:1|gte:start_number',
            'current_number' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'status' => 'required|in:activo,vencido,agotado',
            'active' => 'sometimes|boolean',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->filled('current_number')) {
                $current = (int) $request->current_number;
                $start = (int) $request->start_number;
                $end = (int) $request->end_number;

                if ($current < $start || $current > $end) {
                    $validator->errors()->add('current_number', __('El número actual debe estar dentro del rango.'));
                }
            }
        });

        return $validator->validate();
    }

    protected function ncfTypeOptions(?int $currentTypeId = null)
    {
        $types = NcfType::where(function ($query) {
            $query->where('created_by', \Auth::user()->creatorId())
                ->orWhere('created_by', 0);
        })
            ->where(function ($query) use ($currentTypeId) {
                $query->where('active', true);

                if ($currentTypeId) {
                    $query->orWhere('id', $currentTypeId);
                }
            })
            ->orderBy('code')
            ->get(['id', 'code', 'description']);

        return $types->mapWithKeys(function ($type) {
            $label = $type->code ? $type->code . ' - ' : '';
            $label .= $type->description;

            return [$type->id => $label];
        });
    }

    protected function canManage(NcfSeries $ncfSeries): bool
    {
        if (!\Auth::user()->can('config_ncf_series_manage')) {
            return false;
        }

        return in_array($ncfSeries->created_by, [\Auth::user()->creatorId(), 0], true);
    }
}
