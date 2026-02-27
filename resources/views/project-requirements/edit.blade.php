<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Requirement Wizard - {{ $project->name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('projects.requirements.update', $project) }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="number_of_floors" value="Number of Floors" />
                                <x-text-input id="number_of_floors" name="number_of_floors" type="number" min="1" class="mt-1 block w-full" :value="old('number_of_floors', $requirement->number_of_floors ?? 1)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('number_of_floors')" />
                            </div>

                            <div>
                                <x-input-label for="finish_level" value="Finish Level" />
                                <select id="finish_level" name="finish_level" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    @foreach(\App\Models\ProjectRequirement::FINISH_LEVELS as $finishLevel)
                                        <option value="{{ $finishLevel }}" @selected(old('finish_level', $requirement->finish_level ?? 'standard') === $finishLevel)>
                                            {{ ucfirst($finishLevel) }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('finish_level')" />
                            </div>

                            <div>
                                <x-input-label for="bedrooms" value="Bedrooms" />
                                <x-text-input id="bedrooms" name="bedrooms" type="number" min="0" class="mt-1 block w-full" :value="old('bedrooms', $requirement->bedrooms ?? 0)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('bedrooms')" />
                            </div>

                            <div>
                                <x-input-label for="bathrooms" value="Bathrooms" />
                                <x-text-input id="bathrooms" name="bathrooms" type="number" min="0" class="mt-1 block w-full" :value="old('bathrooms', $requirement->bathrooms ?? 0)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('bathrooms')" />
                            </div>

                            <div>
                                <x-input-label for="garage_count" value="Garages" />
                                <x-text-input id="garage_count" name="garage_count" type="number" min="0" class="mt-1 block w-full" :value="old('garage_count', $requirement->garage_count ?? 0)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('garage_count')" />
                            </div>

                            <div>
                                <x-input-label for="living_rooms" value="Living Rooms" />
                                <x-text-input id="living_rooms" name="living_rooms" type="number" min="0" class="mt-1 block w-full" :value="old('living_rooms', $requirement->living_rooms ?? 0)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('living_rooms')" />
                            </div>

                            <div>
                                <x-input-label for="kitchen_count" value="Kitchens" />
                                <x-text-input id="kitchen_count" name="kitchen_count" type="number" min="0" class="mt-1 block w-full" :value="old('kitchen_count', $requirement->kitchen_count ?? 0)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('kitchen_count')" />
                            </div>
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-xs font-semibold uppercase">Cancel</a>
                            <x-primary-button>Save Requirements</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
