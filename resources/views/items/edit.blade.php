<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Cost Item</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('items.update', $item) }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <x-input-label for="name" value="Name" />
                            <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $item->name)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>
                        <div>
                            <x-input-label for="type" value="Type" />
                            <select id="type" name="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                @foreach(\App\Models\Item::TYPES as $type)
                                    <option value="{{ $type }}" @selected(old('type', $item->type) === $type)>{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('type')" />
                        </div>
                        <div>
                            <x-input-label for="unit" value="Unit" />
                            <x-text-input id="unit" name="unit" class="mt-1 block w-full" :value="old('unit', $item->unit)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('unit')" />
                        </div>
                        <div>
                            <x-input-label for="unit_cost" value="Unit Cost" />
                            <x-text-input id="unit_cost" name="unit_cost" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('unit_cost', $item->unit_cost)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('unit_cost')" />
                        </div>
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('items.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-xs font-semibold uppercase">Cancel</a>
                            <x-primary-button>Save</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
