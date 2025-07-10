<template>
  <AdminLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Documentos
        </h2>
        <Link
          :href="route('admin.documents.create')"
          class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
        >
          Novo Documento
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Cards de Estatísticas -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <!-- Total -->
          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-blue-500 rounded-md">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                <div class="flex-1 w-0 ml-5">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Total de Documentos
                    </dt>
                    <dd class="text-2xl font-semibold text-gray-900">
                      {{ stats.total }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Públicos -->
          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-green-500 rounded-md">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="flex-1 w-0 ml-5">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Documentos Públicos
                    </dt>
                    <dd class="text-2xl font-semibold text-gray-900">
                      {{ stats.public }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Privados -->
          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-yellow-500 rounded-md">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <div class="flex-1 w-0 ml-5">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Documentos Privados
                    </dt>
                    <dd class="text-2xl font-semibold text-gray-900">
                      {{ stats.private }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Lista de Documentos -->
        <div class="mt-8 overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Título
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Categoria
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Criado por
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Visibilidade
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Criado em
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Ações
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="document in documents.data" :key="document.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">
                        {{ document.title }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">{{ document.category }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">{{ document.createdBy?.name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="[
                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                        document.is_public ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                      ]">
                        {{ document.is_public ? 'Público' : 'Privado' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">
                        {{ new Date(document.created_at).toLocaleDateString('pt-BR') }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex space-x-2">
                        <Link
                          :href="route('admin.documents.show', document.id)"
                          class="text-blue-600 hover:text-blue-900"
                        >
                          Ver
                        </Link>
                        <Link
                          :href="route('admin.documents.edit', document.id)"
                          class="text-yellow-600 hover:text-yellow-900"
                        >
                          Editar
                        </Link>
                        <Link
                          :href="route('admin.documents.download', document.id)"
                          class="text-green-600 hover:text-green-900"
                        >
                          Download
                        </Link>
                        <button
                          @click="deleteDocument(document)"
                          class="text-red-600 hover:text-red-900"
                        >
                          Excluir
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Paginação -->
            <div class="mt-6">
              <Pagination :links="documents.links" />
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
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
  documents: {
    type: Object,
    required: true
  },
  stats: {
    type: Object,
    required: true
  }
})

const deleteDocument = (document) => {
  if (confirm(`Tem certeza que deseja excluir o documento ${document.title}?`)) {
    useForm().delete(route('admin.documents.destroy', document.id))
  }
}
</script> 