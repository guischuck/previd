<template>
  <AdminLayout :title="`Editar ${company.name}`">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Editar {{ company.name }}
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="submit">
              <!-- Dados da Empresa -->
              <div class="mb-8">
                <h3 class="mb-4 text-lg font-medium text-gray-900">Dados da Empresa</h3>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                  <!-- Nome -->
                  <div>
                    <InputLabel for="name" value="Nome" />
                    <TextInput
                      id="name"
                      v-model="form.name"
                      type="text"
                      class="block w-full mt-1"
                      required
                    />
                    <InputError :message="form.errors.name" class="mt-2" />
                  </div>

                  <!-- Email -->
                  <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                      id="email"
                      v-model="form.email"
                      type="email"
                      class="block w-full mt-1"
                      required
                    />
                    <InputError :message="form.errors.email" class="mt-2" />
                  </div>

                  <!-- CNPJ -->
                  <div>
                    <InputLabel for="cnpj" value="CNPJ" />
                    <TextInput
                      id="cnpj"
                      v-model="form.cnpj"
                      type="text"
                      class="block w-full mt-1"
                      v-maska="'##.###.###/####-##'"
                    />
                    <InputError :message="form.errors.cnpj" class="mt-2" />
                  </div>

                  <!-- Status -->
                  <div>
                    <InputLabel for="is_active" value="Status" />
                    <select
                      id="is_active"
                      v-model="form.is_active"
                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                      required
                    >
                      <option :value="true">Ativa</option>
                      <option :value="false">Inativa</option>
                    </select>
                    <InputError :message="form.errors.is_active" class="mt-2" />
                  </div>

                  <!-- Limite de Usuários -->
                  <div>
                    <InputLabel for="max_users" value="Limite de Usuários" />
                    <TextInput
                      id="max_users"
                      v-model="form.max_users"
                      type="number"
                      class="block w-full mt-1"
                      required
                      min="1"
                    />
                    <InputError :message="form.errors.max_users" class="mt-2" />
                  </div>

                  <!-- Limite de Processos -->
                  <div>
                    <InputLabel for="max_cases" value="Limite de Processos" />
                    <TextInput
                      id="max_cases"
                      v-model="form.max_cases"
                      type="number"
                      class="block w-full mt-1"
                      required
                      min="1"
                    />
                    <InputError :message="form.errors.max_cases" class="mt-2" />
                  </div>
                </div>
              </div>

              <!-- Usuários -->
              <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                  <h3 class="text-lg font-medium text-gray-900">Usuários</h3>
                  <Link
                    :href="route('admin.users.create', { company_id: company.id })"
                    class="text-sm text-blue-600 hover:text-blue-900"
                  >
                    Adicionar Usuário
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
                        <th scope="col" class="relative px-6 py-3">
                          <span class="sr-only">Ações</span>
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
                        <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                          <Link :href="route('admin.users.edit', user.id)" class="text-blue-600 hover:text-blue-900">
                            Editar
                          </Link>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Botões -->
              <div class="flex items-center justify-end mt-6">
                <Link
                  :href="route('admin.companies.index')"
                  class="text-gray-600 underline hover:text-gray-900"
                >
                  Cancelar
                </Link>

                <PrimaryButton
                  class="ml-4"
                  :class="{ 'opacity-25': form.processing }"
                  :disabled="form.processing"
                >
                  Salvar Alterações
                </PrimaryButton>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { vMaska } from 'maska'

const props = defineProps({
  company: {
    type: Object,
    required: true
  }
})

const form = useForm({
  name: props.company.name,
  email: props.company.email,
  cnpj: props.company.cnpj,
  is_active: props.company.is_active,
  max_users: props.company.max_users,
  max_cases: props.company.max_cases
})

function submit() {
  form.put(route('admin.companies.update', props.company.id))
}
</script>

<script>
export default {
  directives: { maska: vMaska }
}
</script> 