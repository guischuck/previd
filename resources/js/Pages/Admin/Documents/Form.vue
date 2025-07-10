<template>
  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        {{ isEditing ? 'Editar Documento' : 'Novo Documento' }}
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="submit">
              <div class="grid grid-cols-1 gap-6">
                <!-- Título -->
                <div>
                  <InputLabel for="title" value="Título" />
                  <TextInput
                    id="title"
                    v-model="form.title"
                    type="text"
                    class="block w-full mt-1"
                    required
                    autofocus
                  />
                  <InputError :message="form.errors.title" class="mt-2" />
                </div>

                <!-- Descrição -->
                <div>
                  <InputLabel for="description" value="Descrição" />
                  <TextArea
                    id="description"
                    v-model="form.description"
                    class="block w-full mt-1"
                    required
                    rows="4"
                  />
                  <InputError :message="form.errors.description" class="mt-2" />
                </div>

                <!-- Categoria -->
                <div>
                  <InputLabel for="category" value="Categoria" />
                  <SelectInput
                    id="category"
                    v-model="form.category"
                    class="block w-full mt-1"
                    required
                  >
                    <option value="">Selecione uma categoria</option>
                    <option value="contrato">Contrato</option>
                    <option value="peticao">Petição</option>
                    <option value="procuracao">Procuração</option>
                    <option value="outros">Outros</option>
                  </SelectInput>
                  <InputError :message="form.errors.category" class="mt-2" />
                </div>

                <!-- Arquivo -->
                <div>
                  <InputLabel for="file" value="Arquivo" />
                  <input
                    type="file"
                    id="file"
                    ref="fileInput"
                    class="block w-full mt-1"
                    :required="!isEditing"
                    @change="handleFileChange"
                  />
                  <p class="mt-2 text-sm text-gray-500">
                    Arquivos permitidos: PDF, DOC, DOCX (máx. 10MB)
                  </p>
                  <InputError :message="form.errors.file" class="mt-2" />
                </div>

                <!-- Visibilidade -->
                <div>
                  <InputLabel for="is_public" value="Visibilidade" />
                  <div class="mt-1">
                    <Switch
                      v-model="form.is_public"
                      :class="[form.is_public ? 'bg-blue-600' : 'bg-gray-200']"
                      class="relative inline-flex h-6 w-11 items-center rounded-full"
                    >
                      <span class="sr-only">Tornar documento público</span>
                      <span
                        :class="[form.is_public ? 'translate-x-6' : 'translate-x-1']"
                        class="inline-block h-4 w-4 transform rounded-full bg-white transition"
                      />
                    </Switch>
                    <span class="ml-3 text-sm text-gray-500">
                      {{ form.is_public ? 'Público' : 'Privado' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Botões -->
              <div class="flex items-center justify-end mt-6 space-x-4">
                <Link
                  :href="route('admin.documents.index')"
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
import TextArea from '@/Components/TextArea.vue'
import SelectInput from '@/Components/SelectInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import Switch from '@/Components/Switch.vue'
import { ref } from 'vue'

const props = defineProps({
  document: {
    type: Object,
    default: null
  }
})

const isEditing = !!props.document
const fileInput = ref(null)

const form = useForm({
  title: props.document?.title ?? '',
  description: props.document?.description ?? '',
  category: props.document?.category ?? '',
  file: null,
  is_public: props.document?.is_public ?? false
})

const handleFileChange = (e) => {
  if (e.target.files.length > 0) {
    form.file = e.target.files[0]
  }
}

const submit = () => {
  if (isEditing) {
    form.post(route('admin.documents.update', props.document.id), {
      method: 'put',
      forceFormData: true
    })
  } else {
    form.post(route('admin.documents.store'), {
      forceFormData: true
    })
  }
}
</script> 