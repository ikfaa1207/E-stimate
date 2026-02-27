<?php

namespace App\Http\Controllers;

use App\Models\Assembly;
use App\Models\AssemblyMapping;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssemblyMappingController extends Controller
{
    public function edit(): View
    {
        $assemblies = Assembly::query()->orderBy('name')->get();
        $mappings = AssemblyMapping::query()->pluck('assembly_id', 'metric_name');

        return view('assembly-mappings.edit', compact('assemblies', 'mappings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mappings' => ['required', 'array'],
            'mappings.*' => ['required', 'exists:assemblies,id'],
        ]);

        foreach (AssemblyMapping::METRICS as $metric) {
            if (! isset($validated['mappings'][$metric])) {
                continue;
            }

            AssemblyMapping::query()->updateOrCreate(
                ['metric_name' => $metric],
                ['assembly_id' => (int) $validated['mappings'][$metric]]
            );
        }

        return redirect()
            ->route('assembly-mappings.edit')
            ->with('status', 'Assembly mappings updated.');
    }
}
