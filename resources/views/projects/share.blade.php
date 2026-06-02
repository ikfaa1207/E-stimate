<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $project->name }} - Client Portal</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
        @endif
    </head>
    <body class="font-sans antialiased text-gray-900 bg-gray-100">
        <div class="min-h-screen">
            
            <!-- Shared Portal Header Navigation -->
            <nav class="bg-indigo-700 text-white shadow-md">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                    <span class="font-bold text-lg tracking-wide">E-stimate Client Portal</span>
                    <span class="text-xs bg-indigo-600 px-3 py-1 rounded-full border border-indigo-500 font-semibold uppercase tracking-wider">Secure Guest Access</span>
                </div>
            </nav>

            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                            {{ $project->name }}
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">Client: <span class="font-bold text-gray-700">{{ $project->client_name }}</span></p>
                    </div>

                    <!-- Direct Budget Approval Widget for Guests -->
                    @if($latestEstimate)
                        <div class="flex items-center gap-2">
                            @if($latestEstimate->isApproved())
                                <span class="inline-flex items-center px-4 py-2 rounded-md text-xs font-bold uppercase bg-green-100 text-green-800 border border-green-200">
                                    ✓ Budget Proposal Approved
                                </span>
                            @elseif($latestEstimate->isLocked())
                                <form method="POST" action="{{ route('projects.share.approve', $project->share_token) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md text-xs font-bold uppercase tracking-wider shadow-sm transition">
                                        Approve Proposed Budget
                                    </button>
                                </form>
                            @elseif($latestEstimate->isRevisionPending())
                                <span class="inline-flex items-center px-4 py-2 rounded-md text-xs font-bold uppercase bg-yellow-100 text-yellow-800 border border-yellow-200 animate-pulse">
                                    ⚠️ Revision Request Submitted
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold uppercase bg-gray-100 text-gray-600">
                                    Estimate in Draft (Review Pending)
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </header>

            <main class="py-8">
                <!-- Status Notifications -->
                @if (session('status'))
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-6">
                        <div class="rounded-md bg-green-100 p-4 text-green-800 font-semibold shadow-sm border border-green-200">
                            {{ session('status') }}
                        </div>
                    </div>
                @endif

                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                    <!-- Overview Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                        <div class="bg-white p-6 shadow-sm rounded-lg border">
                            <p class="text-xs text-gray-500 uppercase font-semibold">Total Estimated Budget</p>
                            <p class="text-lg font-bold text-indigo-700 mt-1 font-mono">
                                @if($latestEstimate)
                                    ₱{{ number_format($latestEstimate->total_cost, 2) }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Tabbed Dashboard -->
                    <div class="bg-white shadow-sm rounded-lg border overflow-hidden">
                        <!-- Tab Headers -->
                        <div class="border-b border-gray-200 bg-gray-50 px-6">
                            <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
                                <button onclick="switchTab('specs')" id="tab-specs" class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm focus:outline-none transition">
                                    Building Specifications
                                </button>
                                <button onclick="switchTab('compliance')" id="tab-compliance" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm focus:outline-none transition">
                                    Permits & Compliance
                                </button>
                                <button onclick="switchTab('workflow')" id="tab-workflow" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm focus:outline-none transition">
                                    Workflow & Timeline
                                </button>
                                @if($latestEstimate)
                                    <button onclick="switchTab('estimate')" id="tab-estimate" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm focus:outline-none transition">
                                        Budget Proposal & BOQ
                                    </button>
                                @endif
                            </nav>
                        </div>

                        <!-- Tab Panels -->
                        <div class="p-6">
                            
                            <!-- Panel 1: Specs (Read-Only) -->
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
                                        <span class="text-gray-500 block">General Project Notes:</span>
                                        <p class="text-gray-800 mt-1 bg-gray-50 p-3 rounded border">{{ $project->notes ?: 'No notes recorded.' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Panel 2: Permits & Compliance (Read-Only) -->
                            <div id="panel-compliance" class="hidden space-y-4">
                                <div class="flex items-center justify-between border-b pb-2">
                                    <h3 class="text-lg font-semibold text-gray-800">Philippine Code & LGU Clearance Checklist</h3>
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
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200 text-sm">
                                            @forelse($project->compliances as $compliance)
                                                <tr>
                                                    <td class="px-4 py-4">
                                                        <div class="font-medium text-gray-900">{{ $compliance->name }}</div>
                                                        <div class="text-xs text-gray-500">{{ $compliance->remarks }}</div>
                                                    </td>
                                                    <td class="px-4 py-4 capitalize text-gray-500">{{ $compliance->type }}</td>
                                                    <td class="px-4 py-4">
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
                                                    <td class="px-4 py-4 font-mono text-gray-700">₱{{ number_format($compliance->fee, 2) }}</td>
                                                    <td class="px-4 py-4 text-gray-500">{{ $compliance->target_date ? $compliance->target_date->format('Y-m-d') : '-' }}</td>
                                                    <td class="px-4 py-4 text-gray-500">{{ $compliance->approved_at ? $compliance->approved_at->format('Y-m-d') : '-' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">No compliance clearances initialized.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Panel 3: Workflow Timeline (Read-Only) -->
                            <div id="panel-workflow" class="hidden space-y-4">
                                <div class="flex flex-col md:flex-row md:items-center justify-between border-b pb-2 gap-2">
                                    <h3 class="text-lg font-semibold text-gray-800">Construction Work Flow Phases</h3>
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
                                            <div class="bg-green-600 h-3 rounded-full" style="width: {{ $progressPercent }}%"></div>
                                        </div>
                                        <span class="text-sm font-bold text-gray-800">{{ $progressPercent }}%</span>
                                    </div>
                                </div>

                                <!-- Phase Lists -->
                                <div class="space-y-4 mt-4">
                                    @foreach($project->phases as $phase)
                                        <div class="border rounded-lg overflow-hidden shadow-sm">
                                            <!-- Phase Header -->
                                            <div class="bg-gray-100 px-4 py-3 flex items-center justify-between border-b">
                                                <div class="flex items-center gap-3">
                                                    <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-indigo-600 text-white text-xs font-bold">{{ $phase->sequence }}</span>
                                                    <h4 class="font-bold text-gray-800">{{ $phase->name }}</h4>
                                                </div>
                                                <div class="flex items-center gap-2">
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

                                            <!-- Tasks List -->
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-50">
                                                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                                                            <th class="px-4 py-2.5">Task Description</th>
                                                            <th class="px-4 py-2.5">Status</th>
                                                            <th class="px-4 py-2.5">Target Date</th>
                                                            <th class="px-4 py-2.5">Actual Dates</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-200 text-sm">
                                                        @foreach($phase->tasks as $task)
                                                             <tr class="hover:bg-gray-50">
                                                                 <td class="px-4 py-3">
                                                                     <div class="font-medium text-gray-800">{{ $task->name }}</div>
                                                                     @if($task->remarks)
                                                                         <div class="text-xs text-gray-500 italic mt-0.5">{{ $task->remarks }}</div>
                                                                     @endif
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
                                                                 <td class="px-4 py-3 capitalize">
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
                                                                 <td class="px-4 py-3 font-mono text-gray-600">
                                                                     {{ $task->target_date ? $task->target_date->format('Y-m-d') : '-' }}
                                                                 </td>
                                                                 <td class="px-4 py-3 text-xs text-gray-500">
                                                                     <div>Start: {{ $task->start_date ? $task->start_date->format('Y-m-d') : '-' }}</div>
                                                                     <div>End: {{ $task->end_date ? $task->end_date->format('Y-m-d') : '-' }}</div>
                                                                 </td>
                                                             </tr>

                                                             <!-- Task Comments expandable row -->
                                                             <tr id="task-comments-row-{{ $task->id }}" class="hidden bg-gray-50 border-b">
                                                                 <td colspan="4" class="px-6 py-4">
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
                                                                                         <div class="text-xs text-gray-400">
                                                                                             <span>{{ $comment->created_at->diffForHumans() }}</span>
                                                                                         </div>
                                                                                     </div>
                                                                                     <p class="text-sm text-gray-700 mt-1.5 whitespace-pre-line">{{ $comment->content }}</p>
                                                                                     
                                                                                     @if($comment->isRevisionRequest())
                                                                                         <div class="mt-2 flex items-center justify-between border-t border-amber-200/40 pt-2">
                                                                                             <span id="comment-badge-{{ $comment->id }}" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold font-sans {{ $comment->isResolved() ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800 animate-pulse' }}">
                                                                                                 {{ $comment->isResolved() ? 'Resolved' : 'Pending Revision' }}
                                                                                             </span>
                                                                                         </div>
                                                                                     @endif
                                                                                 </div>
                                                                             @empty
                                                                                 <p class="text-xs text-gray-500 italic py-2" id="task-comments-empty-{{ $task->id }}">No discussion for this task yet.</p>
                                                                             @endforelse
                                                                         </div>
                                                                         
                                                                         <!-- Post Comment Form -->
                                                                         <form action="{{ route('projects.share.comments.store', $project->share_token) }}" method="POST" class="task-comment-ajax-form space-y-3" data-task-id="{{ $task->id }}" id="task-comment-form-{{ $task->id }}">
                                                                             @csrf
                                                                             <input type="hidden" name="project_task_id" value="{{ $task->id }}">
                                                                             <div class="flex gap-2">
                                                                                 <input type="text" name="author_name" placeholder="Your Name (Optional)" class="w-1/3 text-xs border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                                                                                 <textarea id="task-comment-content-{{ $task->id }}" name="content" required rows="2" class="w-2/3 text-xs border-gray-300 rounded shadow-sm transition-colors duration-200 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ask a question or request a revision..."></textarea>
                                                                             </div>
                                                                             <div class="flex items-center justify-between">
                                                                                 <div class="inline-flex rounded-lg p-0.5 bg-gray-200" id="type-pill-selector-task-{{ $task->id }}">
                                                                                     <button type="button" onclick="setTaskCommentType({{ $task->id }}, 'comment')" id="type-btn-comment-task-{{ $task->id }}" class="px-2.5 py-1 rounded-md text-[10px] font-semibold transition-all duration-200 bg-white text-indigo-700 shadow-sm focus:outline-none">
                                                                                         Comment
                                                                                     </button>
                                                                                     <button type="button" onclick="setTaskCommentType({{ $task->id }}, 'revision_request')" id="type-btn-revision-task-{{ $task->id }}" class="px-2.5 py-1 rounded-md text-[10px] font-semibold transition-all duration-200 text-gray-600 hover:text-gray-900 focus:outline-none">
                                                                                         ⚠️ Request Revision
                                                                                     </button>
                                                                                     <input type="hidden" name="type" id="comment-type-input-task-{{ $task->id }}" value="comment">
                                                                                 </div>
                                                                                 <button type="submit" class="inline-flex items-center px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-[10px] font-semibold uppercase tracking-wider transition">Post Comment</button>
                                                                             </div>
                                                                         </form>
                                                                     </div>
                                                                 </td>
                                                             </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Panel 4: Estimate Breakdown & BOQ (Read-Only) -->
                            @if($latestEstimate)
                                <div id="panel-estimate" class="hidden space-y-6">
                                    <div class="flex items-center justify-between border-b pb-2">
                                        <h3 class="text-lg font-semibold text-gray-800">Construction Budget Proposal</h3>
                                        <span class="text-xs text-gray-500">Proposal ID: #{{ $latestEstimate->id }}</span>
                                    </div>

                                    <!-- Metrics & Dimensions -->
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded border">
                                        <div>
                                            <span class="text-xs text-gray-500 uppercase font-semibold">Wall Surface Area</span>
                                            <p class="text-base font-bold text-gray-800 mt-0.5">{{ number_format($latestEstimate->wall_area, 2) }} sqm</p>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500 uppercase font-semibold">Roof Surface Area</span>
                                            <p class="text-base font-bold text-gray-800 mt-0.5">{{ number_format($latestEstimate->roof_area, 2) }} sqm</p>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500 uppercase font-semibold">Foundation Slab Area</span>
                                            <p class="text-base font-bold text-gray-800 mt-0.5">{{ number_format($latestEstimate->slab_area, 2) }} sqm</p>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500 uppercase font-semibold">Average Unit Cost</span>
                                            <p class="text-base font-bold text-gray-800 mt-0.5 font-mono">₱{{ number_format($latestEstimate->cost_per_sqm, 2) }} / sqm</p>
                                        </div>
                                    </div>

                                    <!-- Breakdown by Type -->
                                    <div class="space-y-3">
                                        <h4 class="font-semibold text-gray-700 text-sm">Budget Breakdown by Class</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            @foreach($latestEstimate->breakdowns as $breakdown)
                                                <div class="border rounded p-4 bg-white shadow-sm">
                                                    <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider">{{ $breakdown->type }}</p>
                                                    <p class="text-lg font-bold text-gray-800 mt-1 font-mono">₱{{ number_format($breakdown->amount, 2) }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- BOQ Lines -->
                                    <div class="space-y-3">
                                        <h4 class="font-semibold text-gray-700 text-sm">Bill of Quantities (BOQ)</h4>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                                                        <th class="px-4 py-3">Structure / Assembly Item</th>
                                                        <th class="px-4 py-3">Metric Type</th>
                                                        <th class="px-4 py-3">Quantity</th>
                                                        <th class="px-4 py-3">Unit</th>
                                                        <th class="px-4 py-3">Unit Cost</th>
                                                        <th class="px-4 py-3">Total Cost</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200 text-sm bg-white">
                                                    @foreach($latestEstimate->lines as $line)
                                                        <tr>
                                                            <td class="px-4 py-3 font-medium text-gray-900">{{ $line->item_name }}</td>
                                                            <td class="px-4 py-3 text-gray-500">{{ $line->metric_name }}</td>
                                                            <td class="px-4 py-3">{{ number_format($line->quantity, 2) }}</td>
                                                            <td class="px-4 py-3">{{ $line->unit }}</td>
                                                            <td class="px-4 py-3 font-mono">₱{{ number_format($line->unit_cost, 2) }}</td>
                                                            <td class="px-4 py-3 font-mono font-semibold text-gray-800">₱{{ number_format($line->line_total, 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                              <!-- Code Adjustments & Compliance Items -->
                                    <div class="space-y-3">
                                        <h4 class="font-semibold text-gray-700 text-sm">Embedded Standards & Compliance Adjustments</h4>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                                                        <th class="px-4 py-3">Compliance/Adjustment Details</th>
                                                        <th class="px-4 py-3">Class</th>
                                                        <th class="px-4 py-3">Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200 text-sm bg-white">
                                                    @foreach($latestEstimate->adjustments as $adjustment)
                                                        <tr>
                                                            <td class="px-4 py-3 font-medium text-gray-900">{{ $adjustment->name }}</td>
                                                            <td class="px-4 py-3 uppercase text-xs font-semibold text-gray-500">{{ $adjustment->type }}</td>
                                                            <td class="px-4 py-3 font-mono font-semibold {{ $adjustment->amount < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                                                {{ $adjustment->amount < 0 ? '-' : '+' }}₱{{ number_format(abs($adjustment->amount), 2) }}
                                                             </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Estimate Proposal Comments & Revision Requests -->
                                     <div class="bg-gray-50 p-6 rounded-lg border shadow-sm space-y-6 mt-6">
                                          <h4 class="font-semibold text-gray-800 text-sm border-b pb-2">Proposal Discussion & Revision Requests</h4>
                                          
                                          <!-- Revision Checklist -->
                                          <div class="space-y-3 mb-6">
                                              <h5 class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                                                  <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                                  Revision Checklist
                                              </h5>
                                              <div class="space-y-3" id="estimate-revisions-list">
                                                  @php
                                                      $revisions = $latestEstimate->comments->where('type', 'revision_request');
                                                  @endphp
                                                  @forelse($revisions as $comment)
                                                      <div class="bg-white p-4 rounded border border-amber-100 shadow-xs transition hover:shadow" id="comment-container-{{ $comment->id }}">
                                                          <div class="flex items-center justify-between text-xs">
                                                              <div class="flex items-center gap-2">
                                                                  <span class="font-bold text-gray-800">{{ $comment->author_name }}</span>
                                                                  <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800 uppercase font-sans">
                                                                      Client Request
                                                                  </span>
                                                              </div>
                                                              <span class="text-gray-400 font-sans">{{ $comment->created_at->diffForHumans() }}</span>
                                                          </div>
                                                          <p class="text-sm text-gray-700 mt-2 whitespace-pre-line">{{ $comment->content }}</p>
                                                          
                                                          <div class="mt-2 pt-2 border-t flex items-center justify-between">
                                                              <span id="comment-badge-{{ $comment->id }}" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold font-sans {{ $comment->isResolved() ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800 animate-pulse' }}">
                                                                  {{ $comment->isResolved() ? 'Resolved' : 'Pending Revision' }}
                                                              </span>
                                                          </div>
                                                      </div>
                                                  @empty
                                                      <p class="text-xs text-gray-500 italic py-4 text-center border border-dashed rounded bg-white" id="estimate-revisions-empty">No revision requests submitted yet.</p>
                                                  @endforelse
                                              </div>
                                          </div>

                                          <!-- General Discussion -->
                                          <div class="space-y-3">
                                              <h5 class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                                                  <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                                  General Discussion
                                              </h5>
                                              <div class="space-y-4 max-h-80 overflow-y-auto pr-2" id="estimate-comments-list">
                                                  @php
                                                      $comments = $latestEstimate->comments->where('type', 'comment');
                                                  @endphp
                                                  @forelse($comments as $comment)
                                                      <div class="bg-white p-4 rounded border shadow-xs transition hover:shadow" id="comment-container-{{ $comment->id }}">
                                                          <div class="flex items-center justify-between text-xs">
                                                              <div class="flex items-center gap-2">
                                                                  <span class="font-bold text-gray-800">{{ $comment->author_name }}</span>
                                                                  <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold capitalize font-sans {{ in_array($comment->author_role, ['admin', 'estimator']) ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800' }}">
                                                                      {{ $comment->author_role }}
                                                                  </span>
                                                              </div>
                                                              <span class="text-gray-400 font-sans">{{ $comment->created_at->diffForHumans() }}</span>
                                                          </div>
                                                          <p class="text-sm text-gray-700 mt-2 whitespace-pre-line">{{ $comment->content }}</p>
                                                      </div>
                                                  @empty
                                                      <p class="text-xs text-gray-500 italic py-4 text-center border border-dashed rounded bg-white" id="estimate-comments-empty">No general comments posted yet.</p>
                                                  @endforelse
                                              </div>
                                          </div>

                                          <!-- Post Comment Form -->
                                          <form action="{{ route('projects.share.comments.store', $project->share_token) }}" method="POST" class="estimate-comment-ajax-form space-y-3 pt-2" id="estimate-comment-form">
                                              @csrf
                                              <input type="hidden" name="estimate_id" value="{{ $latestEstimate->id }}">
                                              <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                                  <div class="sm:col-span-1">
                                                      <input type="text" name="author_name" placeholder="Your Name" class="w-full text-xs border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                                                  </div>
                                                  <div class="sm:col-span-3">
                                                      <textarea id="estimate-comment-content" name="content" required rows="2" class="w-full text-xs border-gray-300 rounded shadow-sm transition-colors duration-200 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Type a message or request a revision..."></textarea>
                                                  </div>
                                              </div>
                                              <div class="flex flex-col gap-3">
                                                  <div class="flex items-center justify-between flex-wrap gap-2">
                                                      <div class="inline-flex rounded-lg p-0.5 bg-gray-200" id="type-pill-selector">
                                                          <button type="button" onclick="setCommentType('comment')" id="type-btn-comment" class="px-3.5 py-1.5 rounded-md text-xs font-semibold transition-all duration-200 bg-white text-indigo-700 shadow-sm focus:outline-none">
                                                              General Comment
                                                          </button>
                                                          <button type="button" onclick="setCommentType('revision_request')" id="type-btn-revision" class="px-3.5 py-1.5 rounded-md text-xs font-semibold transition-all duration-200 text-gray-600 hover:text-gray-900 focus:outline-none">
                                                              ⚠️ Request Revision
                                                          </button>
                                                          <input type="hidden" name="type" id="comment-type-input" value="comment">
                                                      </div>
                                                      <button type="submit" class="inline-flex items-center px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-xs font-semibold uppercase tracking-wider transition">Post Comment</button>
                                                  </div>
                                                  <!-- Helper warning message for revision request -->
                                                  <div id="revision-helper-msg" class="hidden text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded p-2.5 flex items-start gap-2">
                                                      <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                                      <span><strong>Important:</strong> Submitting a revision request will automatically set the proposal status to <em>Revision Pending</em>, enabling modifications and lock controls for the contractor.</span>
                                                  </div>
                                              </div>
                                      </form>                                            </div>
                                     </div>
                                </div>
                            @endif

                        </div>
                    </div>

                </div>
            </main>
        </div>

        <!-- Switch Tabs JavaScript -->
        <script>
            function switchTab(tabId) {
                const tabs = ['specs', 'compliance', 'workflow', 'estimate'];
                for (const t of tabs) {
                    const btn = document.getElementById('tab-' + t);
                    const panel = document.getElementById('panel-' + t);
                    if (!btn || !panel) continue;

                    if (t === tabId) {
                        btn.classList.add('border-indigo-500', 'text-indigo-600');
                        btn.classList.remove('border-transparent', 'text-gray-500');
                        panel.classList.remove('hidden');
                    } else {
                        btn.classList.remove('border-indigo-500', 'text-indigo-600');
                        btn.classList.add('border-transparent', 'text-gray-500');
                        panel.classList.add('hidden');
                    }
                }
            }

            function toggleTaskComments(id) {
                const row = document.getElementById('task-comments-row-' + id);
                if (row) row.classList.toggle('hidden');
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
                    
                    textarea.className = "w-2/3 text-xs border-amber-400 rounded shadow-sm transition-colors duration-200 focus:ring-amber-400 focus:border-amber-400";
                } else {
                    btnComment.className = "px-2.5 py-1 rounded-md text-[10px] font-semibold transition-all duration-200 bg-white text-indigo-700 shadow-sm focus:outline-none";
                    btnRevision.className = "px-2.5 py-1 rounded-md text-[10px] font-semibold transition-all duration-200 text-gray-600 hover:text-gray-900 focus:outline-none";
                    
                    textarea.className = "w-2/3 text-xs border-gray-300 rounded shadow-sm transition-colors duration-200 focus:ring-indigo-500 focus:border-indigo-500";
                }
            }

            function setCommentType(type) {
                const typeInput = document.getElementById('comment-type-input');
                const btnComment = document.getElementById('type-btn-comment');
                const btnRevision = document.getElementById('type-btn-revision');
                const textarea = document.getElementById('estimate-comment-content');
                const helperMsg = document.getElementById('revision-helper-msg');
                
                if (!typeInput || !btnComment || !btnRevision || !textarea) return;
                
                typeInput.value = type;
                
                if (type === 'revision_request') {
                    btnComment.className = "px-3.5 py-1.5 rounded-md text-xs font-semibold transition-all duration-200 text-gray-600 hover:text-gray-900 focus:outline-none";
                    btnRevision.className = "px-3.5 py-1.5 rounded-md text-xs font-semibold transition-all duration-200 bg-amber-500 text-white shadow-sm focus:outline-none";
                    
                    textarea.className = "w-full text-xs border-amber-400 rounded shadow-sm transition-colors duration-200 focus:ring-amber-400 focus:border-amber-400";
                    if (helperMsg) helperMsg.classList.remove('hidden');
                } else {
                    btnComment.className = "px-3.5 py-1.5 rounded-md text-xs font-semibold transition-all duration-200 bg-white text-indigo-700 shadow-sm focus:outline-none";
                    btnRevision.className = "px-3.5 py-1.5 rounded-md text-xs font-semibold transition-all duration-200 text-gray-600 hover:text-gray-900 focus:outline-none";
                    
                    textarea.className = "w-full text-xs border-gray-300 rounded shadow-sm transition-colors duration-200 focus:ring-indigo-500 focus:border-indigo-500";
                    if (helperMsg) helperMsg.classList.add('hidden');
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                // AJAX for task comments
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
                                
                                const isRev = comment.type === 'revision_request';
                                const cardClass = isRev ? 'bg-amber-50/20 border-amber-200/60' : 'bg-gray-50/70 border-gray-200';
                                const commentHtml = `
                                    <div class="p-3 rounded-lg border shadow-sm ${cardClass}" id="comment-container-${comment.id}">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-xs text-gray-800">${comment.author_name}</span>
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold capitalize font-sans bg-gray-100 text-gray-800">
                                                    guest
                                                </span>
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                <span>just now</span>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-700 mt-1.5 whitespace-pre-line">${comment.content}</p>
                                        ${isRev ? `
                                            <div class="mt-2 flex items-center justify-between border-t border-amber-200/40 pt-2">
                                                <span id="comment-badge-${comment.id}" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold font-sans bg-amber-100 text-amber-800 animate-pulse">
                                                    Pending Revision
                                                </span>
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

                // AJAX for estimate comments
                const estForm = document.getElementById('estimate-comment-form');
                if (estForm) {
                    estForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const formData = new FormData(estForm);
                        
                        fetch(estForm.action, {
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
                                
                                if (comment.type === 'revision_request') {
                                    const emptyRev = document.getElementById('estimate-revisions-empty');
                                    if (emptyRev) emptyRev.remove();
                                    
                                    const commentHtml = `
                                        <div class="bg-white p-4 rounded border border-amber-100 shadow-xs transition hover:shadow" id="comment-container-${comment.id}">
                                            <div class="flex items-center justify-between text-xs">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-gray-800">${comment.author_name}</span>
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800 uppercase font-sans">
                                                        Client Request
                                                    </span>
                                                </div>
                                                <span class="text-gray-400 font-sans">just now</span>
                                            </div>
                                            <p class="text-sm text-gray-700 mt-2 whitespace-pre-line">${comment.content}</p>
                                            <div class="mt-2 pt-2 border-t flex items-center justify-between">
                                                <span id="comment-badge-${comment.id}" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold font-sans bg-amber-100 text-amber-800 animate-pulse">
                                                    Pending Revision
                                                </span>
                                            </div>
                                        </div>
                                    `;
                                    const revList = document.getElementById('estimate-revisions-list');
                                    if (revList) {
                                        revList.insertAdjacentHTML('beforeend', commentHtml);
                                    }
                                } else {
                                    const emptyComm = document.getElementById('estimate-comments-empty');
                                    if (emptyComm) emptyComm.remove();
                                    
                                    const commentHtml = `
                                        <div class="bg-white p-4 rounded border shadow-xs transition hover:shadow" id="comment-container-${comment.id}">
                                             <div class="flex items-center justify-between text-xs">
                                                 <div class="flex items-center gap-2">
                                                     <span class="font-bold text-gray-800">${comment.author_name}</span>
                                                     <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold capitalize font-sans bg-gray-100 text-gray-800">
                                                         guest
                                                     </span>
                                                 </div>
                                                 <span class="text-gray-400 font-sans">just now</span>
                                             </div>
                                             <p class="text-sm text-gray-700 mt-2 whitespace-pre-line">${comment.content}</p>
                                        </div>
                                    `;
                                    
                                    const commentsList = document.getElementById('estimate-comments-list');
                                    if (commentsList) {
                                        commentsList.insertAdjacentHTML('beforeend', commentHtml);
                                        commentsList.scrollTop = commentsList.scrollHeight;
                                    }
                                }
                                
                                estForm.reset();
                                setCommentType('comment');
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
                }
            });
        </script>
    </body>
</html>
