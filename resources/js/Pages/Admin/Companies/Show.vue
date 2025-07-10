<template>
  <AdminLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Detalhes da Empresa
        </h2>
        <div class="flex space-x-4">
          <Link
            :href="route('admin.companies.edit', company.id)"
            class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-md hover:bg-yellow-700"
          >
            Editar
          </Link>
          <button
            @click="deleteCompany"
            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700"
          >
            Excluir
          </button>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Cards de Estatísticas -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
          <!-- Total de Usuários -->
          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-blue-500 rounded-md">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <div class="flex-1 w-0 ml-5">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Total de Usuários
                    </dt>
                    <dd class="text-2xl font-semibold text-gray-900">
                      {{ stats.users_count }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Total de Casos -->
          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-green-500 rounded-md">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                <div class="flex-1 w-0 ml-5">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Total de Casos
                    </dt>
                    <dd class="text-2xl font-semibold text-gray-900">
                      {{ stats.cases_count }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Casos Ativos -->
          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-yellow-500 rounded-md">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="flex-1 w-0 ml-5">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Casos Ativos
                    </dt>
                    <dd class="text-2xl font-semibold text-gray-900">
                      {{ stats.active_cases }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Casos Concluídos -->
          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-blue-500 rounded-md">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="flex-1 w-0 ml-5">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Casos Concluídos
                    </dt>
                    <dd class="text-2xl font-semibold text-gray-900">
                      {{ stats.completed_cases }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Detalhes da Empresa -->
        <div class="mt-8 overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="mb-4 text-lg font-medium text-gray-900">
              Informações da Empresa
            </h3>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <div>
                <p class="text-sm font-medium text-gray-500">Nome</p>
                <p class="mt-1 text-sm text-gray-900">{{ company.name }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Email</p>
                <p class="mt-1 text-sm text-gray-900">{{ company.email }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">CNPJ</p>
                <p class="mt-1 text-sm text-gray-900">{{ company.cnpj || '-' }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Telefone</p>
                <p class="mt-1 text-sm text-gray-900">{{ company.phone || '-' }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Status</p>
                <p class="mt-1">
                  <span :class="[
                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                    company.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                  ]">
                    {{ company.is_active ? 'Ativa' : 'Inativa' }}
                  </span>
                </p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Criada em</p>
                <p class="mt-1 text-sm text-gray-900">
                  {{ new Date(company.created_at).toLocaleDateString('pt-BR') }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Lista de Usuários -->
        <div class="mt-8 overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-gray-900">
                Usuários
              </h3>
              <Link
                :href="route('admin.users.create', { company_id: company.id })"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
              >
                Novo Usuário
              </Link>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Nome
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Email
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Função
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                      Ações
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="user in company.users" :key="user.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">
                        {{ user.name }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">{{ user.email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">
                        {{ user.role === 'admin' ? 'Administrador' : 'Usuário' }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="[
                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                        user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                      ]">
                        {{ user.is_active ? 'Ativo' : 'Inativo' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex space-x-2">
                        <Link
                          :href="route('admin.users.show', user.id)"
                          class="text-blue-600 hover:text-blue-900"
                        >
                          Ver
                        </Link>
                        <Link
                          :href="route('admin.users.edit', user.id)"
                          class="text-yellow-600 hover:text-yellow-900"
                        >
                          Editar
                        </Link>
                        <button
                          @click="deleteUser(user)"
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
  company: {
    type: Object,
    required: true
  },
  stats: {
    type: Object,
    required: true
  }
})

const deleteCompany = () => {
  if (confirm(`Tem certeza que deseja excluir a empresa ${props.company.name}?`)) {
    useForm().delete(route('admin.companies.destroy', props.company.id))
  }
}

const deleteUser = (user) => {
  if (confirm(`Tem certeza que deseja excluir o usuário ${user.name}?`)) {
    useForm().delete(route('admin.users.destroy', user.id))
  }
}
</script> 