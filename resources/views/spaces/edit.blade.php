<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Space Standard</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('spaces.update', $space) }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <x-input-label for="name" value="Name" />
                            <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $space->name)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>
                        <div>
                            <x-input-label for="category" value="Category" />
                            <x-text-input id="category" name="category" class="mt-1 block w-full" :value="old('category', $space->category)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('category')" />
                        </div>
                        <div>
                            <x-input-label for="default_area_sqm" value="Default Area (sqm)" />
                            <x-text-input id="default_area_sqm" name="default_area_sqm" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('default_area_sqm', $space->default_area_sqm)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('default_area_sqm')" />
                        </div>
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $space->is_active))>
                            <span>Active</span>
                        </label>
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('spaces.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-xs font-semibold uppercase">Cancel</a>
                            <x-primary-button>Save</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
