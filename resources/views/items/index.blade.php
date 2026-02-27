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
