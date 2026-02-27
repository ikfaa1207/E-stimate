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

        return view('project-requirements.edit', compact('project', 'requirement'));
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
            'finish_level' => ['required', 'in:'.implode(',', ProjectRequirement::FINISH_LEVELS)],
        ]);

        $project->requirement()->updateOrCreate(
            ['project_id' => $project->id],
            $validated
        );

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Requirements saved. Generate estimate when ready.');
    }
}
