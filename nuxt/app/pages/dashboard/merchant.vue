<template>
  <div class="flex h-auto bg-gray-100 dark:bg-gray-900">
    <!-- Main Content -->
    <div class="flex flex-col flex-1">
      <AppMerchantPanel />
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';

const auth = useAuthStore();
const router = useRouter();

// Redirect if not merchant role
const isMerchant = computed(() => {
  return auth.user?.roles?.includes("merchant");
});

// Check user role and redirect if necessary
onMounted(() => {
  if (!auth.logged || !isMerchant.value) {
    router.push('/');
    useToast().add({
      title: 'Access Denied',
      description: 'You need merchant permissions to access this area.',
      color: 'red'
    });
  }
});
</script>

<style scoped>
/* Custom scrollbar styling */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.5);
  border-radius: 20px;
}

.dark ::-webkit-scrollbar-thumb {
  background-color: rgba(75, 85, 99, 0.5);
}
</style>