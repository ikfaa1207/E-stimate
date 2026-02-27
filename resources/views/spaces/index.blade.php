<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Space Standards</h2>
            <a href="{{ route('spaces.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-xs font-semibold text-white uppercase">
                New Space
            </a>
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
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Category</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Default Area (sqm)</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Status</th>
                                    <th class="px-4 py-3 text-right text-xs uppercase text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($spaces as $space)
                                    <tr>
                                        <td class="px-4 py-3">{{ $space->name }}</td>
                                        <td class="px-4 py-3">{{ $space->category }}</td>
                                        <td class="px-4 py-3">{{ number_format((float) $space->default_area_sqm, 2) }}</td>
                                        <td class="px-4 py-3">
                                            @if($space->is_active)
                                                <span class="text-green-700 bg-green-100 px-2 py-1 rounded text-xs">Active</span>
                                            @else
                                                <span class="text-gray-700 bg-gray-100 px-2 py-1 rounded text-xs">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right space-x-2">
                                            <a href="{{ route('spaces.edit', $space) }}" class="text-indigo-600">Edit</a>
                                            <form method="POST" action="{{ route('spaces.destroy', $space) }}" class="inline" onsubmit="return confirm('Delete this space?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-red-600" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">No spaces configured.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $spaces->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
