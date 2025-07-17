<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Adicionar Documento Legal
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('legal-documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Título</label>
                            <input type="text" name="title" id="title" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                value="{{ old('title') }}">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Tipo</label>
                            <select name="type" id="type" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Selecione...</option>
                                <option value="acordao" {{ old('type') == 'acordao' ? 'selected' : '' }}>Acórdão</option>
                                <option value="lei" {{ old('type') == 'lei' ? 'selected' : '' }}>Lei</option>
                                <option value="jurisprudencia" {{ old('type') == 'jurisprudencia' ? 'selected' : '' }}>Jurisprudência</option>
                                <option value="sumula" {{ old('type') == 'sumula' ? 'selected' : '' }}>Súmula</option>
                                <option value="portaria" {{ old('type') == 'portaria' ? 'selected' : '' }}>Portaria</option>
                                <option value="decreto" {{ old('type') == 'decreto' ? 'selected' : '' }}>Decreto</option>
                                <option value="resolucao" {{ old('type') == 'resolucao' ? 'selected' : '' }}>Resolução</option>
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea name="description" id="description" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="file" class="block text-sm font-medium text-gray-700">Arquivo PDF</label>
                            <input type="file" name="file" id="file" required accept=".pdf"
                                class="mt-1 block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100">
                            <p class="mt-1 text-sm text-gray-500">Tamanho máximo: 10MB</p>
                            @error('file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="metadata-fields" class="space-y-4">
                            <label class="block text-sm font-medium text-gray-700">Metadados</label>
                            <div class="metadata-field grid grid-cols-2 gap-4">
                                <input type="text" name="metadata[keys][]" placeholder="Chave"
                                    class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <input type="text" name="metadata[values][]" placeholder="Valor"
                                    class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <button type="button" onclick="addMetadataField()"
                                class="text-sm text-blue-600 hover:text-blue-900">
                                + Adicionar mais metadados
                            </button>
                        </div>

                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('legal-documents.index') }}"
                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancelar
                            </a>
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Salvar Documento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function addMetadataField() {
            const container = document.getElementById('metadata-fields');
            const newField = document.createElement('div');
            newField.className = 'metadata-field grid grid-cols-2 gap-4';
            newField.innerHTML = `
                <input type="text" name="metadata[keys][]" placeholder="Chave"
                    class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <input type="text" name="metadata[values][]" placeholder="Valor"
                    class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            `;
            container.insertBefore(newField, container.lastElementChild);
        }
    </script>
    @endpush
</x-app-layout> 