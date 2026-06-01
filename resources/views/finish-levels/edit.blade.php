<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Finish Level</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('finish-levels.update', $finishLevel) }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <x-input-label for="name" value="Name (slug-like, e.g. economy, premium, luxury)" />
                            <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $finishLevel->name)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>
                        <div>
                            <x-input-label for="display_name" value="Display Name (e.g. Economy, Premium, Luxury)" />
                            <x-text-input id="display_name" name="display_name" class="mt-1 block w-full" :value="old('display_name', $finishLevel->display_name)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('display_name')" />
                        </div>
                        <div>
                            <x-input-label for="material_multiplier" value="Material Multiplier" />
                            <x-text-input id="material_multiplier" name="material_multiplier" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('material_multiplier', $finishLevel->material_multiplier)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('material_multiplier')" />
                        </div>
                        <div>
                            <x-input-label for="labor_multiplier" value="Labor Multiplier" />
                            <x-text-input id="labor_multiplier" name="labor_multiplier" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('labor_multiplier', $finishLevel->labor_multiplier)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('labor_multiplier')" />
                        </div>
                        <div>
                            <x-input-label for="equipment_multiplier" value="Equipment Multiplier" />
                            <x-text-input id="equipment_multiplier" name="equipment_multiplier" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('equipment_multiplier', $finishLevel->equipment_multiplier)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('equipment_multiplier')" />
                        </div>
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $finishLevel->is_active))>
                            <span>Active</span>
                        </label>
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('finish-levels.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-xs font-semibold uppercase">Cancel</a>
                            <x-primary-button>Save</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
