<template>
  <div class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Main Content -->
    <div class="flex flex-col flex-1">
      <AppMerchantPanel />
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import { computed, onMounted } from 'vue';

const auth = useAuthStore();
const router = useRouter();

// Compute role access permissions
const isMerchant = computed(() => auth.user?.roles?.includes("merchant"));
const isAdmin = computed(() => auth.user?.roles?.includes("admin"));
const isJunkshopOwner = computed(() => auth.user?.roles?.includes("junkshop_owner"));

// Check if user has any of the allowed roles
const hasAccess = computed(() => {
  return isMerchant.value || isAdmin.value || isJunkshopOwner.value;
});

// Check user role and redirect if necessary
onMounted(() => {
  if (!auth.logged || !hasAccess.value) {
    router.push('/');
    useToast().add({
      title: 'Access Denied',
      description: 'You need merchant, junkshop owner, or admin permissions to access this area.',
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
