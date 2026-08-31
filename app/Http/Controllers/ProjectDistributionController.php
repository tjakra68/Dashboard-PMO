<?php

namespace App\Http\Controllers;

use App\Models\ProjectDistribution;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectDistributionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $distributions = ProjectDistribution::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('project-distributions.index', compact('distributions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('project-distributions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        ProjectDistribution::create($this->validateDistribution($request));

        return redirect()->route('project-distributions.index')
            ->with('success', 'Data proyek berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProjectDistribution $projectDistribution): View
    {
        return view('project-distributions.edit', compact('projectDistribution'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProjectDistribution $projectDistribution): RedirectResponse
    {
        $projectDistribution->update($this->validateDistribution($request, $projectDistribution));

        return redirect()->route('project-distributions.index')
            ->with('success', 'Data proyek berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectDistribution $projectDistribution): RedirectResponse
    {
        $projectDistribution->delete();

        return redirect()->route('project-distributions.index')
            ->with('success', 'Data proyek berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateDistribution(Request $request, ?ProjectDistribution $distribution = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('project_distributions', 'name')->ignore($distribution)],
            'total_so' => ['required', 'numeric', 'min:0'],
            'taxation' => ['required', 'numeric', 'min:0'],
            'collection' => ['required', 'numeric', 'min:0'],
            'july_target' => ['required', 'numeric', 'min:0'],
            'july_actual' => ['required', 'numeric', 'min:0'],
            'forecast_aug' => ['required', 'numeric', 'min:0'],
            'forecast_sep' => ['required', 'numeric', 'min:0'],
            'forecast_oct' => ['required', 'numeric', 'min:0'],
            'forecast_nov' => ['required', 'numeric', 'min:0'],
            'forecast_dec' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
