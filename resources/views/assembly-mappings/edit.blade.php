<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Assembly Metric Mapping</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('assembly-mappings.update') }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        @foreach(\App\Models\AssemblyMapping::METRICS as $metric)
                            <div>
                                <x-input-label :for="'mapping_'.$metric" :value="strtoupper(str_replace('_', ' ', $metric))" />
                                <select id="mapping_{{ $metric }}" name="mappings[{{ $metric }}]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Select assembly...</option>
                                    @foreach($assemblies as $assembly)
                                        <option value="{{ $assembly->id }}" @selected((int) old('mappings.'.$metric, $mappings[$metric] ?? 0) === (int) $assembly->id)>
                                            {{ $assembly->name }} ({{ $assembly->unit }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('mappings.'.$metric)" />
                            </div>
                        @endforeach

                        <div class="flex justify-end gap-2">
                            <a href="{{ route('assemblies.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-xs font-semibold uppercase">Back</a>
                            <x-primary-button>Save Mappings</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
