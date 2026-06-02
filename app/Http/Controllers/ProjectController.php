<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $query = Project::query()
            ->with(['requirement', 'estimates' => fn ($query) => $query->latest('generated_at')->limit(1)]);

        if ($request->user()->isClient()) {
            $query->where('client_id', $request->user()->id);
        }

        $projects = $query->latest()->paginate(12);

        return view('projects.index', compact('projects'));
    }

    public function create(): View
    {
        Gate::authorize('create', Project::class);

        $clients = \App\Models\User::where('role', \App\Models\User::ROLE_CLIENT)->get();

        return view('projects.create', compact('clients'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Project::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'client_name' => ['required', 'string', 'max:255'],
            'lot_area' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'client_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $user = \App\Models\User::find($value);
                    if ($user && !$user->isClient()) {
                        $fail('The selected user is not a client.');
                    }
                }
            ],
            'building_type' => ['required', 'in:residential,commercial,industrial,institutional'],
            'structural_type' => ['required', 'in:concrete,steel,mixed'],
            'foundation_type' => ['required', 'in:footing,pile,raft'],
            'number_of_floors' => ['required', 'integer', 'min:1'],
            'gross_floor_area' => ['required', 'numeric', 'min:0.01'],
            'footprint_area' => ['required', 'numeric', 'min:0.01'],
            'finish_level' => ['required', 'in:economy,standard,premium'],
        ]);

        $project = Project::query()->create($validated);

        app(\App\Services\ProjectSetupService::class)->setup($project);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Project created and initialized with compliance checklists and construction workflow.');
    }

    public function show(Project $project): View
    {
        Gate::authorize('view', $project);

        $project->load([
            'compliances',
            'phases.tasks.comments',
            'comments',
            'estimates' => fn ($query) => $query
                ->with(['lines', 'breakdowns', 'adjustments', 'comments'])
                ->latest('generated_at'),
        ]);

        $latestEstimate = $project->estimates->first();

        return view('projects.show', compact('project', 'latestEstimate'));
    }

    public function edit(Project $project): View
    {
        Gate::authorize('update', $project);

        $clients = \App\Models\User::where('role', \App\Models\User::ROLE_CLIENT)->get();

        return view('projects.edit', compact('project', 'clients'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        Gate::authorize('update', $project);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'client_name' => ['required', 'string', 'max:255'],
            'lot_area' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'client_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $user = \App\Models\User::find($value);
                    if ($user && !$user->isClient()) {
                        $fail('The selected user is not a client.');
                    }
                }
            ],
            'building_type' => ['required', 'in:residential,commercial,industrial,institutional'],
            'structural_type' => ['required', 'in:concrete,steel,mixed'],
            'foundation_type' => ['required', 'in:footing,pile,raft'],
            'number_of_floors' => ['required', 'integer', 'min:1'],
            'gross_floor_area' => ['required', 'numeric', 'min:0.01'],
            'footprint_area' => ['required', 'numeric', 'min:0.01'],
            'finish_level' => ['required', 'in:economy,standard,premium'],
        ]);

        $oldFoundation = $project->foundation_type;
        $oldStructural = $project->structural_type;
        $oldBuilding = $project->building_type;

        $project->update($validated);

        app(\App\Services\ProjectSetupService::class)->sync($project, $oldFoundation, $oldStructural, $oldBuilding);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Project updated and building components synchronized successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        Gate::authorize('delete', $project);

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('status', 'Project deleted.');
    }
}
