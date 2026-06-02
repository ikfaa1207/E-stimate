<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectCompliance;
use App\Models\ProjectPhase;
use App\Models\ProjectTask;

class ProjectSetupService
{
    /**
     * Set up a project with standard Philippine compliance checklists and construction workflows.
     */
    public function setup(Project $project): void
    {
        $this->setupCompliances($project);
        $this->setupWorkflow($project);
    }

    private function setupCompliances(Project $project): void
    {
        $compliances = [];

        // 1. General Permits (All building types)
        $compliances[] = [
            'name' => 'Barangay Clearance',
            'type' => 'clearance',
            'fee' => 500.00,
            'remarks' => 'Required from the local barangay before zoning/building permits.'
        ];

        $compliances[] = [
            'name' => 'Zoning / Locational Clearance',
            'type' => 'clearance',
            'fee' => 2000.00,
            'remarks' => 'Verifies compliance with local LGU land use plan.'
        ];

        $compliances[] = [
            'name' => 'Fire Safety Evaluation Clearance (FSEC)',
            'type' => 'clearance',
            'fee' => 3000.00,
            'remarks' => 'Obtained from the Bureau of Fire Protection (BFP) prior to building permit.'
        ];

        $compliances[] = [
            'name' => 'Building Permit',
            'type' => 'permit',
            'fee' => 10000.00,
            'remarks' => 'Primary permit issued by LGU Building Official.'
        ];

        $compliances[] = [
            'name' => 'Sanitary Permit',
            'type' => 'permit',
            'fee' => 1500.00,
            'remarks' => 'Compliance with Code on Sanitation (PD 856).'
        ];

        $compliances[] = [
            'name' => 'Electrical Permit',
            'type' => 'permit',
            'fee' => 2000.00,
            'remarks' => 'Required for all electrical installations.'
        ];

        // 2. Commercial / Industrial / Institutional Permits
        if (in_array($project->building_type, ['commercial', 'industrial', 'institutional'])) {
            $compliances[] = [
                'name' => 'Environmental Compliance Certificate (ECC) / CNC',
                'type' => 'clearance',
                'fee' => 5000.00,
                'remarks' => 'Required from DENR-EMB for environment-sensitive buildings.'
            ];

            $compliances[] = [
                'name' => 'OSHS Safety Program Approval',
                'type' => 'code',
                'fee' => 1000.00,
                'remarks' => 'Occupational Safety and Health Standards construction program approved by DOLE.'
            ];
        }

        if (in_array($project->building_type, ['commercial', 'institutional'])) {
            $compliances[] = [
                'name' => 'Mechanical Permit',
                'type' => 'permit',
                'fee' => 3000.00,
                'remarks' => 'Required for elevators, escalators, and central air conditioning systems.'
            ];
        }

        // 3. Industrial Specific Permits
        if ($project->building_type === 'industrial') {
            $compliances[] = [
                'name' => 'DENR Wastewater Discharge Permit',
                'type' => 'permit',
                'fee' => 4000.00,
                'remarks' => 'Required under RA 9275 Clean Water Act.'
            ];

            $compliances[] = [
                'name' => 'Permit to Operate (Air Pollution Source)',
                'type' => 'permit',
                'fee' => 4000.00,
                'remarks' => 'Required under RA 8749 Clean Air Act.'
            ];
        }

        foreach ($compliances as $compliance) {
            $project->compliances()->create($compliance);
        }
    }

