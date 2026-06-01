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
        ]);

        $project = Project::query()->create($validated);

        return redirect()
            ->route('projects.requirements.edit', $project)
            ->with('status', 'Project created. Complete requirement wizard next.');
    }

    public function show(Project $project): View
    {
        Gate::authorize('view', $project);

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
        ]);

        $project->update($validated);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Project updated.');
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
