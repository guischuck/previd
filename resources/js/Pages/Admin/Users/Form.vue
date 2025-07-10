<template>
  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        {{ isEditing ? 'Editar Usuário' : 'Novo Usuário' }}
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

                <!-- Senha (opcional na edição) -->
                <div>
                  <InputLabel for="password" value="Senha" />
                  <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="block w-full mt-1"
                    :required="!isEditing"
                  />
                  <InputError :message="form.errors.password" class="mt-2" />
                  <p v-if="isEditing" class="mt-2 text-sm text-gray-500">
                    Deixe em branco para manter a senha atual
                  </p>
                </div>

                <!-- Empresa -->
                <div>
                  <InputLabel for="company_id" value="Empresa" />
                  <SelectInput
                    id="company_id"
                    v-model="form.company_id"
                    class="block w-full mt-1"
                    required
                  >
                    <option value="">Selecione uma empresa</option>
                    <option v-for="company in companies" :key="company.id" :value="company.id">
                      {{ company.name }}
                    </option>
                  </SelectInput>
                  <InputError :message="form.errors.company_id" class="mt-2" />
                </div>

                <!-- Função -->
                <div>
                  <InputLabel for="role" value="Função" />
                  <SelectInput
                    id="role"
                    v-model="form.role"
                    class="block w-full mt-1"
                    required
                  >
                    <option value="">Selecione uma função</option>
                    <option value="admin">Administrador</option>
                    <option value="user">Usuário</option>
                  </SelectInput>
                  <InputError :message="form.errors.role" class="mt-2" />
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
                      <span class="sr-only">Ativar usuário</span>
                      <span
                        :class="[form.is_active ? 'translate-x-6' : 'translate-x-1']"
                        class="inline-block h-4 w-4 transform rounded-full bg-white transition"
                      />
                    </Switch>
                    <span class="ml-3 text-sm text-gray-500">
                      {{ form.is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Botões -->
              <div class="flex items-center justify-end mt-6 space-x-4">
                <Link
                  :href="route('admin.users.index')"
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
import SelectInput from '@/Components/SelectInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import Switch from '@/Components/Switch.vue'

const props = defineProps({
  user: {
    type: Object,
    default: null
  },
  companies: {
    type: Array,
    required: true
  }
})

const isEditing = !!props.user

const form = useForm({
  name: props.user?.name ?? '',
  email: props.user?.email ?? '',
  password: '',
  company_id: props.user?.company_id ?? '',
  role: props.user?.role ?? '',
  is_active: props.user?.is_active ?? true
})

const submit = () => {
  if (isEditing) {
    form.put(route('admin.users.update', props.user.id))
  } else {
    form.post(route('admin.users.store'))
  }
}
</script> 