    private function setupWorkflow(Project $project): void
    {
        $phases = [
            [
                'name' => 'Pre-Construction & Permitting',
                'sequence' => 1,
                'days_to_due' => 7,
                'tasks' => [
                    ['name' => 'Secure Barangay, Fire (FSEC), and Building permits', 'estimated_cost' => 15000.00],
                    ['name' => 'Site survey, boundary verification, and soil testing', 'estimated_cost' => 20000.00],
                ]
            ],
            [
                'name' => 'Earthworks & Foundation',
                'sequence' => 2,
                'days_to_due' => 21,
                'tasks' => array_merge(
                    [['name' => 'Site clearing, grading, and layout layouting', 'estimated_cost' => 10000.00]],
                    $this->getFoundationTasks($project)
                )
            ],
            [
                'name' => 'Structural Framing',
                'sequence' => 3,
                'days_to_due' => 45,
                'tasks' => $this->getStructuralTasks($project)
            ],
            [
                'name' => 'Masonry & Partitioning',
                'sequence' => 4,
                'days_to_due' => 60,
                'tasks' => [
                    ['name' => 'Concrete hollow block (CHB) laying for walls', 'estimated_cost' => 35000.00],
                    ['name' => 'Wall plastering, drywalls, and door frame fixing', 'estimated_cost' => 15000.00],
                ]
            ],
            [
                'name' => 'Plumbing, Sanitary & Drainage',
                'sequence' => 5,
                'days_to_due' => 75,
                'tasks' => [
                    ['name' => 'Water supply piping and fixture rough-ins', 'estimated_cost' => 12000.00],
                    ['name' => 'Sewer lines and drainage network installation', 'estimated_cost' => 18000.00],
                    ['name' => 'Construction of septic vault (septic tank) / STP compliance', 'estimated_cost' => 25000.00],
                ]
            ],
            [
                'name' => 'Electrical Works',
                'sequence' => 6,
                'days_to_due' => 90,
                'tasks' => [
                    ['name' => 'Conduits laying, junction/utility boxes rough-ins', 'estimated_cost' => 10000.00],
                    ['name' => 'Wiring, grounding, and panelboard install', 'estimated_cost' => 15000.00],
                    ['name' => 'Fixtures, receptacles, and switch fitting', 'estimated_cost' => 8000.00],
                ]
            ],
            [
                'name' => 'Mechanical, Finishing & Fire Protection',
                'sequence' => 7,
                'days_to_due' => 120,
                'tasks' => $this->getMechanicalAndFinishingTasks($project)
            ],
            [
                'name' => 'Post-Construction & Handover',
                'sequence' => 8,
                'days_to_due' => 135,
                'tasks' => [
                    ['name' => 'Final site cleaning, touch-ups, and inspection', 'estimated_cost' => 10000.00],
                    ['name' => 'Obtain Fire Safety Inspection Certificate (FSIC) from BFP', 'estimated_cost' => 5000.00],
                    ['name' => 'Obtain Certificate of Occupancy from LGU', 'estimated_cost' => 10000.00],
                ]
            ],
        ];

        foreach ($phases as $phaseData) {
            $phase = $project->phases()->create([
                'name' => $phaseData['name'],
                'sequence' => $phaseData['sequence'],
                'status' => 'pending',
            ]);

            foreach ($phaseData['tasks'] as $taskData) {
                $phase->tasks()->create([
                    'name' => $taskData['name'],
                    'status' => 'pending',
                    'estimated_cost' => $taskData['estimated_cost'],
                    'actual_cost' => 0.00,
                    'target_date' => now()->addDays($phaseData['days_to_due'])->toDateString(),
                ]);
            }
        }
    }

    private function getFoundationTasks(Project $project): array
    {
        if ($project->foundation_type === 'pile') {
            return [
                ['name' => 'Pile driving, capping, and integrity tests', 'estimated_cost' => 80000.00],
            ];
        }

        if ($project->foundation_type === 'raft') {
            return [
                ['name' => 'Raft foundation rebar setup and gravel bedding', 'estimated_cost' => 45000.00],
                ['name' => 'Pouring of raft foundation slab', 'estimated_cost' => 60000.00],
            ];
        }

        // Default footing
        return [
            ['name' => 'Excavation of column footings and tie beams', 'estimated_cost' => 20000.00],
            ['name' => 'Concreting of column footings and tie beams', 'estimated_cost' => 35000.00],
        ];
    }

