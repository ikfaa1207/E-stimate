<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Assembly</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('assemblies.update', $assembly) }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="name" value="Assembly Name" />
                                <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $assembly->name)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            </div>
                            <div>
                                <x-input-label for="unit" value="Unit" />
                                <x-text-input id="unit" name="unit" class="mt-1 block w-full" :value="old('unit', $assembly->unit)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('unit')" />
                            </div>
                        </div>

                        <div>
                            <h3 class="font-semibold mb-2">Assembly Items (qty per unit)</h3>
                            <div class="overflow-x-auto border rounded">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs uppercase text-gray-500">Item</th>
                                            <th class="px-4 py-2 text-left text-xs uppercase text-gray-500">Type</th>
                                            <th class="px-4 py-2 text-left text-xs uppercase text-gray-500">Unit</th>
                                            <th class="px-4 py-2 text-left text-xs uppercase text-gray-500">Qty/Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($items as $item)
                                            <tr>
                                                <td class="px-4 py-2">{{ $item->name }}</td>
                                                <td class="px-4 py-2 uppercase">{{ $item->type }}</td>
                                                <td class="px-4 py-2">{{ $item->unit }}</td>
                                                <td class="px-4 py-2">
                                                    <input type="number" step="0.0001" min="0" name="items[{{ $item->id }}]" value="{{ old('items.'.$item->id, $selected[$item->id] ?? 0) }}" class="border-gray-300 rounded-md shadow-sm w-32">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('items')" />
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="{{ route('assemblies.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-xs font-semibold uppercase">Cancel</a>
                            <x-primary-button>Save Assembly</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
