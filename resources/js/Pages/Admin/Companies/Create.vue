<template>
  <AdminLayout title="Nova Empresa">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Nova Empresa
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
                </div>
              </div>

              <!-- Dados do Administrador -->
              <div class="mb-8">
                <h3 class="mb-4 text-lg font-medium text-gray-900">Dados do Administrador</h3>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                  <!-- Nome do Admin -->
                  <div>
                    <InputLabel for="admin_name" value="Nome" />
                    <TextInput
                      id="admin_name"
                      v-model="form.admin_name"
                      type="text"
                      class="block w-full mt-1"
                      required
                    />
                    <InputError :message="form.errors.admin_name" class="mt-2" />
                  </div>

                  <!-- Email do Admin -->
                  <div>
                    <InputLabel for="admin_email" value="Email" />
                    <TextInput
                      id="admin_email"
                      v-model="form.admin_email"
                      type="email"
                      class="block w-full mt-1"
                      required
                    />
                    <InputError :message="form.errors.admin_email" class="mt-2" />
                  </div>

                  <!-- Senha do Admin -->
                  <div>
                    <InputLabel for="admin_password" value="Senha" />
                    <TextInput
                      id="admin_password"
                      v-model="form.admin_password"
                      type="password"
                      class="block w-full mt-1"
                      required
                    />
                    <InputError :message="form.errors.admin_password" class="mt-2" />
                  </div>
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
                  Criar Empresa
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

const form = useForm({
  name: '',
  email: '',
  cnpj: '',
  admin_name: '',
  admin_email: '',
  admin_password: ''
})

function submit() {
  form.post(route('admin.companies.store'))
}
</script>

<script>
export default {
  directives: { maska: vMaska }
}
</script> 