    private function getStructuralTasks(Project $project): array
    {
        if ($project->structural_type === 'steel') {
            return [
                ['name' => 'Steel columns and beams fabrication off-site', 'estimated_cost' => 90000.00],
                ['name' => 'Erection and alignment of structural steel frame', 'estimated_cost' => 50000.00],
                ['name' => 'Steel roofing trusses erection and metal decking', 'estimated_cost' => 40000.00],
                ['name' => 'Welding inspection and NDT (Non-Destructive Testing)', 'estimated_cost' => 15000.00],
            ];
        }

        if ($project->structural_type === 'mixed') {
            return [
                ['name' => 'Pouring of reinforced concrete columns and ground beams', 'estimated_cost' => 60000.00],
                ['name' => 'Fabrication and erection of steel roof trusses', 'estimated_cost' => 35000.00],
                ['name' => 'Installation of floor metal decking and slab pouring', 'estimated_cost' => 45000.00],
            ];
        }

        // Default concrete
        return [
            ['name' => 'Formworks and scaffolding fabrication', 'estimated_cost' => 25000.00],
            ['name' => 'Column and beam reinforcing bars installation', 'estimated_cost' => 45000.00],
            ['name' => 'Concrete pouring for columns, beams, and slabs', 'estimated_cost' => 80000.00],
            ['name' => 'Stripping of formworks and concrete curing', 'estimated_cost' => 10000.00],
        ];
    }

    private function getMechanicalAndFinishingTasks(Project $project): array
    {
        $tasks = [
            ['name' => 'Ceiling framing and board installation', 'estimated_cost' => 15000.00],
            ['name' => 'Tiling, wood, or vinyl flooring finish', 'estimated_cost' => 25000.00],
            ['name' => 'Interior and exterior painting works', 'estimated_cost' => 20000.00],
            ['name' => 'Installation of doors and windows', 'estimated_cost' => 18000.00],
        ];

        // Commercial/Industrial/Institutional compliance safety systems
        if (in_array($project->building_type, ['commercial', 'industrial', 'institutional'])) {
            $tasks[] = ['name' => 'Fire sprinkler piping, valves, and heads install', 'estimated_cost' => 50000.00];
            $tasks[] = ['name' => 'Fire alarm, detectors, and emergency lighting', 'estimated_cost' => 15000.00];
            $tasks[] = ['name' => 'HVAC systems and ventilation duct installation', 'estimated_cost' => 45000.00];
        } else {
            $tasks[] = ['name' => 'Installation of smoke detectors and fire extinguishers', 'estimated_cost' => 5000.00];
        }

        return $tasks;
    }

    /**
     * Synchronize compliances and workflow tasks if project parameters changed.
     */
    public function sync(Project $project, string $oldFoundation, string $oldStructural, string $oldBuilding): void
    {
        // 1. Sync compliances if building type changed
        if ($project->building_type !== $oldBuilding) {
            $this->syncCompliances($project);
        }

        // 2. Sync foundation tasks if foundation type changed
        if ($project->foundation_type !== $oldFoundation) {
            $phase = $project->phases()->where('sequence', 2)->first();
            if ($phase) {
                $phase->tasks()->delete();
                $tasks = array_merge(
                    [['name' => 'Site clearing, grading, and layout layouting', 'estimated_cost' => 10000.00]],
                    $this->getFoundationTasks($project)
                );
                foreach ($tasks as $taskData) {
                    $phase->tasks()->create([
                        'name' => $taskData['name'],
                        'status' => 'pending',
                        'estimated_cost' => $taskData['estimated_cost'],
                        'actual_cost' => 0.00,
                        'target_date' => now()->addDays(21)->toDateString(),
                    ]);
                }
                $phase->update(['status' => 'pending', 'start_date' => null, 'end_date' => null]);
            }
        }

        // 3. Sync structural tasks if structural type changed
        if ($project->structural_type !== $oldStructural) {
            $phase = $project->phases()->where('sequence', 3)->first();
            if ($phase) {
                $phase->tasks()->delete();
                $tasks = $this->getStructuralTasks($project);
                foreach ($tasks as $taskData) {
                    $phase->tasks()->create([
                        'name' => $taskData['name'],
                        'status' => 'pending',
                        'estimated_cost' => $taskData['estimated_cost'],
                        'actual_cost' => 0.00,
                        'target_date' => now()->addDays(45)->toDateString(),
                    ]);
                }
                $phase->update(['status' => 'pending', 'start_date' => null, 'end_date' => null]);
            }
        }

        // 4. Sync mechanical/finishing tasks if building type changed
        if ($project->building_type !== $oldBuilding) {
            $phase = $project->phases()->where('sequence', 7)->first();
            if ($phase) {
                $phase->tasks()->delete();
                $tasks = $this->getMechanicalAndFinishingTasks($project);
                foreach ($tasks as $taskData) {
                    $phase->tasks()->create([
                        'name' => $taskData['name'],
                        'status' => 'pending',
                        'estimated_cost' => $taskData['estimated_cost'],
                        'actual_cost' => 0.00,
                        'target_date' => now()->addDays(120)->toDateString(),
                    ]);
                }
                $phase->update(['status' => 'pending', 'start_date' => null, 'end_date' => null]);
            }
        }
    }

