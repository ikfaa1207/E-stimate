<?php

namespace App\Http\Controllers;

use App\Models\FinishLevel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinishLevelController extends Controller
{
    public function index(): View
    {
        $finishLevels = FinishLevel::query()
            ->orderBy('name')
            ->paginate(15);

        return view('finish-levels.index', compact('finishLevels'));
    }

    public function create(): View
    {
        return view('finish-levels.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:finish_levels,name'],
            'display_name' => ['required', 'string', 'max:255'],
            'material_multiplier' => ['required', 'numeric', 'min:0'],
            'labor_multiplier' => ['required', 'numeric', 'min:0'],
            'equipment_multiplier' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        FinishLevel::query()->create($validated);

        return redirect()
            ->route('finish-levels.index')
            ->with('status', 'Finish level created.');
    }

    public function show(FinishLevel $finishLevel): RedirectResponse
    {
        return redirect()->route('finish-levels.edit', $finishLevel);
    }

    public function edit(FinishLevel $finishLevel): View
    {
        return view('finish-levels.edit', compact('finishLevel'));
    }

    public function update(Request $request, FinishLevel $finishLevel): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:finish_levels,name,' . $finishLevel->id],
            'display_name' => ['required', 'string', 'max:255'],
            'material_multiplier' => ['required', 'numeric', 'min:0'],
            'labor_multiplier' => ['required', 'numeric', 'min:0'],
            'equipment_multiplier' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        $finishLevel->update($validated);

        return redirect()
            ->route('finish-levels.index')
            ->with('status', 'Finish level updated.');
    }

    public function destroy(FinishLevel $finishLevel): RedirectResponse
    {
        $finishLevel->delete();

        return redirect()
            ->route('finish-levels.index')
            ->with('status', 'Finish level deleted.');
    }
}
