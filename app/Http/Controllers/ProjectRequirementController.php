<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectRequirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectRequirementController extends Controller
{
    public function edit(Project $project): View
    {
        $requirement = $project->requirement()->firstOrNew();
        $spaces = \App\Models\Space::query()->where('is_active', true)->get()->keyBy('category');
        $finishLevels = \App\Models\FinishLevel::query()->where('is_active', true)->orderBy('name')->get();

        return view('project-requirements.edit', compact('project', 'requirement', 'spaces', 'finishLevels'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'number_of_floors' => ['required', 'integer', 'min:1'],
            'bedrooms' => ['required', 'integer', 'min:0'],
            'bathrooms' => ['required', 'integer', 'min:0'],
            'garage_count' => ['required', 'integer', 'min:0'],
            'living_rooms' => ['required', 'integer', 'min:0'],
            'kitchen_count' => ['required', 'integer', 'min:0'],
            'finish_level' => ['required', 'string', 'exists:finish_levels,name'],
            'space_area_overrides' => ['nullable', 'array'],
            'space_area_overrides.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (isset($validated['space_area_overrides'])) {
            $validated['space_area_overrides'] = collect($validated['space_area_overrides'])
                ->map(fn ($val) => ($val === null || $val === '') ? null : (float) $val)
                ->filter(fn ($val) => $val !== null)
                ->toArray();
        }

        $project->requirement()->updateOrCreate(
            ['project_id' => $project->id],
            $validated
        );

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Requirements saved. Generate estimate when ready.');
    }
}
