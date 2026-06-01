<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Assemblies</h2>
            <div class="space-x-2">
                <a href="{{ route('assembly-mappings.edit') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-xs font-semibold uppercase">
                    Metric Mapping
                </a>
                <a href="{{ route('assemblies.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md text-xs font-semibold uppercase">
                    New Assembly
                </a>
            </div>
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
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Unit</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Items</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Base Unit Cost</th>
                                    <th class="px-4 py-3 text-right text-xs uppercase text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($assemblies as $assembly)
                                    <tr>
                                        <td class="px-4 py-3">{{ $assembly->name }}</td>
                                        <td class="px-4 py-3">{{ $assembly->unit }}</td>
                                        <td class="px-4 py-3">{{ $assembly->assembly_items_count }}</td>
                                        <td class="px-4 py-3 font-mono text-sm font-semibold">{{ number_format($assembly->base_unit_cost, 2) }}</td>
                                        <td class="px-4 py-3 text-right space-x-2">
                                            <a href="{{ route('assemblies.edit', $assembly) }}" class="text-indigo-600">Edit</a>
                                            <form method="POST" action="{{ route('assemblies.destroy', $assembly) }}" class="inline" onsubmit="return confirm('Delete this assembly?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">No assemblies configured.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $assemblies->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