    private function syncCompliances(Project $project): void
    {
        $requiredNames = [
            'Barangay Clearance',
            'Zoning / Locational Clearance',
            'Fire Safety Evaluation Clearance (FSEC)',
            'Building Permit',
            'Sanitary Permit',
            'Electrical Permit'
        ];

        if (in_array($project->building_type, ['commercial', 'industrial', 'institutional'])) {
            $requiredNames[] = 'Environmental Compliance Certificate (ECC) / CNC';
            $requiredNames[] = 'OSHS Safety Program Approval';
        }

        if (in_array($project->building_type, ['commercial', 'institutional'])) {
            $requiredNames[] = 'Mechanical Permit';
        }

        if ($project->building_type === 'industrial') {
            $requiredNames[] = 'DENR Wastewater Discharge Permit';
            $requiredNames[] = 'Permit to Operate (Air Pollution Source)';
        }

        // Delete compliances that are no longer required
        $project->compliances()->whereNotIn('name', $requiredNames)->delete();

        // Add required compliances that do not exist yet
        $existingNames = $project->compliances()->pluck('name')->toArray();
        
        $allPossible = [
            'Barangay Clearance' => [
                'type' => 'clearance',
                'fee' => 500.00,
                'remarks' => 'Required from the local barangay before zoning/building permits.'
            ],
            'Zoning / Locational Clearance' => [
                'type' => 'clearance',
                'fee' => 2000.00,
                'remarks' => 'Verifies compliance with local LGU land use plan.'
            ],
            'Fire Safety Evaluation Clearance (FSEC)' => [
                'type' => 'clearance',
                'fee' => 3000.00,
                'remarks' => 'Obtained from the Bureau of Fire Protection (BFP) prior to building permit.'
            ],
            'Building Permit' => [
                'type' => 'permit',
                'fee' => 10000.00,
                'remarks' => 'Primary permit issued by LGU Building Official.'
            ],
            'Sanitary Permit' => [
                'type' => 'permit',
                'fee' => 1500.00,
                'remarks' => 'Compliance with Code on Sanitation (PD 856).'
            ],
            'Electrical Permit' => [
                'type' => 'permit',
                'fee' => 2000.00,
                'remarks' => 'Required for all electrical installations.'
            ],
            'Environmental Compliance Certificate (ECC) / CNC' => [
                'type' => 'clearance',
                'fee' => 5000.00,
                'remarks' => 'Required from DENR-EMB for environment-sensitive buildings.'
            ],
            'OSHS Safety Program Approval' => [
                'type' => 'code',
                'fee' => 1000.00,
                'remarks' => 'Occupational Safety and Health Standards construction program approved by DOLE.'
            ],
            'Mechanical Permit' => [
                'type' => 'permit',
                'fee' => 3000.00,
                'remarks' => 'Required for elevators, escalators, and central air conditioning systems.'
            ],
            'DENR Wastewater Discharge Permit' => [
                'type' => 'permit',
                'fee' => 4000.00,
                'remarks' => 'Required under RA 9275 Clean Water Act.'
            ],
            'Permit to Operate (Air Pollution Source)' => [
                'type' => 'permit',
                'fee' => 4000.00,
                'remarks' => 'Required under RA 8749 Clean Air Act.'
            ],
        ];

        foreach ($requiredNames as $name) {
            if (!in_array($name, $existingNames)) {
                $project->compliances()->create(array_merge(['name' => $name], $allPossible[$name]));
            }
        }
    }
}
