<template>
  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        {{ isEditing ? 'Editar Empresa' : 'Nova Empresa' }}
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="submit">
              <!-- Dados da Empresa -->
              <div class="mb-8">
                <h3 class="mb-4 text-lg font-medium text-gray-900">
                  Dados da Empresa
                </h3>
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
                      v-mask="'##.###.###/####-##'"
                    />
                    <InputError :message="form.errors.cnpj" class="mt-2" />
                  </div>

                  <!-- Telefone -->
                  <div>
                    <InputLabel for="phone" value="Telefone" />
                    <TextInput
                      id="phone"
                      v-model="form.phone"
                      type="text"
                      class="block w-full mt-1"
                      v-mask="'(##) #####-####'"
                    />
                    <InputError :message="form.errors.phone" class="mt-2" />
                  </div>

                  <!-- Status -->
                  <div>
                    <InputLabel for="is_active" value="Status" />
                    <div class="mt-1">
                      <Switch
                        v-model="form.is_active"
                        :class="[form.is_active ? 'bg-blue-600' : 'bg-gray-200']"
                        class="relative inline-flex h-6 w-11 items-center rounded-full"
                      >
                        <span class="sr-only">Ativar empresa</span>
                        <span
                          :class="[form.is_active ? 'translate-x-6' : 'translate-x-1']"
                          class="inline-block h-4 w-4 transform rounded-full bg-white transition"
                        />
                      </Switch>
                      <span class="ml-3 text-sm text-gray-500">
                        {{ form.is_active ? 'Ativa' : 'Inativa' }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Dados do Administrador (apenas na criação) -->
              <div v-if="!isEditing" class="mb-8">
                <h3 class="mb-4 text-lg font-medium text-gray-900">
                  Dados do Administrador
                </h3>
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
              <div class="flex items-center justify-end mt-6 space-x-4">
                <Link
                  :href="route('admin.companies.index')"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50"
                >
                  Cancelar
                </Link>
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                  {{ isEditing ? 'Atualizar' : 'Criar' }}
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
import { Head, Link, useForm } from '@inertiajs/vue3'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import Switch from '@/Components/Switch.vue'
import { vMask } from 'vue-the-mask'

const props = defineProps({
  company: {
    type: Object,
    default: null
  }
})

const isEditing = !!props.company

const form = useForm({
  name: props.company?.name ?? '',
  email: props.company?.email ?? '',
  cnpj: props.company?.cnpj ?? '',
  phone: props.company?.phone ?? '',
  is_active: props.company?.is_active ?? true,
  admin_name: '',
  admin_email: '',
  admin_password: ''
})

const submit = () => {
  if (isEditing) {
    form.put(route('admin.companies.update', props.company.id))
  } else {
    form.post(route('admin.companies.store'))
  }
}
</script>

<script>
export default {
  directives: {
    mask: vMask
  }
}
</script> 