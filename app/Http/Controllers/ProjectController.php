<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $projects = Project::query()
            ->with(['requirement', 'estimates' => fn ($query) => $query->latest('generated_at')->limit(1)])
            ->latest()
            ->paginate(12);

        return view('projects.index', compact('projects'));
    }

    public function create(): View
    {
        return view('projects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'client_name' => ['required', 'string', 'max:255'],
            'lot_area' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $project = Project::query()->create($validated);

        return redirect()
            ->route('projects.requirements.edit', $project)
            ->with('status', 'Project created. Complete requirement wizard next.');
    }

    public function show(Project $project): View
    {
        $project->load([
            'requirement',
            'estimates' => fn ($query) => $query
                ->with(['lines', 'breakdowns'])
                ->latest('generated_at'),
        ]);

        $latestEstimate = $project->estimates->first();

        return view('projects.show', compact('project', 'latestEstimate'));
    }

    public function edit(Project $project): View
    {
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'client_name' => ['required', 'string', 'max:255'],
            'lot_area' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $project->update($validated);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('status', 'Project deleted.');
    }
}
