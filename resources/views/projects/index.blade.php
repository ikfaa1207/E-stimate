<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Projects
            </h2>
            @can('create', App\Models\Project::class)
            <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                New Project
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lot Area</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Specs</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Latest Estimate</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($projects as $project)
                                    @php($latestEstimate = $project->estimates->first())
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $project->name }}</td>
                                        <td class="px-4 py-3">{{ $project->client_name }}</td>
                                        <td class="px-4 py-3">{{ $project->lot_area ? number_format((float) $project->lot_area, 2) . ' sqm' : '-' }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="capitalize font-semibold text-gray-700">{{ $project->building_type ?: 'Residential' }}</span>
                                            <div class="text-xs text-gray-500">GFA: {{ number_format((float) ($project->gross_floor_area ?: ($latestEstimate?->gross_floor_area ?: 0)), 2) }} sqm</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($latestEstimate)
                                                {{ number_format((float) $latestEstimate->total_cost, 2) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right space-x-2">
                                            <a class="text-indigo-600 hover:text-indigo-900" href="{{ route('projects.show', $project) }}">View</a>
                                            @can('update', $project)
                                            <a class="text-gray-600 hover:text-gray-900" href="{{ route('projects.edit', $project) }}">Edit</a>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">No projects yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $projects->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
