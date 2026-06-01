<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $project->name }}
            </h2>
            @can('update', $project)
            <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-xs font-semibold uppercase">
                Edit Project
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 grid md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Client</p>
                        <p class="font-medium">{{ $project->client_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Lot Area</p>
                        <p class="font-medium">{{ $project->lot_area ? number_format((float) $project->lot_area, 2).' sqm' : '-' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500">Notes</p>
                        <p class="font-medium">{{ $project->notes ?: '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Requirement Wizard</h3>
                        @can('update', $project)
                        <a href="{{ route('projects.requirements.edit', $project) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md text-xs font-semibold uppercase">
                            {{ $project->requirement ? 'Edit Requirements' : 'Start Wizard' }}
                        </a>
                        @endcan
                    </div>

                    @if($project->requirement)
                        <div class="mt-4 grid md:grid-cols-3 gap-4 text-sm">
                            <div><span class="text-gray-500">Floors:</span> {{ $project->requirement->number_of_floors }}</div>
                            <div><span class="text-gray-500">Bedrooms:</span> {{ $project->requirement->bedrooms }}</div>
                            <div><span class="text-gray-500">Bathrooms:</span> {{ $project->requirement->bathrooms }}</div>
                            <div><span class="text-gray-500">Garage:</span> {{ $project->requirement->garage_count }}</div>
                            <div><span class="text-gray-500">Living Rooms:</span> {{ $project->requirement->living_rooms }}</div>
                            <div><span class="text-gray-500">Kitchens:</span> {{ $project->requirement->kitchen_count }}</div>
                            <div><span class="text-gray-500">Finish:</span> <span class="uppercase">{{ $project->requirement->finish_level }}</span></div>
                        </div>
                    @else
                        <p class="mt-3 text-sm text-gray-500 bg-gray-50 p-3 rounded">
                            No requirements defined yet.
                        </p>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Instant Estimate</h3>
                        @can('update', $project)
                        @if($latestEstimate && !$latestEstimate->isEditable())
                            <span class="inline-flex items-center px-3 py-1.5 rounded text-xs font-semibold uppercase bg-amber-100 text-amber-800">
                                Latest Estimate Locked
                            </span>
                        @else
                            <form method="POST" action="{{ route('projects.estimates.store', $project) }}">
                                @csrf
                                <x-primary-button :disabled="!$project->requirement">Generate Estimate</x-primary-button>
                            </form>
                        @endif
                        @endcan
                    </div>

                    @if($latestEstimate)
                        <div class="mt-4 grid md:grid-cols-3 gap-4 text-sm">
                            <div><span class="text-gray-500">Gross Floor Area:</span> {{ number_format((float) $latestEstimate->gross_floor_area, 2) }} sqm</div>
                            <div><span class="text-gray-500">Cost per sqm:</span> {{ number_format((float) $latestEstimate->cost_per_sqm, 2) }}</div>
                            <div><span class="text-gray-500">Total Cost:</span> {{ number_format((float) $latestEstimate->total_cost, 2) }}</div>
                        </div>
                        <a href="{{ route('projects.estimates.show', [$project, $latestEstimate]) }}" class="mt-4 inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase">
                            Open Latest Estimate
                        </a>
                    @else
                        <p class="mt-3 text-sm text-gray-500">No estimate generated yet.</p>
                    @endif
                </div>
            </div>

            @if($project->estimates->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-3">Estimate History</h3>
                        <div class="space-y-2">
                            @foreach($project->estimates as $estimate)
                                <div class="flex items-center justify-between border rounded p-3">
                                    <div class="text-sm">
                                        <div class="font-medium">Estimate #{{ $estimate->id }}</div>
                                        <div class="text-gray-500">{{ optional($estimate->generated_at)->toDateTimeString() }}</div>
                                    </div>
                                    <div class="text-sm font-semibold">{{ number_format((float) $estimate->total_cost, 2) }}</div>
                                    <a href="{{ route('projects.estimates.show', [$project, $estimate]) }}" class="text-indigo-600 text-sm">View</a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
