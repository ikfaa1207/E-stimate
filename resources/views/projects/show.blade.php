<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $project->name }}
            </h2>
            @can('update', $project)
            <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-xs font-semibold uppercase hover:bg-gray-300 transition">
                Edit Project Details
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Project Header Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-6 shadow-sm rounded-lg border">
                    <p class="text-xs text-gray-500 uppercase font-semibold">Client Name</p>
                    <p class="text-lg font-bold text-gray-800 mt-1">{{ $project->client_name }}</p>
                </div>
                <div class="bg-white p-6 shadow-sm rounded-lg border">
                    <p class="text-xs text-gray-500 uppercase font-semibold">Building Type</p>
                    <p class="text-lg font-bold text-gray-800 capitalize mt-1">{{ $project->building_type }}</p>
                </div>
                <div class="bg-white p-6 shadow-sm rounded-lg border">
                    <p class="text-xs text-gray-500 uppercase font-semibold">Gross Floor Area</p>
                    <p class="text-lg font-bold text-gray-800 mt-1">{{ number_format($project->gross_floor_area, 2) }} sqm</p>
                </div>
                <div class="bg-white p-6 shadow-sm rounded-lg border">
                    <p class="text-xs text-gray-500 uppercase font-semibold">Finish Level</p>
                    <span class="inline-flex mt-2 items-center px-2.5 py-0.5 rounded-full text-xs font-medium uppercase bg-indigo-100 text-indigo-800">
                        {{ $project->finish_level }}
                    </span>
                </div>
            </div>

            <!-- Share Link Banner -->
            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <svg class="h-6 w-6 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                    <div>
                        <h4 class="text-sm font-semibold text-indigo-900">Guest Shareable Link & PIN</h4>
                        <p class="text-xs text-indigo-700">Send this secure link and the access PIN to your client to let them track progress and approve estimates without an account.</p>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row w-full lg:w-auto items-stretch sm:items-center gap-4">
                    <div class="flex items-center justify-between sm:justify-start gap-2 bg-indigo-100/50 border border-indigo-200/60 rounded px-3 py-1">
                        <span class="text-[10px] font-bold text-indigo-900/60 uppercase tracking-wider">Access PIN:</span>
                        <div class="flex items-center gap-1">
                            <span class="font-mono text-sm font-bold text-indigo-800 tracking-wider">{{ $project->share_passcode }}</span>
                            <button onclick="copySharePin('{{ $project->share_passcode }}')" class="p-1 text-indigo-600 hover:text-indigo-900 rounded hover:bg-indigo-100 transition" title="Copy PIN">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-1 sm:flex-none">
                        <input type="text" readonly id="share-link-input" value="{{ route('projects.share.show', $project->share_token) }}" class="flex-1 sm:w-80 text-xs font-mono bg-white border border-indigo-200 rounded px-3 py-1.5 text-indigo-800 focus:outline-none" />
                        <button onclick="copyShareLink()" class="px-4 py-1.5 bg-indigo-600 text-white rounded text-xs font-semibold uppercase hover:bg-indigo-700 transition whitespace-nowrap">
                            Copy Link
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabbed Interface Container -->
            <div class="bg-white shadow-sm rounded-lg border overflow-hidden">
                <!-- Tabs Header -->
                <div class="border-b border-gray-200 bg-gray-50 px-6">
                    <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                        <button onclick="switchTab('specs')" id="tab-specs" class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm focus:outline-none transition">
                            Building Specifications
                        </button>
                        <button onclick="switchTab('compliance')" id="tab-compliance" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm focus:outline-none transition">
                            Permits & Code Compliance
                        </button>
                        <button onclick="switchTab('workflow')" id="tab-workflow" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm focus:outline-none transition">
                            Workflow & Cost Tracking
                        </button>
                    </nav>
                </div>

                <!-- Tab Panels -->
                <div class="p-6">
                    
                    <!-- Panel 1: Specs -->
                    <div id="panel-specs" class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Technical Specifications</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                            <div>
                                <span class="text-gray-500 block">Structural Frame Type:</span>
                                <span class="font-semibold text-gray-800 capitalize">{{ $project->structural_type }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Foundation Type:</span>
                                <span class="font-semibold text-gray-800 capitalize">{{ $project->foundation_type }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Number of Floors:</span>
                                <span class="font-semibold text-gray-800">{{ $project->number_of_floors }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Building Footprint Area:</span>
                                <span class="font-semibold text-gray-800">{{ number_format($project->footprint_area, 2) }} sqm</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Lot Area:</span>
                                <span class="font-semibold text-gray-800">{{ $project->lot_area ? number_format($project->lot_area, 2).' sqm' : '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block">Finish Standard:</span>
                                <span class="font-semibold text-gray-800 uppercase">{{ $project->finish_level }}</span>
                            </div>
                            <div class="md:col-span-3">
                                <span class="text-gray-500 block">Engineering / General Project Notes:</span>
                                <p class="text-gray-800 mt-1 bg-gray-50 p-3 rounded border">{{ $project->notes ?: 'No notes recorded.' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Panel 2: Permits & Compliance -->
                    <div id="panel-compliance" class="hidden space-y-4">
                        <div class="flex items-center justify-between border-b pb-2">
                            <h3 class="text-lg font-semibold text-gray-800">Philippine Code & LGU Clearance Checklist</h3>
                            <span class="text-xs text-gray-500">Auto-populated based on building type</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Requirement / Permit</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fee (PHP)</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Target Date</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Approved Date</th>
                                        @can('update', $project)
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Action</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                                    @forelse($project->compliances as $compliance)
                                        <tr id="compliance-view-row-{{ $compliance->id }}">
                                            <td class="px-4 py-4">
                                                <div class="font-medium text-gray-900">{{ $compliance->name }}</div>
                                                <div class="text-xs text-gray-500" id="compliance-remarks-{{ $compliance->id }}">{{ $compliance->remarks }}</div>
                                            </td>
                                            <td class="px-4 py-4 capitalize text-gray-500">{{ $compliance->type }}</td>
                                            <td class="px-4 py-4" id="compliance-status-{{ $compliance->id }}">
                                                @if($compliance->status === 'approved')
                                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                                                @elseif($compliance->status === 'pending')
                                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                                @elseif($compliance->status === 'not_applicable')
                                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-500">N/A</span>
                                                @else
                                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800 font-semibold">Not Started</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 font-mono" id="compliance-fee-{{ $compliance->id }}">₱{{ number_format($compliance->fee, 2) }}</td>
                                            <td class="px-4 py-4 text-gray-500" id="compliance-target-{{ $compliance->id }}">{{ $compliance->target_date ? $compliance->target_date->format('Y-m-d') : '-' }}</td>
                                            <td class="px-4 py-4 text-gray-500" id="compliance-approved-{{ $compliance->id }}">{{ $compliance->approved_at ? $compliance->approved_at->format('Y-m-d') : '-' }}</td>
                                            @can('update', $project)
                                            <td class="px-4 py-4 text-right">
                                                <button onclick="toggleComplianceEdit({{ $compliance->id }})" class="text-indigo-600 hover:text-indigo-900 font-semibold">Edit</button>
                                            </td>
                                            @endcan
                                        </tr>

                                        <!-- Expandable Edit Form Row -->
                                        @can('update', $project)
                                        <tr id="compliance-row-{{ $compliance->id }}" class="hidden bg-gray-50">
                                            <td colspan="7" class="px-6 py-4">
                                                <form method="POST" action="{{ route('projects.compliance.update', [$project, $compliance]) }}" class="compliance-ajax-form grid grid-cols-1 md:grid-cols-4 gap-4 items-end" data-compliance-id="{{ $compliance->id }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Status</label>
                                                        <select name="status" class="w-full border-gray-300 rounded shadow-sm text-sm">
                                                            <option value="not_started" @selected($compliance->status == 'not_started')>Not Started</option>
                                                            <option value="pending" @selected($compliance->status == 'pending')>Pending</option>
                                                            <option value="approved" @selected($compliance->status == 'approved')>Approved</option>
                                                            <option value="not_applicable" @selected($compliance->status == 'not_applicable')>Not Applicable</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Fee (PHP)</label>
                                                        <input type="number" step="0.01" name="fee" value="{{ $compliance->fee }}" class="w-full border-gray-300 rounded shadow-sm text-sm font-mono" required />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Target Date</label>
                                                        <input type="date" name="target_date" value="{{ $compliance->target_date ? $compliance->target_date->format('Y-m-d') : '' }}" class="w-full border-gray-300 rounded shadow-sm text-sm" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Remarks</label>
                                                        <input type="text" name="remarks" value="{{ $compliance->remarks }}" class="w-full border-gray-300 rounded shadow-sm text-sm" />
                                                    </div>
                                                    <div class="md:col-span-4 flex justify-end gap-2 mt-2">
                                                        <button type="button" onclick="toggleComplianceEdit({{ $compliance->id }})" class="px-3 py-1.5 bg-gray-200 text-xs font-semibold rounded uppercase">Cancel</button>
                                                        <x-primary-button class="py-1 px-3 text-xs font-semibold uppercase">Save Changes</x-primary-button>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                        @endcan
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">No compliance clearances initialized.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Panel 3: Workflow & Cost Tracking -->
                    <div id="panel-workflow" class="hidden space-y-4">
                        <div class="flex flex-col md:flex-row md:items-center justify-between border-b pb-2 gap-2">
                            <h3 class="text-lg font-semibold text-gray-800">Construction Work Flow Phases</h3>
                            <!-- Progress Bar -->
                            @php
                                $totalTasksCount = 0;
                                $completedTasksCount = 0;
                                $estimatedTotalSum = 0.0;
                                $actualTotalSum = 0.0;
                                foreach($project->phases as $phase) {
                                    $totalTasksCount += $phase->tasks->count();
                                    $completedTasksCount += $phase->tasks->where('status', 'completed')->count();
                                    $estimatedTotalSum += (float) $phase->tasks->sum('estimated_cost');
                                    $actualTotalSum += (float) $phase->tasks->sum('actual_cost');
                                }
                                $progressPercent = $totalTasksCount > 0 ? round(($completedTasksCount / $totalTasksCount) * 100) : 0;
                            @endphp
                            <div class="flex items-center gap-3 w-full md:w-auto">
                                <span class="text-sm text-gray-500 font-semibold">Overall Progress:</span>
                                <div class="w-48 bg-gray-200 rounded-full h-3">
                                    <div id="project-progress-bar" class="bg-green-600 h-3 rounded-full" style="width: {{ $progressPercent }}%"></div>
                                </div>
                                <span id="project-progress-text" class="text-sm font-bold text-gray-800">{{ $progressPercent }}%</span>
                            </div>
                        </div>

                        <!-- Budget Summary Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 p-4 rounded border">
                            <div class="text-center">
                                <span class="text-xs text-gray-500 uppercase font-semibold">Total Estimated Cost</span>
                                <p id="project-estimated-total" class="text-lg font-mono font-bold text-gray-800 mt-1">₱{{ number_format($estimatedTotalSum, 2) }}</p>
                            </div>
                            <div class="text-center border-t md:border-t-0 md:border-x py-2 md:py-0">
                                <span class="text-xs text-gray-500 uppercase font-semibold">Total Actual Cost Spent</span>
                                <p id="project-actual-total" class="text-lg font-mono font-bold text-gray-800 mt-1">₱{{ number_format($actualTotalSum, 2) }}</p>
                            </div>
                            <div class="text-center">
                                <span class="text-xs text-gray-500 uppercase font-semibold">Budget Balance</span>
                                @php $balance = $estimatedTotalSum - $actualTotalSum; @endphp
                                <p id="project-balance-container" class="text-lg font-mono font-bold mt-1 {{ $balance < 0 ? 'text-red-600' : 'text-green-600' }}">
                                    <span id="project-balance">₱{{ number_format($balance, 2) }}</span>
                                    <span id="project-over-budget-warning" class="text-xs block font-sans font-semibold {{ $balance < 0 ? '' : 'hidden' }}">(Over Budget!)</span>
                                </p>
                            </div>
                        </div>

                        <!-- Phases Accordion List -->
                        <div class="space-y-4 mt-4">
                            @foreach($project->phases as $phase)
                                <div class="border rounded-lg overflow-hidden shadow-sm" id="phase-container-{{ $phase->id }}">
                                    <!-- Phase Header -->
                                    <div class="bg-gray-100 px-4 py-3 flex items-center justify-between border-b">
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-indigo-600 text-white text-xs font-bold">{{ $phase->sequence }}</span>
                                            <h4 class="font-bold text-gray-800">{{ $phase->name }}</h4>
                                        </div>
                                        <div class="flex items-center gap-2" id="phase-status-{{ $phase->id }}">
                                            @if($phase->status === 'completed')
                                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-green-100 text-green-800 uppercase">Completed</span>
                                            @elseif($phase->isDelayed())
                                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-red-100 text-red-800 uppercase animate-pulse">⚠️ Delayed</span>
                                            @elseif($phase->status === 'in_progress')
                                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-blue-100 text-blue-800 uppercase">In Progress</span>
                                            @else
                                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-gray-200 text-gray-600 uppercase">Pending</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Tasks Table -->
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                                                    <th class="px-4 py-2.5">Task Description</th>
                                                    <th class="px-4 py-2.5">Status</th>
                                                    <th class="px-4 py-2.5">Est. Cost</th>
                                                    <th class="px-4 py-2.5">Act. Cost</th>
                                                    <th class="px-4 py-2.5">Target Date</th>
                                                    <th class="px-4 py-2.5">Actual Dates</th>
                                                    @can('update', $project)
                                                    <th class="px-4 py-2.5 text-right">Action</th>
                                                    @endcan
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 text-sm">
                                                @foreach($phase->tasks as $task)
                                                    <tr class="hover:bg-gray-50" id="task-view-row-{{ $task->id }}">
                                                        <td class="px-4 py-3">
                                                            <div class="font-medium text-gray-800">{{ $task->name }}</div>
                                                            <div class="text-xs text-gray-500 italic mt-0.5" id="task-remarks-{{ $task->id }}">{{ $task->remarks ?: '' }}</div>
                                                            <div class="flex items-center gap-2 mt-1">
                                                                <button type="button" onclick="toggleTaskComments({{ $task->id }})" class="text-xs text-indigo-600 hover:text-indigo-900 flex items-center gap-1 focus:outline-none">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                                                    </svg>
                                                                    <span id="task-comment-count-{{ $task->id }}">{{ $task->comments->count() }}</span> comments
                                                                    <span id="task-pending-revision-badge-{{ $task->id }}" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] bg-amber-100 text-amber-800 font-semibold {{ $task->comments->where('type', 'revision_request')->where('status', 'pending')->count() > 0 ? '' : 'hidden' }}">
                                                                        ⚠️ Revision Requested
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-3 capitalize" id="task-status-{{ $task->id }}">
                                                            @if($task->status === 'completed')
                                                                <span class="text-green-600 font-semibold">✓ Completed</span>
                                                            @elseif($task->isOverdue())
                                                                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">⚠️ Overdue</span>
                                                            @elseif($task->status === 'in_progress')
                                                                <span class="text-blue-600 font-semibold">→ In Progress</span>
                                                            @else
                                                                <span class="text-gray-400">Pending</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-3 font-mono text-gray-500" data-estimated-cost="{{ $task->estimated_cost }}">₱{{ number_format($task->estimated_cost, 2) }}</td>
                                                        <td class="px-4 py-3 font-mono font-semibold {{ (float)$task->actual_cost > (float)$task->estimated_cost ? 'text-red-600' : 'text-gray-800' }}" id="task-actual-cost-{{ $task->id }}">
                                                            ₱{{ number_format($task->actual_cost, 2) }}
                                                        </td>
                                                        <td class="px-4 py-3 font-mono text-gray-600" id="task-target-{{ $task->id }}">
                                                            {{ $task->target_date ? $task->target_date->format('Y-m-d') : '-' }}
                                                        </td>
                                                        <td class="px-4 py-3 text-xs text-gray-500" id="task-dates-{{ $task->id }}">
                                                            <div>S: {{ $task->start_date ? $task->start_date->format('Y-m-d') : '-' }}</div>
                                                            <div>E: {{ $task->end_date ? $task->end_date->format('Y-m-d') : '-' }}</div>
                                                        </td>
                                                        @can('update', $project)
                                                        <td class="px-4 py-3 text-right">
                                                            <button onclick="toggleTaskEdit({{ $task->id }})" class="text-indigo-600 hover:text-indigo-900 font-semibold">Update</button>
                                                        </td>
                                                        @endcan
                                                    </tr>

                                                    <!-- Task Comments expandable row -->
                                                    <tr id="task-comments-row-{{ $task->id }}" class="hidden bg-gray-50 border-b">
                                                        <td colspan="7" class="px-6 py-4">
                                                            <div class="max-w-3xl space-y-4">
                                                                <h5 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Collaboration & Task Discussion</h5>
                                                                
                                                                <!-- Comments List -->
                                                                <div class="space-y-3 max-h-60 overflow-y-auto pr-2" id="task-comments-list-{{ $task->id }}">
                                                                    @forelse($task->comments as $comment)
                                                                        <div class="p-3 rounded-lg border shadow-sm {{ $comment->isRevisionRequest() ? 'bg-amber-50/20 border-amber-200/60' : 'bg-gray-50/70 border-gray-200' }}" id="comment-container-{{ $comment->id }}">
                                                                            <div class="flex items-center justify-between">
                                                                                <div class="flex items-center gap-2">
                                                                                    <span class="font-bold text-xs text-gray-800">{{ $comment->author_name }}</span>
                                                                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold capitalize font-sans {{ in_array($comment->author_role, ['admin', 'estimator']) ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800' }}">
                                                                                        {{ $comment->author_role }}
                                                                                    </span>
                                                                                </div>
                                                                                <div class="flex items-center gap-2 text-xs text-gray-400">
                                                                                    <span>{{ $comment->created_at->diffForHumans() }}</span>
                                                                                    @can('update', $project)
                                                                                        <button onclick="deleteComment({{ $project->id }}, {{ $comment->id }}, 'task', {{ $task->id }})" class="text-rose-600 hover:text-rose-900 font-semibold ml-2">Delete</button>
                                                                                    @endcan
                                                                                </div>
                                                                            </div>
                                                                            <p class="text-sm text-gray-700 mt-1.5 whitespace-pre-line">{{ $comment->content }}</p>
                                                                            
                                                                            @if($comment->isRevisionRequest())
                                                                                <div class="mt-2 flex items-center justify-between border-t border-amber-200/40 pt-2" id="comment-status-bar-{{ $comment->id }}">
                                                                                    <span id="comment-badge-{{ $comment->id }}" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold font-sans {{ $comment->isResolved() ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800 animate-pulse font-sans' }}">
                                                                                        {{ $comment->isResolved() ? 'Resolved' : 'Pending Revision' }}
                                                                                    </span>
                                                                                    @if($comment->isPending())
                                                                                        @can('update', $project)
                                                                                            <button id="comment-resolve-btn-{{ $comment->id }}" onclick="resolveRevision({{ $project->id }}, {{ $comment->id }}, 'task', {{ $task->id }})" class="text-xs text-indigo-600 hover:text-indigo-900 font-bold underline font-sans">
                                                                                                Mark Resolved
                                                                                            </button>
                                                                                        @endcan
                                                                                    @endif
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    @empty
                                                                        <p class="text-xs text-gray-500 italic py-2" id="task-comments-empty-{{ $task->id }}">No discussion for this task yet.</p>
                                                                    @endforelse
                                                                </div>
                                                                
                                                                <!-- Post Comment Form -->
                                                                <form action="{{ route('projects.comments.store', $project) }}" method="POST" class="task-comment-ajax-form space-y-3" data-task-id="{{ $task->id }}" id="task-comment-form-{{ $task->id }}">
                                                                    @csrf
                                                                    <input type="hidden" name="project_task_id" value="{{ $task->id }}">
                                                                    <textarea id="task-comment-content-{{ $task->id }}" name="content" required rows="2" class="w-full text-xs border-gray-300 rounded shadow-sm transition-colors duration-200 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ask a question or request a revision..."></textarea>
                                                                    <div class="flex items-center justify-between">
                                                                        @if($project->estimates()->where('status', 'approved')->exists())
                                                                            <input type="hidden" name="type" value="comment">
                                                                        @else
                                                                            <div class="inline-flex rounded-lg p-0.5 bg-gray-200" id="type-pill-selector-task-{{ $task->id }}">
                                                                                <button type="button" onclick="setTaskCommentType({{ $task->id }}, 'comment')" id="type-btn-comment-task-{{ $task->id }}" class="px-2.5 py-1 rounded-md text-[10px] font-semibold transition-all duration-200 bg-white text-indigo-700 shadow-sm focus:outline-none">
                                                                                    Comment
                                                                                </button>
                                                                                <button type="button" onclick="setTaskCommentType({{ $task->id }}, 'revision_request')" id="type-btn-revision-task-{{ $task->id }}" class="px-2.5 py-1 rounded-md text-[10px] font-semibold transition-all duration-200 text-gray-600 hover:text-gray-900 focus:outline-none">
                                                                                    ⚠️ Request Revision
                                                                                </button>
                                                                                <input type="hidden" name="type" id="comment-type-input-task-{{ $task->id }}" value="comment">
                                                                            </div>
                                                                        @endif
                                                                        <x-primary-button class="py-1.5 px-4 text-xs font-semibold uppercase tracking-wider">Post Comment</x-primary-button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <!-- Task Edit Inline Form Row -->
                                                    @can('update', $project)
                                                    <tr id="task-row-{{ $task->id }}" class="hidden bg-gray-50">
                                                        <td colspan="7" class="px-6 py-4">
                                                            <form method="POST" action="{{ route('projects.tasks.update', [$project, $task]) }}" class="task-ajax-form grid grid-cols-1 md:grid-cols-5 gap-4 items-end" data-task-id="{{ $task->id }}">
                                                                @csrf
                                                                @method('PUT')
                                                                <div>
                                                                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Status</label>
                                                                    <select name="status" class="w-full border-gray-300 rounded shadow-sm text-sm">
                                                                        <option value="pending" @selected($task->status == 'pending')>Pending</option>
                                                                        <option value="in_progress" @selected($task->status == 'in_progress')>In Progress</option>
                                                                        <option value="completed" @selected($task->status == 'completed')>Completed</option>
                                                                    </select>
                                                                </div>
                                                                <div>
                                                                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Actual Cost Spent (PHP)</label>
                                                                    <input type="number" step="0.01" name="actual_cost" value="{{ $task->actual_cost }}" class="w-full border-gray-300 rounded shadow-sm text-sm font-mono" required />
                                                                </div>
                                                                <div>
                                                                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Start Date</label>
                                                                    <input type="date" name="start_date" value="{{ $task->start_date ? $task->start_date->format('Y-m-d') : '' }}" class="w-full border-gray-300 rounded shadow-sm text-sm" />
                                                                </div>
                                                                <div>
                                                                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">End Date / Completion Date</label>
                                                                    <input type="date" name="end_date" value="{{ $task->end_date ? $task->end_date->format('Y-m-d') : '' }}" class="w-full border-gray-300 rounded shadow-sm text-sm" />
                                                                </div>
                                                                <div>
                                                                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Target Date / Due Date</label>
                                                                    <input type="date" name="target_date" value="{{ $task->target_date ? $task->target_date->format('Y-m-d') : '' }}" class="w-full border-gray-300 rounded shadow-sm text-sm" />
                                                                </div>
                                                                <div class="md:col-span-4">
                                                                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Remarks / Delay Causes</label>
                                                                    <input type="text" name="remarks" value="{{ $task->remarks }}" class="w-full border-gray-300 rounded shadow-sm text-sm" placeholder="Reason for delays or modifications" />
                                                                </div>
                                                                <div class="flex justify-end gap-2 mt-2 md:col-span-1">
                                                                    <button type="button" onclick="toggleTaskEdit({{ $task->id }})" class="px-3 py-1.5 bg-gray-200 text-xs font-semibold rounded uppercase">Cancel</button>
                                                                    <x-primary-button class="py-1.5 px-3 text-xs font-semibold uppercase">Save</x-primary-button>
                                                                </div>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                    @endcan
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>

            <!-- Instant Estimate Generation -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between border-b pb-2 mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Direct Cost Estimates & Proposals</h3>
                        @can('update', $project)
                        @if($latestEstimate && !$latestEstimate->isEditable())
                            <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase bg-amber-100 text-amber-800">
                                Latest Estimate Locked
                            </span>
                        @else
                            <form method="POST" action="{{ route('projects.estimates.store', $project) }}">
                                @csrf
                                <x-primary-button :disabled="!$project->gross_floor_area">Generate Estimate Proposal</x-primary-button>
                            </form>
                        @endif
                        @endcan
                    </div>

                    @if($latestEstimate)
                        <div class="grid md:grid-cols-4 gap-4 text-sm mt-3 bg-gray-50 p-4 rounded border">
                            <div><span class="text-gray-500 block">Est. GFA:</span> <span class="font-bold text-gray-800">{{ number_format($latestEstimate->gross_floor_area, 2) }} sqm</span></div>
                            <div><span class="text-gray-500 block">Finish standard:</span> <span class="font-bold uppercase text-gray-800">{{ $latestEstimate->finish_level }}</span></div>
                            <div><span class="text-gray-500 block">Cost / sqm:</span> <span class="font-bold text-gray-800 font-mono">₱{{ number_format($latestEstimate->cost_per_sqm, 2) }}</span></div>
                            <div><span class="text-gray-500 block">Total Est. Cost:</span> <span class="font-bold text-indigo-700 font-mono text-base">₱{{ number_format($latestEstimate->total_cost, 2) }}</span></div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('projects.estimates.show', [$project, $latestEstimate]) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase hover:bg-gray-700 transition">
                                Open Budget Proposal
                            </a>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 py-2">No estimate has been generated yet for this project specs.</p>
                    @endif
                </div>
            </div>

            <!-- Estimate History -->
            @if($project->estimates->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold border-b pb-2 mb-3 text-gray-800">Proposal & Estimate History</h3>
                        <div class="space-y-2">
                            @foreach($project->estimates as $estimate)
                                <div class="flex items-center justify-between border rounded p-3 hover:bg-gray-50 transition">
                                    <div class="text-sm">
                                        <div class="font-semibold text-gray-800">Estimate proposal #{{ $estimate->id }}</div>
                                        <div class="text-xs text-gray-500">Generated: {{ optional($estimate->generated_at)->toDateTimeString() ?: $estimate->created_at }}</div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <span class="text-sm font-bold font-mono text-gray-800">₱{{ number_format($estimate->total_cost, 2) }}</span>
                                        <a href="{{ route('projects.estimates.show', [$project, $estimate]) }}" class="inline-flex items-center px-3 py-1 bg-indigo-50 text-indigo-600 rounded text-xs font-semibold uppercase hover:bg-indigo-100 transition">View</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- Switch Tabs Javascript -->
    <script>
        function setTaskCommentType(taskId, type) {
            const typeInput = document.getElementById('comment-type-input-task-' + taskId);
            const btnComment = document.getElementById('type-btn-comment-task-' + taskId);
            const btnRevision = document.getElementById('type-btn-revision-task-' + taskId);
            const textarea = document.getElementById('task-comment-content-' + taskId);
            
            if (!typeInput || !btnComment || !btnRevision || !textarea) return;
            
            typeInput.value = type;
            
            if (type === 'revision_request') {
                btnComment.className = "px-2.5 py-1 rounded-md text-[10px] font-semibold transition-all duration-200 text-gray-600 hover:text-gray-900 focus:outline-none";
                btnRevision.className = "px-2.5 py-1 rounded-md text-[10px] font-semibold transition-all duration-200 bg-amber-500 text-white shadow-sm focus:outline-none";
                
                textarea.className = "w-full text-xs border-amber-400 rounded shadow-sm transition-colors duration-200 focus:ring-amber-400 focus:border-amber-400";
            } else {
                btnComment.className = "px-2.5 py-1 rounded-md text-[10px] font-semibold transition-all duration-200 bg-white text-indigo-700 shadow-sm focus:outline-none";
                btnRevision.className = "px-2.5 py-1 rounded-md text-[10px] font-semibold transition-all duration-200 text-gray-600 hover:text-gray-900 focus:outline-none";
                
                textarea.className = "w-full text-xs border-gray-300 rounded shadow-sm transition-colors duration-200 focus:ring-indigo-500 focus:border-indigo-500";
            }
        }

        function switchTab(tabId) {
            const tabs = ['specs', 'compliance', 'workflow'];
            tabs.forEach(t => {
                const btn = document.getElementById('tab-' + t);
                const panel = document.getElementById('panel-' + t);
                if (t === tabId) {
                    btn.classList.add('border-indigo-500', 'text-indigo-600');
                    btn.classList.remove('border-transparent', 'text-gray-500');
                    panel.classList.remove('hidden');
                } else {
                    btn.classList.remove('border-indigo-500', 'text-indigo-600');
                    btn.classList.add('border-transparent', 'text-gray-500');
                    panel.classList.add('hidden');
                }
            });
            // Persist the active tab in localStorage
            localStorage.setItem('active_tab_project_' + {{ $project->id }}, tabId);
        }

        function toggleComplianceEdit(id) {
            const row = document.getElementById('compliance-row-' + id);
            row.classList.toggle('hidden');
        }

        function toggleTaskEdit(id) {
            const row = document.getElementById('task-row-' + id);
            row.classList.toggle('hidden');
        }

        function copyShareLink() {
            const input = document.getElementById('share-link-input');
            input.select();
            input.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(input.value);
            alert('Share link copied to clipboard!');
        }

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-4 right-4 z-50 px-4 py-2.5 rounded-lg text-white font-semibold text-sm shadow-lg transition-all duration-300 transform translate-y-10 opacity-0 ${
                type === 'success' ? 'bg-green-600' : 'bg-red-600'
            }`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            // Trigger transition
            setTimeout(() => {
                toast.classList.remove('translate-y-10', 'opacity-0');
            }, 10);

            // Dismiss
            setTimeout(() => {
                toast.classList.add('translate-y-10', 'opacity-0');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        }

        // Restore active tab on load and attach AJAX form handlers
        document.addEventListener('DOMContentLoaded', function() {
            const savedTab = localStorage.getItem('active_tab_project_' + {{ $project->id }});
            if (savedTab && ['specs', 'compliance', 'workflow'].includes(savedTab)) {
                switchTab(savedTab);
            }

            // AJAX handling for compliance forms
            document.querySelectorAll('.compliance-ajax-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const complianceId = form.dataset.complianceId;
                    const formData = new FormData(form);
                    
                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            const comp = data.compliance;
                            
                            // Update Status
                            const statusTd = document.getElementById('compliance-status-' + complianceId);
                            if (statusTd) {
                                let badgeHtml = '';
                                if (comp.status === 'approved') {
                                    badgeHtml = '<span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Approved</span>';
                                } else if (comp.status === 'pending') {
                                    badgeHtml = '<span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>';
                                } else if (comp.status === 'not_applicable') {
                                    badgeHtml = '<span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-500">N/A</span>';
                                } else {
                                    badgeHtml = '<span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800 font-semibold">Not Started</span>';
                                }
                                statusTd.innerHTML = badgeHtml;
                            }

                            // Update Fee
                            const feeTd = document.getElementById('compliance-fee-' + complianceId);
                            if (feeTd) {
                                feeTd.textContent = '₱' + Number(comp.fee).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            }

                            // Update Target Date
                            const targetTd = document.getElementById('compliance-target-' + complianceId);
                            if (targetTd) {
                                targetTd.textContent = comp.target_date || '-';
                            }

                            // Update Approved Date
                            const approvedTd = document.getElementById('compliance-approved-' + complianceId);
                            if (approvedTd) {
                                approvedTd.textContent = comp.approved_at || '-';
                            }

                            // Update Remarks
                            const remarksDiv = document.getElementById('compliance-remarks-' + complianceId);
                            if (remarksDiv) {
                                remarksDiv.textContent = comp.remarks || '';
                            }

                            toggleComplianceEdit(complianceId);
                            showToast(data.message || 'Updated successfully!');
                        } else {
                            showToast(data.message || 'An error occurred.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Failed to update compliance item.', 'error');
                    });
                });
            });

            // AJAX handling for task forms
            document.querySelectorAll('.task-ajax-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const taskId = form.dataset.taskId;
                    const formData = new FormData(form);

                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            const task = data.task;
                            const phase = data.phase;
                            const project = data.project;

                            // Update Task Status
                            const statusTd = document.getElementById('task-status-' + taskId);
                            if (statusTd) {
                                let statusHtml = '';
                                if (task.status === 'completed') {
                                    statusHtml = '<span class="text-green-600 font-semibold">✓ Completed</span>';
                                } else if (task.is_overdue) {
                                    statusHtml = '<span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">⚠️ Overdue</span>';
                                } else if (task.status === 'in_progress') {
                                    statusHtml = '<span class="text-blue-600 font-semibold">→ In Progress</span>';
                                } else {
                                    statusHtml = '<span class="text-gray-400">Pending</span>';
                                }
                                statusTd.innerHTML = statusHtml;
                            }

                            // Update Target Date
                            const targetTd = document.getElementById('task-target-' + taskId);
                            if (targetTd) {
                                targetTd.textContent = task.target_date || '-';
                            }

                            // Update Actual Cost
                            const actualCostTd = document.getElementById('task-actual-cost-' + taskId);
                            if (actualCostTd) {
                                actualCostTd.textContent = '₱' + Number(task.actual_cost).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                
                                const viewRow = document.getElementById('task-view-row-' + taskId);
                                const estCostTd = viewRow ? viewRow.querySelector('[data-estimated-cost]') : null;
                                if (estCostTd) {
                                    const estVal = parseFloat(estCostTd.getAttribute('data-estimated-cost'));
                                    const actVal = parseFloat(task.actual_cost);
                                    if (actVal > estVal) {
                                        actualCostTd.className = 'px-4 py-3 font-mono font-semibold text-red-600';
                                    } else {
                                        actualCostTd.className = 'px-4 py-3 font-mono font-semibold text-gray-800';
                                    }
                                }
                            }

                            // Update Dates
                            const datesTd = document.getElementById('task-dates-' + taskId);
                            if (datesTd) {
                                datesTd.innerHTML = `
                                    <div>S: ${task.start_date || '-'}</div>
                                    <div>E: ${task.end_date || '-'}</div>
                                `;
                            }

                            // Update Remarks
                            const remarksDiv = document.getElementById('task-remarks-' + taskId);
                            if (remarksDiv) {
                                remarksDiv.textContent = task.remarks || '';
                            }

                            // Update Phase Status Badge
                            const phaseStatusDiv = document.getElementById('phase-status-' + phase.id);
                            if (phaseStatusDiv) {
                                let phaseHtml = '';
                                if (phase.status === 'completed') {
                                    phaseHtml = '<span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-green-100 text-green-800 uppercase">Completed</span>';
                                } else if (phase.is_delayed) {
                                    phaseHtml = '<span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-red-100 text-red-800 uppercase animate-pulse">⚠️ Delayed</span>';
                                } else if (phase.status === 'in_progress') {
                                    phaseHtml = '<span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-blue-100 text-blue-800 uppercase">In Progress</span>';
                                } else {
                                    phaseHtml = '<span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-gray-200 text-gray-600 uppercase">Pending</span>';
                                }
                                phaseStatusDiv.innerHTML = phaseHtml;
                            }

                            // Update Overall Project Progress
                            const progBar = document.getElementById('project-progress-bar');
                            if (progBar) {
                                progBar.style.width = project.progress_percent + '%';
                            }
                            const progText = document.getElementById('project-progress-text');
                            if (progText) {
                                progText.textContent = project.progress_percent + '%';
                            }

                            // Update Budget Summary Cards
                            const actualTotalP = document.getElementById('project-actual-total');
                            if (actualTotalP) {
                                actualTotalP.textContent = '₱' + Number(project.actual_total).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            }
                            
                            const balanceContainer = document.getElementById('project-balance-container');
                            const balanceSpan = document.getElementById('project-balance');
                            const warningSpan = document.getElementById('project-over-budget-warning');
                            if (balanceSpan) {
                                balanceSpan.textContent = '₱' + Number(project.balance).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            }
                            if (balanceContainer) {
                                if (project.balance < 0) {
                                    balanceContainer.className = 'text-lg font-mono font-bold mt-1 text-red-600';
                                    if (warningSpan) warningSpan.classList.remove('hidden');
                                } else {
                                    balanceContainer.className = 'text-lg font-mono font-bold mt-1 text-green-600';
                                    if (warningSpan) warningSpan.classList.add('hidden');
                                }
                            }

                            toggleTaskEdit(taskId);
                            showToast(data.message || 'Updated successfully!');
                        } else {
                            showToast(data.message || 'An error occurred.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Failed to update task.', 'error');
                    });
                });
            });

            // AJAX handling for posting task comments
            document.querySelectorAll('.task-comment-ajax-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const taskId = form.dataset.taskId;
                    const formData = new FormData(form);
                    
                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            const comment = data.comment;
                            
                            const emptyP = document.getElementById(`task-comments-empty-${taskId}`);
                            if (emptyP) emptyP.remove();
                            
                            const deleteBtnHtml = `<button onclick="deleteComment(${comment.project_id}, ${comment.id}, 'task', ${taskId})" class="text-rose-600 hover:text-rose-900 font-semibold ml-2">Delete</button>`;
                            
                            const isRev = comment.type === 'revision_request';
                            const cardClass = isRev ? 'bg-amber-50/20 border-amber-200/60' : 'bg-gray-50/70 border-gray-200';
                            
                            const commentHtml = `
                                <div class="p-3 rounded-lg border shadow-sm ${cardClass}" id="comment-container-${comment.id}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-xs text-gray-800">${comment.author_name}</span>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold capitalize font-sans bg-indigo-100 text-indigo-800">
                                                ${comment.author_role}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs text-gray-400">
                                            <span>just now</span>
                                            ${deleteBtnHtml}
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-700 mt-1.5 whitespace-pre-line">${comment.content}</p>
                                    ${isRev ? `
                                        <div class="mt-2 flex items-center justify-between border-t border-amber-200/40 pt-2" id="comment-status-bar-${comment.id}">
                                            <span id="comment-badge-${comment.id}" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold font-sans bg-amber-100 text-amber-800 animate-pulse">
                                                Pending Revision
                                            </span>
                                            <button id="comment-resolve-btn-${comment.id}" onclick="resolveRevision(${comment.project_id}, ${comment.id}, 'task', ${taskId})" class="text-xs text-indigo-600 hover:text-indigo-900 font-bold underline font-sans">
                                                Mark Resolved
                                            </button>
                                        </div>
                                    ` : ''}
                                </div>
                            `;
                            
                            const commentsList = document.getElementById(`task-comments-list-${taskId}`);
                            if (commentsList) {
                                commentsList.insertAdjacentHTML('beforeend', commentHtml);
                                commentsList.scrollTop = commentsList.scrollHeight;
                            }
                            
                            form.reset();
                            setTaskCommentType(taskId, 'comment');
                            updateTaskCommentCount(taskId, 1);
                            updateTaskRevisionBadge(taskId);
                            showToast(data.message || 'Comment posted successfully!');
                        } else {
                            showToast(data.message || 'An error occurred.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Failed to post comment.', 'error');
                    });
                });
            });
        });

        function toggleTaskComments(id) {
            const row = document.getElementById('task-comments-row-' + id);
            if (row) row.classList.toggle('hidden');
        }

        function resolveRevision(projectId, commentId, context, contextId) {
            if (!confirm('Are you sure you want to mark this revision request as resolved?')) return;
            const url = `/projects/${projectId}/comments/${commentId}/resolve`;
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Resolved successfully.');
                    const badge = document.getElementById(`comment-badge-${commentId}`);
                    if (badge) {
                        badge.className = "inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-green-100 text-green-800";
                        badge.textContent = "Resolved";
                    }
                    const resolveBtn = document.getElementById(`comment-resolve-btn-${commentId}`);
                    if (resolveBtn) resolveBtn.remove();
                    
                    if (context === 'task') {
                        updateTaskRevisionBadge(contextId);
                    } else if (context === 'estimate') {
                        updateEstimateRevisionBadge();
                    }
                } else {
                    showToast(data.message || 'An error occurred.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to resolve request.', 'error');
            });
        }

        function deleteComment(projectId, commentId, context, contextId) {
            if (!confirm('Are you sure you want to delete this comment?')) return;
            const url = `/projects/${projectId}/comments/${commentId}`;
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Deleted successfully.');
                    const container = document.getElementById(`comment-container-${commentId}`);
                    if (container) container.remove();
                    
                    if (context === 'task') {
                        updateTaskCommentCount(contextId, -1);
                        updateTaskRevisionBadge(contextId);
                    } else if (context === 'estimate') {
                        updateEstimateCommentCount(-1);
                        updateEstimateRevisionBadge();
                    }
                } else {
                    showToast(data.message || 'An error occurred.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to delete comment.', 'error');
            });
        }

        function updateTaskCommentCount(taskId, offset) {
            const el = document.getElementById(`task-comment-count-${taskId}`);
            if (el) {
                const current = parseInt(el.textContent) || 0;
                el.textContent = Math.max(0, current + offset);
            }
        }

        function updateTaskRevisionBadge(taskId) {
            const container = document.getElementById(`task-comments-list-${taskId}`);
            const badge = document.getElementById(`task-pending-revision-badge-${taskId}`);
            if (!badge) return;
            
            const pendingCount = container ? container.querySelectorAll('.animate-pulse').length : 0;
            if (pendingCount > 0) {
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        function copySharePin(pin) {
            navigator.clipboard.writeText(pin);
            alert('Access PIN copied to clipboard!');
        }
    </script>
</x-app-layout>
