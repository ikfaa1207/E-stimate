<?php

namespace App\Http\Controllers;

use App\Exports\EstimateExport;
use App\Models\Estimate;
use App\Models\Project;
use App\Services\EstimateGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class EstimateController extends Controller
{
    public function __construct(
        private readonly EstimateGenerator $estimateGenerator
    ) {}

    public function store(Project $project): RedirectResponse
    {
        Gate::authorize('update', $project);

        try {
            $estimate = $this->estimateGenerator->generate($project);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('projects.show', $project)
                ->with('status', 'Estimate generation failed. Please validate your items, assemblies, and mappings.');
        }

        return redirect()
            ->route('projects.estimates.show', [$project, $estimate])
            ->with('status', 'Estimate generated successfully.');
    }

    public function show(Project $project, Estimate $estimate): View
    {
        abort_unless((int) $estimate->project_id === (int) $project->id, 404);
        Gate::authorize('view', $estimate);

        $estimate->load(['project', 'projectRequirement', 'lines', 'breakdowns', 'adjustments']);

        return view('estimates.show', compact('project', $estimate ? 'estimate' : []));
    }

    public function export(Project $project, Estimate $estimate)
    {
        abort_unless((int) $estimate->project_id === (int) $project->id, 404);
        Gate::authorize('view', $estimate);

        $estimate->load(['project', 'projectRequirement', 'lines', 'breakdowns', 'adjustments']);

        $fileName = Str::slug($project->name ?: 'project')
            .'-estimate-'.$estimate->id.'.xlsx';

        return Excel::download(new EstimateExport($estimate), $fileName);
    }

    public function lock(Project $project, Estimate $estimate): RedirectResponse
    {
        abort_unless((int) $estimate->project_id === (int) $project->id, 404);
        Gate::authorize('update', $project);

        $estimate->update(['status' => 'locked']);

        return redirect()
            ->route('projects.estimates.show', [$project, $estimate])
            ->with('status', 'Estimate has been locked and proposed.');
    }

    public function unlock(Project $project, Estimate $estimate): RedirectResponse
    {
        abort_unless((int) $estimate->project_id === (int) $project->id, 404);
        Gate::authorize('update', $project);

        $estimate->update(['status' => 'draft']);

        return redirect()
            ->route('projects.estimates.show', [$project, $estimate])
            ->with('status', 'Estimate has been unlocked.');
    }

    public function approve(Project $project, Estimate $estimate): RedirectResponse
    {
        abort_unless((int) $estimate->project_id === (int) $project->id, 404);

        abort_unless(
            auth()->user()->isClient() && (int) $project->client_id === (int) auth()->user()->id,
            403,
            'Only the assigned client can approve this estimate.'
        );

        abort_unless(
            $estimate->isLocked(),
            400,
            'Only proposed estimates can be approved.'
        );

        $estimate->update(['status' => 'approved']);

        return redirect()
            ->route('projects.estimates.show', [$project, $estimate])
            ->with('status', 'Estimate has been approved.');
    }
}
