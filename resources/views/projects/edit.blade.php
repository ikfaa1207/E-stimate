<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Project
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="name" value="Project Name" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $project->name)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="client_name" value="Client Name" />
                            <x-text-input id="client_name" name="client_name" type="text" class="mt-1 block w-full" :value="old('client_name', $project->client_name)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('client_name')" />
                        </div>

                        <div>
                            <x-input-label for="client_id" value="Assigned Client User (Optional)" />
                            <select id="client_id" name="client_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">-- No User Assignment --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" @selected(old('client_id', $project->client_id) == $client->id)>
                                        {{ $client->name }} ({{ $client->email }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('client_id')" />
                        </div>

                        <div>
                            <x-input-label for="lot_area" value="Lot Area (sqm)" />
                            <x-text-input id="lot_area" name="lot_area" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('lot_area', $project->lot_area)" />
                            <x-input-error class="mt-2" :messages="$errors->get('lot_area')" />
                        </div>

                        <div class="border-t pt-4">
                            <h3 class="text-md font-semibold text-gray-700 mb-3">Building Specifications</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="building_type" value="Building Type" />
                                    <select id="building_type" name="building_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                        <option value="residential" @selected(old('building_type', $project->building_type) == 'residential')>Residential</option>
                                        <option value="commercial" @selected(old('building_type', $project->building_type) == 'commercial')>Commercial</option>
                                        <option value="industrial" @selected(old('building_type', $project->building_type) == 'industrial')>Industrial</option>
                                        <option value="institutional" @selected(old('building_type', $project->building_type) == 'institutional')>Institutional (School, Hospital, etc.)</option>
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('building_type')" />
                                </div>

                                <div>
                                    <x-input-label for="structural_type" value="Structural Frame Type" />
                                    <select id="structural_type" name="structural_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                        <option value="concrete" @selected(old('structural_type', $project->structural_type) == 'concrete')>Reinforced Concrete</option>
                                        <option value="steel" @selected(old('structural_type', $project->structural_type) == 'steel')>Structural Steel</option>
                                        <option value="mixed" @selected(old('structural_type', $project->structural_type) == 'mixed')>Mixed Concrete/Steel</option>
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('structural_type')" />
                                </div>

                                <div>
                                    <x-input-label for="foundation_type" value="Foundation Type" />
                                    <select id="foundation_type" name="foundation_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                        <option value="footing" @selected(old('foundation_type', $project->foundation_type) == 'footing')>Standard Isolated Footing</option>
                                        <option value="pile" @selected(old('foundation_type', $project->foundation_type) == 'pile')>Pile Foundation (Deep Foundation)</option>
                                        <option value="raft" @selected(old('foundation_type', $project->foundation_type) == 'raft')>Raft / Mat Foundation</option>
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('foundation_type')" />
                                </div>

                                <div>
                                    <x-input-label for="number_of_floors" value="Number of Floors" />
                                    <x-text-input id="number_of_floors" name="number_of_floors" type="number" min="1" class="mt-1 block w-full" :value="old('number_of_floors', $project->number_of_floors ?: 1)" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('number_of_floors')" />
                                </div>

                                <div>
                                    <x-input-label for="gross_floor_area" value="Gross Floor Area (GFA) (sqm)" />
                                    <x-text-input id="gross_floor_area" name="gross_floor_area" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('gross_floor_area', $project->gross_floor_area)" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('gross_floor_area')" />
                                </div>

                                <div>
                                    <x-input-label for="footprint_area" value="Building Footprint Area (sqm)" />
                                    <x-text-input id="footprint_area" name="footprint_area" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('footprint_area', $project->footprint_area)" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('footprint_area')" />
                                </div>

                                <div>
                                    <x-input-label for="finish_level" value="Finish Level Standard" />
                                    <select id="finish_level" name="finish_level" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                        <option value="economy" @selected(old('finish_level', $project->finish_level) == 'economy')>Economy</option>
                                        <option value="standard" @selected(old('finish_level', $project->finish_level) == 'standard')>Standard</option>
                                        <option value="premium" @selected(old('finish_level', $project->finish_level) == 'premium')>Premium</option>
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('finish_level')" />
                                </div>
                            </div>
                        </div>

                        <div>
                            <x-input-label for="notes" value="Notes" />
                            <textarea id="notes" name="notes" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('notes', $project->notes) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-xs font-semibold uppercase">Cancel</a>
                            <x-primary-button>Save</x-primary-button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('projects.destroy', $project) }}" class="mt-6" onsubmit="return confirm('Delete this project?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-100 text-red-700 rounded-md text-xs font-semibold uppercase">Delete Project</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
