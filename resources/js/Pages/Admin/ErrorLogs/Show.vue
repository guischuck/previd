<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { format } from 'date-fns';
import ptBR from 'date-fns/locale/pt-BR';
import { CheckCircleIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    log: {
        type: Object,
        required: true
    }
});

const form = useForm({
    resolution_notes: ''
});

const formatDate = (date) => {
    return format(new Date(date), "dd 'de' MMMM 'de' yyyy 'às' HH:mm", { locale: ptBR });
};

const markAsResolved = () => {
    form.post(route('admin.error-logs.mark-as-resolved', props.log.id), {
        preserveScroll: true
    });
};

const deleteLog = () => {
    if (confirm('Tem certeza que deseja excluir este log?')) {
        form.delete(route('admin.error-logs.destroy', props.log.id));
    }
};
</script>

<template>
    <Head title="Detalhes do Erro" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Detalhes do Log de Erro
                </h2>
                <div class="flex space-x-4">
                    <button
                        @click="deleteLog"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700"
                    >
                        Excluir
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Informações Básicas -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">
                            Informações Básicas
                        </h3>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Tipo</p>
                                <p class="mt-1">
                                    <span :class="[
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        log.type === 'error' ? 'bg-red-100 text-red-800' :
                                        log.type === 'warning' ? 'bg-yellow-100 text-yellow-800' :
                                        'bg-blue-100 text-blue-800'
                                    ]">
                                        {{ log.type }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Data</p>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ new Date(log.created_at).toLocaleDateString('pt-BR') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Arquivo</p>
                                <p class="mt-1 text-sm text-gray-900">{{ log.file }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Linha</p>
                                <p class="mt-1 text-sm text-gray-900">{{ log.line }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">URL</p>
                                <p class="mt-1 text-sm text-gray-900">{{ log.url }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Método</p>
                                <p class="mt-1 text-sm text-gray-900">{{ log.method }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensagem de Erro -->
                <div class="mt-8 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">
                            Mensagem de Erro
                        </h3>
                        <div class="p-4 font-mono text-sm text-gray-900 bg-gray-100 rounded-lg">
                            {{ log.message }}
                        </div>
                    </div>
                </div>

                <!-- Stack Trace -->
                <div class="mt-8 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">
                            Stack Trace
                        </h3>
                        <div class="p-4 overflow-x-auto font-mono text-sm text-gray-900 bg-gray-100 rounded-lg">
                            <pre>{{ log.stack_trace }}</pre>
                        </div>
                    </div>
                </div>

                <!-- Dados da Requisição -->
                <div class="mt-8 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">
                            Dados da Requisição
                        </h3>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <p class="text-sm font-medium text-gray-500">IP</p>
                                <p class="mt-1 text-sm text-gray-900">{{ log.ip }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">User Agent</p>
                                <p class="mt-1 text-sm text-gray-900">{{ log.user_agent }}</p>
                            </div>
                            <div class="sm:col-span-2">
                                <p class="text-sm font-medium text-gray-500">Headers</p>
                                <div class="p-4 mt-1 overflow-x-auto font-mono text-sm text-gray-900 bg-gray-100 rounded-lg">
                                    <pre>{{ JSON.stringify(log.headers, null, 2) }}</pre>
                                </div>
                            </div>
                            <div class="sm:col-span-2">
                                <p class="text-sm font-medium text-gray-500">Parâmetros</p>
                                <div class="p-4 mt-1 overflow-x-auto font-mono text-sm text-gray-900 bg-gray-100 rounded-lg">
                                    <pre>{{ JSON.stringify(log.parameters, null, 2) }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contexto -->
                <div class="mt-8 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">
                            Contexto
                        </h3>
                        <div class="p-4 overflow-x-auto font-mono text-sm text-gray-900 bg-gray-100 rounded-lg">
                            <pre>{{ JSON.stringify(log.context, null, 2) }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template> 