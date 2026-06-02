<?php

namespace App\Services;

use App\Models\Assembly;
use App\Models\AssemblyMapping;
use App\Models\Estimate;
use App\Models\Item;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EstimateGenerator
{
    public function __construct(
        private readonly BuildingCalculator $buildingCalculator
    ) {}

    public function generate(Project $project): Estimate
    {
        $project->loadMissing(['requirement', 'compliances']);

        if ((float) $project->gross_floor_area <= 0 && !$project->requirement) {
            throw ValidationException::withMessages([
                'gross_floor_area' => 'Project gross floor area must be specified to generate an estimate.',
            ]);
        }

        $metrics = $this->buildingCalculator->calculate($project);
        $metricValues = collect(AssemblyMapping::METRICS)
            ->mapWithKeys(fn (string $metric): array => [$metric => (float) ($metrics[$metric] ?? 0)]);

        $mappings = AssemblyMapping::query()
            ->with(['assembly.assemblyItems.item'])
            ->whereIn('metric_name', $metricValues->keys())
            ->get()
            ->keyBy('metric_name');

        $missingMappings = $metricValues->keys()
            ->filter(fn (string $metric): bool => ! $mappings->has($metric))
            ->values();

        if ($missingMappings->isNotEmpty()) {
            throw ValidationException::withMessages([
                'mapping' => 'Missing assembly mappings for: '.$missingMappings->implode(', '),
            ]);
        }

        $finishLevelName = $project->finish_level;
        if ($project->requirement && $project->gross_floor_area <= 0) {
            $finishLevelName = $project->requirement->finish_level;
        }

        $finishLevel = \App\Models\FinishLevel::where('name', $finishLevelName)->first();

        if (! $finishLevel) {
            throw ValidationException::withMessages([
                'finish_level' => "Finish level '{$finishLevelName}' is not defined in the system.",
            ]);
        }

        return DB::transaction(function () use (
            $project,
            $metrics,
            $metricValues,
            $mappings,
            $finishLevel,
            $finishLevelName
        ): Estimate {
            $estimate = Estimate::query()->create([
                'project_id' => $project->id,
                'project_requirement_id' => $project->requirement?->id,
                'finish_level' => $finishLevelName,
                'gross_floor_area' => $metrics['gross_floor_area'],
                'wall_area' => $metrics['wall_area'],
                'roof_area' => $metrics['roof_area'],
                'slab_area' => $metrics['slab_area'],
                'total_cost' => 0,
                'cost_per_sqm' => 0,
                'generated_at' => now(),
            ]);

            $breakdownTotals = collect(Item::TYPES)->mapWithKeys(
                fn (string $type): array => [$type => 0.0]
            );

            $totalCost = 0.0;

            foreach ($metricValues as $metricName => $metricValue) {
                $mapping = $mappings->get($metricName);
                $assembly = $mapping?->assembly;

                if (! $assembly || $metricValue <= 0) {
                    continue;
                }

                $adjustedUnitCost = $this->assemblyUnitCost($assembly, $finishLevel);
                $lineTotal = $metricValue * $adjustedUnitCost;

                $estimate->lines()->create([
                    'metric_name' => $metricName,
                    'assembly_id' => $assembly->id,
                    'item_name' => $assembly->name,
                    'quantity' => round($metricValue, 4),
                    'unit' => $assembly->unit,
                    'unit_cost' => round($adjustedUnitCost, 2),
                    'line_total' => round($lineTotal, 2),
                ]);

                $totalCost += $lineTotal;

                foreach ($assembly->assemblyItems as $assemblyItem) {
                    $item = $assemblyItem->item;

                    if (! $item) {
                        continue;
                    }

                    $lineBreakdown = $metricValue
                        * (float) $assemblyItem->qty_per_unit
                        * (float) $item->unit_cost
                        * $finishLevel->getMultiplierForType($item->type);

                    $breakdownTotals[$item->type] += $lineBreakdown;
                }
            }

            // Auto-inject Philippine government code compliance adjustments
            // 1. LGU Permits and Clearances Fees
            $complianceFees = (float) $project->compliances()->sum('fee');
            if ($complianceFees > 0) {
                $estimate->adjustments()->create([
                    'name' => 'Government Permits & Clearances Fees',
                    'amount' => $complianceFees,
                    'type' => 'equipment',
                ]);
                $breakdownTotals['equipment'] += $complianceFees;
                $totalCost += $complianceFees;
            }

            // 2. OSHS Safety Program Budget (PPE, signage, safety officer compliance)
            $oshsBudget = in_array($project->building_type, ['commercial', 'industrial', 'institutional']) ? 25000.00 : 5000.00;
            $estimate->adjustments()->create([
                'name' => 'OSHS Safety & Health Compliance (PPE, Signage, Safety Officer)',
                'amount' => $oshsBudget,
                'type' => 'labor',
            ]);
            $breakdownTotals['labor'] += $oshsBudget;
            $totalCost += $oshsBudget;

            // 3. Sanitary Code Compliance (Septic vault, proper drainage / STP)
            $sanitaryBudget = 15000.00;
            if ($project->building_type === 'commercial' || $project->building_type === 'institutional') {
                $sanitaryBudget = 50000.00;
            } elseif ($project->building_type === 'industrial') {
                $sanitaryBudget = 80000.00;
            }
            $estimate->adjustments()->create([
                'name' => 'Sanitary Code Compliance (Septic Vault / Drainage / STP)',
                'amount' => $sanitaryBudget,
                'type' => 'material',
            ]);
            $breakdownTotals['material'] += $sanitaryBudget;
            $totalCost += $sanitaryBudget;

            $costPerSqm = (float) $metrics['gross_floor_area'] > 0
                ? $totalCost / (float) $metrics['gross_floor_area']
                : 0.0;

            $estimate->update([
                'total_cost' => round($totalCost, 2),
                'cost_per_sqm' => round($costPerSqm, 2),
            ]);

            foreach ($breakdownTotals as $type => $amount) {
                $estimate->breakdowns()->create([
                    'type' => $type,
                    'amount' => round((float) $amount, 2),
                ]);
            }

            return $estimate->load(['project', 'projectRequirement', 'lines', 'breakdowns']);
        });
    }

    private function assemblyUnitCost(Assembly $assembly, \App\Models\FinishLevel $finishLevel): float
    {
        return (float) $assembly->assemblyItems->sum(
            fn ($assemblyItem): float => (float) $assemblyItem->qty_per_unit * (float) ($assemblyItem->item?->unit_cost ?? 0) * $finishLevel->getMultiplierForType($assemblyItem->item?->type ?? '')
        );
    }
}
