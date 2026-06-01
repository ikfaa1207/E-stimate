<?php

namespace App\Http\Controllers;

use App\Models\Estimate;
use App\Models\EstimateAdjustment;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class EstimateAdjustmentController extends Controller
{
    public function store(Request $request, Project $project, Estimate $estimate): RedirectResponse
    {
        abort_unless((int) $estimate->project_id === (int) $project->id, 404);
        Gate::authorize('update', $project);
        abort_unless($estimate->isEditable(), 403, 'This estimate has been locked/approved and cannot be modified.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric'],
            'type' => ['required', 'in:material,labor,equipment'],
        ]);

        DB::transaction(function () use ($estimate, $validated) {
            $estimate->adjustments()->create($validated);

            // Update category breakdown
            $breakdown = $estimate->breakdowns()->firstOrCreate([
                'type' => $validated['type'],
            ], [
                'amount' => 0.0,
            ]);
            $breakdown->increment('amount', (float) $validated['amount']);

            $this->recalculateEstimateTotals($estimate);
        });

        return redirect()
            ->route('projects.estimates.show', [$project, $estimate])
            ->with('status', 'Adjustment added successfully.');
    }

    public function update(Request $request, Project $project, Estimate $estimate, EstimateAdjustment $adjustment): RedirectResponse
    {
        abort_unless((int) $estimate->project_id === (int) $project->id, 404);
        abort_unless((int) $adjustment->estimate_id === (int) $estimate->id, 404);
        Gate::authorize('update', $project);
        abort_unless($estimate->isEditable(), 403, 'This estimate has been locked/approved and cannot be modified.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric'],
            'type' => ['required', 'in:material,labor,equipment'],
        ]);

        DB::transaction(function () use ($estimate, $adjustment, $validated) {
            // Revert old breakdown change
            $oldBreakdown = $estimate->breakdowns()->where('type', $adjustment->type)->first();
            if ($oldBreakdown) {
                $oldBreakdown->decrement('amount', (float) $adjustment->amount);
            }

            // Apply new breakdown change
            $newBreakdown = $estimate->breakdowns()->firstOrCreate([
                'type' => $validated['type'],
            ], [
                'amount' => 0.0,
            ]);
            $newBreakdown->increment('amount', (float) $validated['amount']);

            // Update adjustment
            $adjustment->update($validated);

            $this->recalculateEstimateTotals($estimate);
        });

        return redirect()
            ->route('projects.estimates.show', [$project, $estimate])
            ->with('status', 'Adjustment updated successfully.');
    }

    public function destroy(Project $project, Estimate $estimate, EstimateAdjustment $adjustment): RedirectResponse
    {
        abort_unless((int) $estimate->project_id === (int) $project->id, 404);
        abort_unless((int) $adjustment->estimate_id === (int) $estimate->id, 404);
        Gate::authorize('update', $project);
        abort_unless($estimate->isEditable(), 403, 'This estimate has been locked/approved and cannot be modified.');

        DB::transaction(function () use ($estimate, $adjustment) {
            // Revert breakdown change
            $breakdown = $estimate->breakdowns()->where('type', $adjustment->type)->first();
            if ($breakdown) {
                $breakdown->decrement('amount', (float) $adjustment->amount);
            }

            $adjustment->delete();

            $this->recalculateEstimateTotals($estimate);
        });

        return redirect()
            ->route('projects.estimates.show', [$project, $estimate])
            ->with('status', 'Adjustment deleted successfully.');
    }

    private function recalculateEstimateTotals(Estimate $estimate): void
    {
        $baseCost = (float) $estimate->lines()->sum('line_total');
        $adjustmentsCost = (float) $estimate->adjustments()->sum('amount');
        $totalCost = $baseCost + $adjustmentsCost;

        $costPerSqm = (float) $estimate->gross_floor_area > 0
            ? $totalCost / (float) $estimate->gross_floor_area
            : 0.0;

        $estimate->update([
            'total_cost' => round($totalCost, 2),
            'cost_per_sqm' => round($costPerSqm, 2),
        ]);
    }
}
