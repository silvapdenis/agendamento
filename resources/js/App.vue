<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
              <router-link to="/" class="text-xl font-bold text-primary-600">
                MediSystem
              </router-link>
            </div>

            <!-- Navigation Links -->
            <div class="hidden sm:ml-6 sm:flex items-center sm:space-x-8">
              <router-link 
                to="/doctors" 
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
              >
                Médicos
              </router-link>
              <router-link 
                to="/clinics" 
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
              >
                Clínicas
              </router-link>
            </div>
          </div>

          <!-- User menu -->
          <div class="flex items-center space-x-4">
            <template v-if="authStore.isAuthenticated">
              <router-link 
                to="/dashboard" 
                class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium"
              >
                Dashboard
              </router-link>
              <router-link 
                to="/appointments" 
                class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium"
              >
                Consultas
              </router-link>
              <router-link 
                v-if="authStore.user?.user_type === 'doctor'"
                to="/doctor/schedule" 
                class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium"
              >
                Agenda
              </router-link>
              <div class="relative">
                <button 
                  @click="showUserMenu = !showUserMenu"
                  class="flex text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                >
                  <span class="sr-only">Open user menu</span>
                  <div class="h-8 w-8 rounded-full bg-primary-600 flex items-center justify-center">
                    <span class="text-white text-sm font-medium">
                      {{ authStore.user?.name?.charAt(0).toUpperCase() }}
                    </span>
                  </div>
                </button>

                <!-- User dropdown -->
                <div 
                  v-show="showUserMenu"
                  class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50"
                >
                  <div class="py-1">
                    <router-link 
                      to="/profile"
                      class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    >
                      Perfil
                    </router-link>
                    <button 
                      @click="logout"
                      class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    >
                      Sair
                    </button>
                  </div>
                </div>
              </div>
            </template>
            <template v-else>
              <router-link 
                to="/login" 
                class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium"
              >
                Entrar
              </router-link>
              <router-link 
                to="/register" 
                class="btn-primary"
              >
                Cadastrar
              </router-link>
            </template>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main content -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-8">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="text-center text-sm text-gray-500">
          © 2024 MediSystem. Sistema de Agendamento Médico.
        </div>
      </div>
    </footer>

    <!-- Global Loading Overlay -->
    <LoadingSpinner 
      :show="globalLoading" 
      :title="loadingTitle"
      :message="loadingMessage"
      overlay
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import { useLoading } from '@/composables/useLoading';
import LoadingSpinner from '@/components/LoadingSpinner.vue';

const authStore = useAuthStore();
const router = useRouter();
const showUserMenu = ref(false);

// Loading global
const { globalLoading, loadingTitle, loadingMessage } = useLoading();

const logout = async () => {
  try {
    await authStore.logout();
    router.push('/');
  } catch (error) {
    console.error('Erro no logout:', error);
  }
};

onMounted(async () => {
  // Check if user is logged in on app start
  authStore.checkAuth();
  
  // Fetch fresh user data if authenticated
  if (authStore.isAuthenticated) {
    await authStore.fetchUser();
  }
});
</script>