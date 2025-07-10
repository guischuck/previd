<template>
  <AdminLayout title="Novo Usuário">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Novo Usuário
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="submit">
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
                    autofocus
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

                <!-- Senha -->
                <div>
                  <InputLabel for="password" value="Senha" />
                  <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="block w-full mt-1"
                    required
                  />
                  <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <!-- Empresa -->
                <div>
                  <InputLabel for="company_id" value="Empresa" />
                  <select
                    id="company_id"
                    v-model="form.company_id"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    required
                  >
                    <option value="">Selecione uma empresa</option>
                    <option v-for="company in companies" :key="company.id" :value="company.id">
                      {{ company.name }}
                    </option>
                  </select>
                  <InputError :message="form.errors.company_id" class="mt-2" />
                </div>

                <!-- Função -->
                <div>
                  <InputLabel for="role" value="Função" />
                  <select
                    id="role"
                    v-model="form.role"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    required
                  >
                    <option value="user">Usuário</option>
                    <option value="admin">Administrador</option>
                  </select>
                  <InputError :message="form.errors.role" class="mt-2" />
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
                    <option :value="true">Ativo</option>
                    <option :value="false">Inativo</option>
                  </select>
                  <InputError :message="form.errors.is_active" class="mt-2" />
                </div>
              </div>

              <!-- Botões -->
              <div class="flex items-center justify-end mt-6">
                <Link
                  :href="route('admin.users.index')"
                  class="text-gray-600 underline hover:text-gray-900"
                >
                  Cancelar
                </Link>

                <PrimaryButton
                  class="ml-4"
                  :class="{ 'opacity-25': form.processing }"
                  :disabled="form.processing"
                >
                  Criar Usuário
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

const props = defineProps({
  companies: {
    type: Array,
    required: true
  },
  company_id: {
    type: Number,
    required: false
  }
})

const form = useForm({
  name: '',
  email: '',
  password: '',
  company_id: props.company_id || '',
  role: 'user',
  is_active: true
})

function submit() {
  form.post(route('admin.users.store'))
}
</script> 