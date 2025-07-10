<template>
  <AdminLayout :title="`Editar ${document.title}`">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Editar {{ document.title }}
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="submit">
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Título -->
                <div class="sm:col-span-2">
                  <InputLabel for="title" value="Título" />
                  <TextInput
                    id="title"
                    v-model="form.title"
                    type="text"
                    class="block w-full mt-1"
                    required
                  />
                  <InputError :message="form.errors.title" class="mt-2" />
                </div>

                <!-- Descrição -->
                <div class="sm:col-span-2">
                  <InputLabel for="description" value="Descrição" />
                  <textarea
                    id="description"
                    v-model="form.description"
                    rows="3"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    required
                  ></textarea>
                  <InputError :message="form.errors.description" class="mt-2" />
                </div>

                <!-- Categoria -->
                <div>
                  <InputLabel for="category" value="Categoria" />
                  <select
                    id="category"
                    v-model="form.category"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    required
                  >
                    <option value="">Selecione uma categoria</option>
                    <option value="lei">Lei</option>
                    <option value="jurisprudencia">Jurisprudência</option>
                    <option value="acordao">Acórdão</option>
                    <option value="outro">Outro</option>
                  </select>
                  <InputError :message="form.errors.category" class="mt-2" />
                </div>

                <!-- Visibilidade -->
                <div>
                  <InputLabel for="is_public" value="Visibilidade" />
                  <select
                    id="is_public"
                    v-model="form.is_public"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    required
                  >
                    <option :value="true">Público</option>
                    <option :value="false">Privado</option>
                  </select>
                  <InputError :message="form.errors.is_public" class="mt-2" />
                </div>

                <!-- Arquivo -->
                <div class="sm:col-span-2">
                  <InputLabel for="file" value="Arquivo PDF" />
                  <div class="flex items-center mt-1">
                    <Link
                      :href="route('admin.documents.download', document.id)"
                      class="text-sm text-blue-600 hover:text-blue-900"
                    >
                      {{ document.file_name }}
                    </Link>
                  </div>
                  <div class="mt-2">
                    <input
                      type="file"
                      id="file"
                      ref="fileInput"
                      @change="handleFileChange"
                      accept=".pdf"
                      class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                    />
                    <p class="mt-1 text-sm text-gray-500">
                      Deixe em branco para manter o arquivo atual
                    </p>
                  </div>
                  <InputError :message="form.errors.file" class="mt-2" />
                </div>
              </div>

              <!-- Botões -->
              <div class="flex items-center justify-end mt-6">
                <Link
                  :href="route('admin.documents.index')"
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
import { ref } from 'vue'

const props = defineProps({
  document: {
    type: Object,
    required: true
  }
})

const fileInput = ref(null)

const form = useForm({
  title: props.document.title,
  description: props.document.description,
  category: props.document.category,
  is_public: props.document.is_public,
  file: null,
  _method: 'PUT'
})

function handleFileChange(e) {
  const file = e.target.files[0]
  if (file) {
    form.file = file
  }
}

function submit() {
  form.post(route('admin.documents.update', props.document.id), {
    preserveScroll: true,
    onSuccess: () => {
      if (fileInput.value) {
        fileInput.value.value = ''
      }
    }
  })
}
</script> 