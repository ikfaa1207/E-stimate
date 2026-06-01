<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cost Items</h2>
            <a href="{{ route('items.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-xs font-semibold text-white uppercase">
                New Item
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Search and Filters -->
                    <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <form method="GET" action="{{ route('items.index') }}" class="grid md:grid-cols-3 gap-4 items-end">
                            <div>
                                <x-input-label for="search" value="Search by Name" />
                                <x-text-input id="search" name="search" class="mt-1 block w-full bg-white" :value="$filters['search'] ?? ''" placeholder="Enter item name..." />
                            </div>

                            <div>
                                <x-input-label for="type" value="Filter by Type" />
                                <select id="type" name="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-white">
                                    <option value="">All Types</option>
                                    @foreach(\App\Models\Item::TYPES as $type)
                                        <option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>
                                            {{ strtoupper($type) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex gap-2">
                                <x-primary-button class="justify-center w-full">Filter</x-primary-button>
                                <a href="{{ route('items.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 w-full text-center">
                                    Reset
                                </a>
                            </div>
                        </form>
                    </div>

                    @if(auth()->user()->isAdmin())
                        <!-- Bulk Cost Adjustment Panel -->
                        <div x-data="{ open: false }" class="mb-6 border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm">
                            <button @click="open = !open" type="button" class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 transition text-sm font-semibold text-gray-700">
                                <span>Bulk Cost Adjustment (Admin Only)</span>
                                <svg class="h-5 w-5 transform transition" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" style="display: none;" class="p-4 border-t border-gray-200 bg-gray-50/50">
                                <form method="POST" action="{{ route('items.bulk-update') }}" onsubmit="return confirm('Are you sure you want to adjust all matching item costs? This cannot be undone.')" class="grid md:grid-cols-4 gap-4 items-end">
                                    @csrf
                                    <div>
                                        <x-input-label for="bulk_type" value="Target Item Type" />
                                        <select id="bulk_type" name="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-white">
                                            <option value="">All Types</option>
                                            @foreach(\App\Models\Item::TYPES as $type)
                                                <option value="{{ $type }}">{{ strtoupper($type) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label for="adjustment_type" value="Adjustment Type" />
                                        <select id="adjustment_type" name="adjustment_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-white" required>
                                            <option value="percentage">Percentage (%)</option>
                                            <option value="fixed">Fixed Amount ($)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label for="direction" value="Action" />
                                        <select id="direction" name="direction" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-white" required>
                                            <option value="increase">Increase By</option>
                                            <option value="decrease">Decrease By</option>
                                        </select>
                                    </div>
                                    <div class="flex gap-2">
                                        <div class="w-2/3">
                                            <x-input-label for="amount" value="Amount" />
                                            <x-text-input id="amount" name="amount" type="number" step="0.01" min="0" class="mt-1 block w-full bg-white" placeholder="0.00" required />
                                        </div>
                                        <div class="w-1/3">
                                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150 h-[38px] mt-6">
                                                Apply
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Name</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Type</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Unit</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Unit Cost</th>
                                    <th class="px-4 py-3 text-right text-xs uppercase text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($items as $item)
                                    <tr>
                                        <td class="px-4 py-3">{{ $item->name }}</td>
                                        <td class="px-4 py-3 uppercase">{{ $item->type }}</td>
                                        <td class="px-4 py-3">{{ $item->unit }}</td>
                                        <td class="px-4 py-3">{{ number_format((float) $item->unit_cost, 2) }}</td>
                                        <td class="px-4 py-3 text-right space-x-2">
                                            <a href="{{ route('items.edit', $item) }}" class="text-indigo-600">Edit</a>
                                            <form method="POST" action="{{ route('items.destroy', $item) }}" class="inline" onsubmit="return confirm('Delete this item?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">No items configured.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $items->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
