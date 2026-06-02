<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectCompliance;
use App\Models\ProjectTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectWorkflowController extends Controller
{
    /**
     * Update a permit compliance item.
     */
    public function updateCompliance(Request $request, Project $project, ProjectCompliance $compliance): \Symfony\Component\HttpFoundation\Response
    {
        Gate::authorize('update', $project);
        abort_unless((int) $compliance->project_id === (int) $project->id, 404);

        $validated = $request->validate([
            'status' => ['required', 'in:not_started,pending,approved,not_applicable'],
            'fee' => ['required', 'numeric', 'min:0'],
            'target_date' => ['nullable', 'date'],
            'approved_at' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validated['status'] === 'approved' && empty($validated['approved_at'])) {
            $validated['approved_at'] = now()->toDateString();
        }

        $compliance->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Compliance item '{$compliance->name}' updated successfully.",
                'compliance' => [
                    'id' => $compliance->id,
                    'status' => $compliance->status,
                    'fee' => $compliance->fee,
                    'target_date' => $compliance->target_date ? $compliance->target_date->format('Y-m-d') : null,
                    'approved_at' => $compliance->approved_at ? $compliance->approved_at->format('Y-m-d') : null,
                    'remarks' => $compliance->remarks,
                ]
            ]);
        }

        return redirect()
            ->route('projects.show', $project)
            ->with('status', "Compliance item '{$compliance->name}' updated successfully.");
    }

    /**
     * Update a construction task.
     */
    public function updateTask(Request $request, Project $project, ProjectTask $task): \Symfony\Component\HttpFoundation\Response
    {
        Gate::authorize('update', $project);
        abort_unless((int) $task->phase->project_id === (int) $project->id, 404);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,completed'],
            'actual_cost' => ['required', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'target_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validated['status'] === 'completed' && empty($validated['end_date'])) {
            $validated['end_date'] = now()->toDateString();
        }
        if ($validated['status'] === 'in_progress' && empty($validated['start_date'])) {
            $validated['start_date'] = now()->toDateString();
        }

        $task->update($validated);

        // Check and update phase status
        $phase = $task->phase;
        $allTasks = $phase->tasks()->get(); // refresh tasks from DB
        
        $completedCount = $allTasks->where('status', 'completed')->count();
        $inProgressCount = $allTasks->where('status', 'in_progress')->count();

        if ($completedCount === $allTasks->count()) {
            $maxEndDate = $allTasks->max(function ($t) {
                return $t->end_date ? $t->end_date->toDateString() : null;
            });
            $phase->update([
                'status' => 'completed',
                'end_date' => $maxEndDate ?: now()->toDateString(),
            ]);
        } elseif ($completedCount > 0 || $inProgressCount > 0) {
            $maxStartDate = $allTasks->min(function ($t) {
                return $t->start_date ? $t->start_date->toDateString() : null;
            });
            $phase->update([
                'status' => 'in_progress',
                'start_date' => $maxStartDate ?: now()->toDateString(),
            ]);
        } else {
            $phase->update(['status' => 'pending']);
        }

        if ($request->wantsJson()) {
            // Freshly compute project progress and totals
            $project = $project->fresh(['phases.tasks']);
            $totalTasksCount = 0;
            $completedTasksCount = 0;
            $estimatedTotalSum = 0.0;
            $actualTotalSum = 0.0;
            foreach($project->phases as $p) {
                $totalTasksCount += $p->tasks->count();
                $completedTasksCount += $p->tasks->where('status', 'completed')->count();
                $estimatedTotalSum += (float) $p->tasks->sum('estimated_cost');
                $actualTotalSum += (float) $p->tasks->sum('actual_cost');
            }
            $progressPercent = $totalTasksCount > 0 ? round(($completedTasksCount / $totalTasksCount) * 100) : 0;
            $balance = $estimatedTotalSum - $actualTotalSum;

            return response()->json([
                'success' => true,
                'message' => "Task '{$task->name}' updated successfully.",
                'task' => [
                    'id' => $task->id,
                    'status' => $task->status,
                    'actual_cost' => $task->actual_cost,
                    'start_date' => $task->start_date ? $task->start_date->format('Y-m-d') : null,
                    'end_date' => $task->end_date ? $task->end_date->format('Y-m-d') : null,
                    'target_date' => $task->target_date ? $task->target_date->format('Y-m-d') : null,
                    'is_overdue' => $task->isOverdue(),
                    'remarks' => $task->remarks,
                ],
                'phase' => [
                    'id' => $phase->id,
                    'status' => $phase->status,
                    'is_delayed' => $phase->fresh()->isDelayed(),
                    'start_date' => $phase->start_date ? $phase->start_date->format('Y-m-d') : null,
                    'end_date' => $phase->end_date ? $phase->end_date->format('Y-m-d') : null,
                ],
                'project' => [
                    'progress_percent' => $progressPercent,
                    'estimated_total' => $estimatedTotalSum,
                    'actual_total' => $actualTotalSum,
                    'balance' => $balance,
                ]
            ]);
        }

        return redirect()
            ->route('projects.show', $project)
            ->with('status', "Task '{$task->name}' updated successfully.");
    }
}
