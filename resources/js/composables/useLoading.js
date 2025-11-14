import { ref } from 'vue';

// Estado global de loading
const globalLoading = ref(false);
const loadingMessage = ref('');
const loadingTitle = ref('Carregando...');

export function useLoading() {
  const isLoading = ref(false);
  
  // Métodos para controle local
  const startLoading = (title = 'Carregando...', message = '') => {
    isLoading.value = true;
    loadingTitle.value = title;
    loadingMessage.value = message;
  };

  const stopLoading = () => {
    isLoading.value = false;
    loadingTitle.value = 'Carregando...';
    loadingMessage.value = '';
  };

  // Métodos para controle global (overlay)
  const startGlobalLoading = (title = 'Carregando...', message = '') => {
    globalLoading.value = true;
    loadingTitle.value = title;
    loadingMessage.value = message;
  };

  const stopGlobalLoading = () => {
    globalLoading.value = false;
    loadingTitle.value = 'Carregando...';
    loadingMessage.value = '';
  };

  // Helper para operações async
  const withLoading = async (operation, title = 'Carregando...', message = '') => {
    startLoading(title, message);
    try {
      const result = await operation();
      return result;
    } finally {
      stopLoading();
    }
  };

  // Helper para operações async com overlay global
  const withGlobalLoading = async (operation, title = 'Carregando...', message = '') => {
    startGlobalLoading(title, message);
    try {
      const result = await operation();
      return result;
    } finally {
      stopGlobalLoading();
    }
  };

  return {
    // Estados
    isLoading,
    globalLoading,
    loadingTitle,
    loadingMessage,
    
    // Métodos locais
    startLoading,
    stopLoading,
    
    // Métodos globais
    startGlobalLoading,
    stopGlobalLoading,
    
    // Helpers
    withLoading,
    withGlobalLoading
  };
}