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
    </script>
</x-app-layout>
