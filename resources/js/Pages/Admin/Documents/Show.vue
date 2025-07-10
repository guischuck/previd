<template>
  <AdminLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Detalhes do Documento
        </h2>
        <div class="flex space-x-4">
          <Link
            :href="route('admin.documents.edit', document.id)"
            class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-md hover:bg-yellow-700"
          >
            Editar
          </Link>
          <Link
            :href="route('admin.documents.download', document.id)"
            class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700"
          >
            Download
          </Link>
          <button
            @click="deleteDocument"
            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700"
          >
            Excluir
          </button>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Detalhes do Documento -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="mb-4 text-lg font-medium text-gray-900">
              Informações do Documento
            </h3>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <div>
                <p class="text-sm font-medium text-gray-500">Título</p>
                <p class="mt-1 text-sm text-gray-900">{{ document.title }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Categoria</p>
                <p class="mt-1 text-sm text-gray-900">{{ document.category }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Criado por</p>
                <p class="mt-1 text-sm text-gray-900">{{ document.createdBy?.name }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Visibilidade</p>
                <p class="mt-1">
                  <span :class="[
                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                    document.is_public ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                  ]">
                    {{ document.is_public ? 'Público' : 'Privado' }}
                  </span>
                </p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Criado em</p>
                <p class="mt-1 text-sm text-gray-900">
                  {{ new Date(document.created_at).toLocaleDateString('pt-BR') }}
                </p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Última atualização</p>
                <p class="mt-1 text-sm text-gray-900">
                  {{ new Date(document.updated_at).toLocaleDateString('pt-BR') }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Descrição -->
        <div class="mt-8 overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="mb-4 text-lg font-medium text-gray-900">
              Descrição
            </h3>
            <div class="prose max-w-none">
              {{ document.description }}
            </div>
          </div>
        </div>

        <!-- Histórico de Versões -->
        <div class="mt-8 overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="mb-4 text-lg font-medium text-gray-900">
              Histórico de Versões
            </h3>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Versão
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Atualizado por
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Data
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Ações
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="version in document.versions" :key="version.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">
                        v{{ version.version }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">{{ version.updatedBy?.name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">
                        {{ new Date(version.created_at).toLocaleDateString('pt-BR') }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex space-x-2">
                        <Link
                          :href="route('admin.documents.download-version', [document.id, version.id])"
                          class="text-blue-600 hover:text-blue-900"
                        >
                          Download
                        </Link>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  document: {
    type: Object,
    required: true
  }
})

const deleteDocument = () => {
  if (confirm(`Tem certeza que deseja excluir o documento ${props.document.title}?`)) {
    useForm().delete(route('admin.documents.destroy', props.document.id))
  }
}
</script> 