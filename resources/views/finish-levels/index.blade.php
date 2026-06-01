<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Finish Levels</h2>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('finish-levels.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-xs font-semibold text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-155">
                    New Finish Level
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Name</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Display Name</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Material Multiplier</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Labor Multiplier</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Equipment Multiplier</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Status</th>
                                    @if(auth()->user()->isAdmin())
                                        <th class="px-4 py-3 text-right text-xs uppercase text-gray-500">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($finishLevels as $finishLevel)
                                    <tr>
                                        <td class="px-4 py-3 font-medium">{{ $finishLevel->name }}</td>
                                        <td class="px-4 py-3">{{ $finishLevel->display_name }}</td>
                                        <td class="px-4 py-3">{{ number_format((float) $finishLevel->material_multiplier, 2) }}</td>
                                        <td class="px-4 py-3">{{ number_format((float) $finishLevel->labor_multiplier, 2) }}</td>
                                        <td class="px-4 py-3">{{ number_format((float) $finishLevel->equipment_multiplier, 2) }}</td>
                                        <td class="px-4 py-3">
                                            @if($finishLevel->is_active)
                                                <span class="text-green-700 bg-green-100 px-2 py-1 rounded text-xs">Active</span>
                                            @else
                                                <span class="text-gray-700 bg-gray-100 px-2 py-1 rounded text-xs">Inactive</span>
                                            @endif
                                        </td>
                                        @if(auth()->user()->isAdmin())
                                            <td class="px-4 py-3 text-right space-x-2">
                                                <a href="{{ route('finish-levels.edit', $finishLevel) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                <form method="POST" action="{{ route('finish-levels.destroy', $finishLevel) }}" class="inline" onsubmit="return confirm('Delete this finish level?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="text-red-600 hover:text-red-900" type="submit">Delete</button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()->isAdmin() ? 7 : 6 }}" class="px-4 py-6 text-center text-gray-500">No finish levels configured.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $finishLevels->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
