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
                <div class="p-6 text-gray-900 grid md:grid-cols-4 gap-4">
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
        </div>
    </div>
</x-app-layout>
