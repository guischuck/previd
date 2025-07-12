<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $document->title }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('legal-documents.download', $document) }}" 
                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Download
                </a>
                <a href="{{ route('legal-documents.index') }}" 
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Informações Básicas -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Informações Básicas</h3>
                            <dl class="grid grid-cols-1 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Tipo</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $document->type_text }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Tamanho</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $document->file_size_formatted }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Adicionado em</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $document->created_at->format('d/m/Y H:i') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Adicionado por</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $document->uploadedBy->name }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Metadados -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Metadados</h3>
                            @if($document->metadata && count($document->metadata))
                                <dl class="grid grid-cols-1 gap-4">
                                    @foreach($document->metadata as $key => $value)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ $key }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $value }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            @else
                                <p class="text-sm text-gray-500">Nenhum metadado disponível.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Descrição -->
                    @if($document->description)
                        <div class="mt-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Descrição</h3>
                            <div class="prose max-w-none">
                                {{ $document->description }}
                            </div>
                        </div>
                    @endif

                    <!-- Conteúdo Extraído -->
                    @if($document->is_processed && $document->extracted_text)
                        <div class="mt-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Conteúdo do Documento</h3>
                            <div class="prose max-w-none bg-gray-50 p-4 rounded-md">
                                {!! nl2br(e($document->extracted_text)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 