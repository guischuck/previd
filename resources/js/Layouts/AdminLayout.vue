<template>
  <div>
    <Head :title="title" />

    <div class="min-h-screen bg-gray-100">
      <!-- Barra de navegação superior -->
      <nav class="bg-white border-b border-gray-100">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div class="flex justify-between h-16">
            <div class="flex">
              <!-- Logo -->
              <div class="flex items-center shrink-0">
                <Link :href="route('admin.dashboard')">
                  <ApplicationLogo class="block w-auto h-9" />
                </Link>
              </div>

              <!-- Links de Navegação -->
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                <NavLink :href="route('admin.dashboard')" :active="route().current('admin.dashboard')">
                  Dashboard
                </NavLink>
                <NavLink :href="route('admin.companies.index')" :active="route().current('admin.companies.*')">
                  Empresas
                </NavLink>
                <NavLink :href="route('admin.users.index')" :active="route().current('admin.users.*')">
                  Usuários
                </NavLink>
                <NavLink :href="route('admin.documents.index')" :active="route().current('admin.documents.*')">
                  Base de Conhecimento
                </NavLink>
              </div>
            </div>

            <!-- Menu do Usuário -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
              <div class="relative ml-3">
                <Dropdown align="right" width="48">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button type="button" class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md hover:text-gray-700 focus:outline-none">
                        {{ $page.props.auth.user.name }}
                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <DropdownLink :href="route('profile.edit')">
                      Perfil
                    </DropdownLink>
                    <DropdownLink :href="route('logout')" method="post" as="button">
                      Sair
                    </DropdownLink>
                  </template>
                </Dropdown>
              </div>
            </div>

            <!-- Botão do Menu Mobile -->
            <div class="flex items-center -mr-2 sm:hidden">
              <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500">
                <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                  <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                  <path :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Menu Mobile -->
        <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="sm:hidden">
          <div class="pt-2 pb-3 space-y-1">
            <ResponsiveNavLink :href="route('admin.dashboard')" :active="route().current('admin.dashboard')">
              Dashboard
            </ResponsiveNavLink>
            <ResponsiveNavLink :href="route('admin.companies.index')" :active="route().current('admin.companies.*')">
              Empresas
            </ResponsiveNavLink>
            <ResponsiveNavLink :href="route('admin.users.index')" :active="route().current('admin.users.*')">
              Usuários
            </ResponsiveNavLink>
            <ResponsiveNavLink :href="route('admin.documents.index')" :active="route().current('admin.documents.*')">
              Base de Conhecimento
            </ResponsiveNavLink>
          </div>

          <!-- Menu Mobile do Usuário -->
          <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
              <div class="text-base font-medium text-gray-800">
                {{ $page.props.auth.user.name }}
              </div>
              <div class="text-sm font-medium text-gray-500">
                {{ $page.props.auth.user.email }}
              </div>
            </div>

            <div class="mt-3 space-y-1">
              <ResponsiveNavLink :href="route('profile.edit')">
                Perfil
              </ResponsiveNavLink>
              <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                Sair
              </ResponsiveNavLink>
            </div>
          </div>
        </div>
      </nav>

      <!-- Cabeçalho da Página -->
      <header class="bg-white shadow" v-if="$slots.header">
        <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </header>

      <!-- Conteúdo da Página -->
      <main>
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import NavLink from '@/Components/NavLink.vue'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'

const showingNavigationDropdown = ref(false)

defineProps({
  title: {
    type: String,
    default: ''
  }
})
</script> 