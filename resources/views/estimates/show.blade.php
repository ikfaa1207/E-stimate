<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Estimate #{{ $estimate->id }} - {{ $project->name }}
            </h2>
            <div class="space-x-2">
                <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-xs font-semibold uppercase">
                    Back to Project
                </a>
                <a href="{{ route('projects.estimates.export', [$project, $estimate]) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-md text-xs font-semibold uppercase">
                    Export Excel
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 grid md:grid-cols-5 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Gross Floor Area</p>
                        <p class="text-xl font-semibold">{{ number_format((float) $estimate->gross_floor_area, 2) }} sqm</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Cost per sqm</p>
                        <p class="text-xl font-semibold">{{ number_format((float) $estimate->cost_per_sqm, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Construction Cost</p>
                        <p class="text-xl font-semibold">{{ number_format((float) $estimate->total_cost, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Finish Level</p>
                        <p class="text-xl font-semibold uppercase">{{ $estimate->finish_level }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <div class="flex flex-col gap-1.5 mt-1 items-start">
                            @if($estimate->isApproved())
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold uppercase bg-green-100 text-green-800">
                                    Approved by Client
                                </span>
                                @can('update', $project)
                                    <form method="POST" action="{{ route('projects.estimates.unlock', [$project, $estimate]) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-900 font-medium underline">Unlock</button>
                                    </form>
                                @endcan
                            @elseif($estimate->isLocked())
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold uppercase bg-amber-100 text-amber-800">
                                    Proposed/Locked
                                </span>
                                @can('update', $project)
                                    <form method="POST" action="{{ route('projects.estimates.unlock', [$project, $estimate]) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-900 font-medium underline">Unlock</button>
                                    </form>
                                @endcan
                                @if(auth()->user()->isClient() && (int) $project->client_id === (int) auth()->user()->id)
                                    <form method="POST" action="{{ route('projects.estimates.approve', [$project, $estimate]) }}" class="inline mt-1">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-semibold uppercase">
                                            Approve Proposed Estimate
                                        </button>
                                    </form>
                                @endif
                            @elseif($estimate->isRevisionPending())
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold uppercase bg-yellow-100 text-yellow-800 animate-pulse">
                                    ⚠️ Revision Requested
                                </span>
                                @can('update', $project)
                                    <form method="POST" action="{{ route('projects.estimates.lock', [$project, $estimate]) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs text-emerald-600 hover:text-emerald-950 font-medium underline">Lock & Propose (Repropose)</button>
                                    </form>
                                @endcan
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold uppercase bg-gray-100 text-gray-800">
                                    Draft
                                </span>
                                @can('update', $project)
                                    <form method="POST" action="{{ route('projects.estimates.lock', [$project, $estimate]) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs text-emerald-600 hover:text-emerald-950 font-medium underline">Lock & Propose</button>
                                    </form>
                                    @if(!$project->client_id)
                                        <p class="text-xs text-amber-600 mt-1 max-w-[200px]">Note: A client must be assigned to this project before they can approve the proposed estimate.</p>
                                    @endif
                                @endcan
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-3">Calculated Building Metrics</h3>
                    <div class="grid md:grid-cols-3 gap-4 text-sm">
                        <div><span class="text-gray-500">Wall Area:</span> {{ number_format((float) $estimate->wall_area, 2) }} sqm</div>
                        <div><span class="text-gray-500">Roof Area:</span> {{ number_format((float) $estimate->roof_area, 2) }} sqm</div>
                        <div><span class="text-gray-500">Slab Area:</span> {{ number_format((float) $estimate->slab_area, 2) }} sqm</div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-3">Breakdown by Type</h3>
                    <div class="grid md:grid-cols-3 gap-4">
                        @foreach($estimate->breakdowns as $breakdown)
                            <div class="border rounded p-4">
                                <p class="text-sm text-gray-500 uppercase">{{ $breakdown->type }}</p>
                                <p class="text-xl font-semibold">{{ number_format((float) $breakdown->amount, 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-3">BOQ</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Item</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Metric</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Quantity</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Unit</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Unit Cost</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Total Cost</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($estimate->lines as $line)
                                    <tr>
                                        <td class="px-4 py-3">{{ $line->item_name }}</td>
                                        <td class="px-4 py-3">{{ $line->metric_name }}</td>
                                        <td class="px-4 py-3">{{ number_format((float) $line->quantity, 4) }}</td>
                                        <td class="px-4 py-3">{{ $line->unit }}</td>
                                        <td class="px-4 py-3">{{ number_format((float) $line->unit_cost, 2) }}</td>
                                        <td class="px-4 py-3">{{ number_format((float) $line->line_total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">No BOQ lines generated.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Custom Adjustments Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-lg font-semibold">Custom Adjustments</h3>
                        <p class="text-sm text-gray-500">Add or modify adjustments (discounts, structural add-ons, etc.)</p>
                    </div>

                    @if(session('status'))
                        <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-md text-sm">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto mb-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Name / Description</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Category</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Amount</th>
                                    @can('update', $project)
                                    <th class="px-4 py-3 text-right text-xs uppercase text-gray-500">Actions</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($estimate->adjustments as $adjustment)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $adjustment->name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500 uppercase text-xs font-semibold">{{ $adjustment->type }}</td>
                                        <td class="px-4 py-3 text-sm font-semibold {{ $adjustment->amount < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                            {{ $adjustment->amount < 0 ? '-' : '+' }}{{ number_format(abs((float) $adjustment->amount), 2) }}
                                        </td>
                                        @can('update', $project)
                                        <td class="px-4 py-3 text-sm text-right space-x-2">
                                            @if($estimate->isEditable())
                                                <button 
                                                    type="button" 
                                                    onclick="editAdjustment({{ $adjustment->id }}, '{{ addslashes($adjustment->name) }}', '{{ $adjustment->type }}', {{ (float) $adjustment->amount }}, '{{ route('projects.estimates.adjustments.update', [$project, $estimate, $adjustment]) }}')" 
                                                    class="text-indigo-600 hover:text-indigo-900 font-medium"
                                                >
                                                    Edit
                                                </button>
                                                <form action="{{ route('projects.estimates.adjustments.destroy', [$project, $estimate, $adjustment]) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this adjustment?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-rose-600 hover:text-rose-900 font-medium">Delete</button>
                                                </form>
                                            @else
                                                <span class="text-gray-400 text-xs italic">Locked</span>
                                            @endif
                                        </td>
                                        @endcan
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ Auth::user()->isClient() ? 3 : 4 }}" class="px-4 py-6 text-center text-gray-500">No custom adjustments added to this estimate yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Form Section -->
                    @can('update', $project)
                    <div id="adjustment-form-section" class="border-t pt-6">
                        @if($estimate->isEditable())
                            <h4 id="adjustment-form-title" class="text-md font-semibold mb-3">Add Custom Adjustment</h4>
                            
                            <form id="adjustment-form" action="{{ route('projects.estimates.adjustments.store', [$project, $estimate]) }}" method="POST" class="grid md:grid-cols-4 gap-4 items-end">
                                @csrf
                                <div>
                                    <x-input-label for="adjustment_name" value="Name/Description" />
                                    <x-text-input id="adjustment_name" name="name" type="text" placeholder="e.g. Premium foundation upgrade" class="mt-1 block w-full text-sm" required />
                                </div>
                                <div>
                                    <x-input-label for="adjustment_type" value="Breakdown Category" />
                                    <select id="adjustment_type" name="type" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                        <option value="material">Material</option>
                                        <option value="labor">Labor</option>
                                        <option value="equipment">Equipment</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="adjustment_amount" value="Amount (negative for discounts)" />
                                    <x-text-input id="adjustment_amount" name="amount" type="number" step="0.01" placeholder="e.g. 2500 or -500" class="mt-1 block w-full text-sm" required />
                                </div>
                                <div class="flex gap-2">
                                    <x-primary-button id="adjustment-submit-btn" class="flex-1 justify-center py-2">Add Adjustment</x-primary-button>
                                    <button 
                                        id="adjustment-cancel-btn" 
                                        type="button" 
                                        onclick="cancelEdit('{{ route('projects.estimates.adjustments.store', [$project, $estimate]) }}')" 
                                        class="hidden inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        @else
                            <p class="text-sm text-gray-500 bg-gray-50 p-4 rounded border text-center italic">
                                This estimate has been locked/approved. Unlocking is required before modifying custom adjustments.
                            </p>
                        @endif
                    </div>
                    @endcan
                </div>
            <!-- Estimate Proposal Comments & Revision Requests -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <h3 class="text-lg font-semibold border-b pb-2 text-gray-800">Proposal Discussion & Revision Requests</h3>
                    
                    <!-- Revision Checklist -->
                    <div class="space-y-3 mb-6">
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                            Revision Checklist
                        </h4>
                        <div class="space-y-3" id="estimate-revisions-list">
                            @php
                                $revisions = $estimate->comments->where('type', 'revision_request');
                            @endphp
                            @forelse($revisions as $comment)
                                <div class="bg-amber-50/30 p-4 rounded-lg border border-amber-100 shadow-sm transition hover:shadow-md" id="comment-container-{{ $comment->id }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-sm text-gray-800">{{ $comment->author_name }}</span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800 uppercase font-sans">
                                                Client Request
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs text-gray-400">
                                            <span>{{ $comment->created_at->diffForHumans() }}</span>
                                            @can('update', $project)
                                                <button onclick="deleteComment({{ $project->id }}, {{ $comment->id }}, 'estimate', {{ $estimate->id }})" class="text-rose-600 hover:text-rose-900 font-semibold ml-2">Delete</button>
                                            @endcan
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-700 mt-2 whitespace-pre-line">{{ $comment->content }}</p>
                                    
                                    <div class="mt-3 flex items-center justify-between border-t border-amber-100/50 pt-2" id="comment-status-bar-{{ $comment->id }}">
                                        <span id="comment-badge-{{ $comment->id }}" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $comment->isResolved() ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800 animate-pulse font-sans' }}">
                                            {{ $comment->isResolved() ? 'Resolved' : 'Pending Revision' }}
                                        </span>
                                        @if($comment->isPending())
                                            @can('update', $project)
                                                <button id="comment-resolve-btn-{{ $comment->id }}" onclick="resolveRevision({{ $project->id }}, {{ $comment->id }}, 'estimate', {{ $estimate->id }})" class="text-xs text-indigo-600 hover:text-indigo-900 font-bold underline">
                                                    Mark Resolved
                                                </button>
                                            @endcan
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-gray-500 italic py-4 text-center border border-dashed rounded bg-gray-50/50" id="estimate-revisions-empty">No revision requests submitted yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- General Discussion -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                            General Discussion
                        </h4>
                        <div class="space-y-3 max-h-80 overflow-y-auto pr-2" id="estimate-comments-list">
                            @php
                                $comments = $estimate->comments->where('type', 'comment');
                            @endphp
                            @forelse($comments as $comment)
                                <div class="bg-gray-50/50 p-4 rounded-lg border shadow-sm transition hover:shadow-md" id="comment-container-{{ $comment->id }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-sm text-gray-800">{{ $comment->author_name }}</span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold capitalize font-sans {{ in_array($comment->author_role, ['admin', 'estimator']) ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $comment->author_role }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs text-gray-400">
                                            <span>{{ $comment->created_at->diffForHumans() }}</span>
                                            @can('update', $project)
                                                <button onclick="deleteComment({{ $project->id }}, {{ $comment->id }}, 'estimate', {{ $estimate->id }})" class="text-rose-600 hover:text-rose-900 font-semibold ml-2">Delete</button>
                                            @endcan
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-700 mt-2 whitespace-pre-line">{{ $comment->content }}</p>
                                </div>
                            @empty
                                <p class="text-xs text-gray-500 italic py-4 text-center border border-dashed rounded bg-gray-50/50" id="estimate-comments-empty">No general comments posted yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Post Comment Form -->
                    <form action="{{ route('projects.comments.store', $project) }}" method="POST" class="estimate-comment-ajax-form space-y-3 pt-2" id="estimate-comment-form">
                        @csrf
                        <input type="hidden" name="estimate_id" value="{{ $estimate->id }}">
                        <div>
                            <textarea id="estimate-comment-content" name="content" required rows="3" class="w-full text-sm border-gray-300 rounded-md shadow-sm transition-colors duration-200 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Post a comment or details about required revisions..."></textarea>
                        </div>
                        
                        <div class="flex flex-col gap-3">
                            <!-- Type Pill Selector -->
                            <div class="flex justify-between items-center flex-wrap gap-2">
                                @if($estimate->isApproved())
                                    <input type="hidden" name="type" value="comment">
                                @else
                                    <div class="inline-flex rounded-lg p-0.5 bg-gray-200" id="type-pill-selector">
                                        <button type="button" onclick="setCommentType('comment')" id="type-btn-comment" class="px-3.5 py-1.5 rounded-md text-xs font-semibold transition-all duration-200 bg-white text-indigo-700 shadow-sm focus:outline-none">
                                            General Comment
                                        </button>
                                        <button type="button" onclick="setCommentType('revision_request')" id="type-btn-revision" class="px-3.5 py-1.5 rounded-md text-xs font-semibold transition-all duration-200 text-gray-600 hover:text-gray-900 focus:outline-none">
                                            ⚠️ Request Revision
                                        </button>
                                        <input type="hidden" name="type" id="comment-type-input" value="comment">
                                    </div>
                                @endif
                                <x-primary-button id="submit-button">Post to Discussion</x-primary-button>
                            </div>
                            @if(!$estimate->isApproved())
                                <!-- Helper warning message for revision request -->
                                <div id="revision-helper-msg" class="hidden text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded p-2.5 flex items-start gap-2">
                                    <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                    <span><strong>Important:</strong> Submitting a revision request will automatically set the proposal status to <em>Revision Pending</em>, enabling modifications and lock controls for the contractor.</span>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function editAdjustment(id, name, type, amount, updateUrl) {
            document.getElementById('adjustment-form-title').innerText = 'Edit Custom Adjustment';
            document.getElementById('adjustment_name').value = name;
            document.getElementById('adjustment_type').value = type;
            document.getElementById('adjustment_amount').value = amount;
            
            const form = document.getElementById('adjustment-form');
            form.action = updateUrl;
            
            let methodInput = document.getElementById('adjustment-method');
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.id = 'adjustment-method';
                methodInput.value = 'PUT';
                form.appendChild(methodInput);
            } else {
                methodInput.value = 'PUT';
            }
            
            document.getElementById('adjustment-submit-btn').innerText = 'Save Changes';
            document.getElementById('adjustment-cancel-btn').classList.remove('hidden');
            
            document.getElementById('adjustment-form-section').scrollIntoView({ behavior: 'smooth' });
        }

        function cancelEdit(storeUrl) {
            document.getElementById('adjustment-form-title').innerText = 'Add Custom Adjustment';
            document.getElementById('adjustment_name').value = '';
            document.getElementById('adjustment_type').value = 'material';
            document.getElementById('adjustment_amount').value = '';
            
            const form = document.getElementById('adjustment-form');
            form.action = storeUrl;
            
            const methodInput = document.getElementById('adjustment-method');
            if (methodInput) {
                methodInput.remove();
            }
            
            document.getElementById('adjustment-submit-btn').innerText = 'Add Adjustment';
            document.getElementById('adjustment-cancel-btn').classList.add('hidden');
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
                
                textarea.className = "w-full text-sm border-amber-400 rounded-md shadow-sm transition-colors duration-200 focus:ring-amber-400 focus:border-amber-400";
                if (helperMsg) helperMsg.classList.remove('hidden');
            } else {
                btnComment.className = "px-3.5 py-1.5 rounded-md text-xs font-semibold transition-all duration-200 bg-white text-indigo-700 shadow-sm focus:outline-none";
                btnRevision.className = "px-3.5 py-1.5 rounded-md text-xs font-semibold transition-all duration-200 text-gray-600 hover:text-gray-900 focus:outline-none";
                
                textarea.className = "w-full text-sm border-gray-300 rounded-md shadow-sm transition-colors duration-200 focus:ring-indigo-500 focus:border-indigo-500";
                if (helperMsg) helperMsg.classList.add('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('estimate-comment-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
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
                            
                            const deleteBtnHtml = `<button onclick="deleteComment(${comment.project_id}, ${comment.id}, 'estimate', ${comment.estimate_id})" class="text-rose-600 hover:text-rose-900 font-semibold ml-2">Delete</button>`;
                            
                            if (comment.type === 'revision_request') {
                                const emptyRev = document.getElementById('estimate-revisions-empty');
                                if (emptyRev) emptyRev.remove();
                                
                                const commentHtml = `
                                    <div class="bg-amber-50/30 p-4 rounded-lg border border-amber-100 shadow-sm transition hover:shadow-md" id="comment-container-${comment.id}">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-sm text-gray-800">${comment.author_name}</span>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800 uppercase font-sans">
                                                    Client Request
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2 text-xs text-gray-400">
                                                <span>just now</span>
                                                ${deleteBtnHtml}
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-700 mt-2 whitespace-pre-line">${comment.content}</p>
                                        <div class="mt-3 flex items-center justify-between border-t border-amber-100/50 pt-2" id="comment-status-bar-${comment.id}">
                                            <span id="comment-badge-${comment.id}" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800 animate-pulse font-sans">
                                                Pending Revision
                                            </span>
                                            <button id="comment-resolve-btn-${comment.id}" onclick="resolveRevision(${comment.project_id}, ${comment.id}, 'estimate', ${comment.estimate_id})" class="text-xs text-indigo-600 hover:text-indigo-900 font-bold underline">
                                                Mark Resolved
                                            </button>
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
                                    <div class="bg-gray-50/50 p-4 rounded-lg border shadow-sm transition hover:shadow-md" id="comment-container-${comment.id}">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-sm text-gray-800">${comment.author_name}</span>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold capitalize font-sans bg-indigo-100 text-indigo-800">
                                                    ${comment.author_role}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2 text-xs text-gray-400">
                                                <span>just now</span>
                                                ${deleteBtnHtml}
                                            </div>
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
                            
                            form.reset();
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
                        badge.className = "inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800";
                        badge.textContent = "Resolved";
                    }
                    const resolveBtn = document.getElementById(`comment-resolve-btn-${commentId}`);
                    if (resolveBtn) resolveBtn.remove();
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
                } else {
                    showToast(data.message || 'An error occurred.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to delete comment.', 'error');
            });
        }
    </script>
</x-app-layout>
