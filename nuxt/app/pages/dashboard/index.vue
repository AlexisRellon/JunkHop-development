<template>
  <div v-if="isAdminUser">
    <AppAdminPanel />
  </div>
  <div v-else-if="isJunkshopOwnerUser">
    <AppJunkshopPanel />
  </div>
  <div v-else-if="isMerchantUser">
    <AppMerchantPanel />
  </div>
  <div v-else>
    <!-- Fallback or redirect -->
    <div class="flex flex-col items-center justify-center h-screen bg-gray-100 dark:bg-gray-900">
      <div class="text-center p-8 bg-white dark:bg-gray-800 rounded-lg shadow-md">
        <UIcon name="i-heroicons-exclamation-triangle" class="w-16 h-16 text-amber-500 mx-auto mb-4" />
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Access Not Configured</h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
          Your account doesn't have a role assigned to access the dashboard.
        </p>
        <UButton to="/account/general" color="primary">
          Go to Account
        </UButton>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
const authStore = useAuthStore();
const router = useRouter();

// Ensure authStore is reactive
const user = computed(() => authStore.user);

// Determine if the user role is admin
const isAdminUser = computed(() => {
  return user.value?.roles?.includes("admin");
});

// Determine if the user role is junkshop_owner
const isJunkshopOwnerUser = computed(() => {
  return user.value?.roles?.includes("junkshop_owner");
});

// Determine if the user role is merchant
const isMerchantUser = computed(() => {
  return user.value?.roles?.includes("merchant");
});

// Determine if the user role is user
const isUser = computed(() => {
  return user.value?.roles?.includes("user");
});

// If the role is user, redirect to /account/general
onMounted(() => {
  if (isUser.value === true) {
    router.push("/account/general");
  }
});

const scrollY = ref(0);

const handleScroll = () => {
  scrollY.value = window.scrollY;
};

onMounted(() => {
  window.addEventListener("scroll", handleScroll);
});

onUnmounted(() => {
  window.removeEventListener("scroll", handleScroll);
});
</script>

<style scoped>
.parallax {
  background-image: url("https://images.pexels.com/photos/29431251/pexels-photo-29431251/free-photo-of-mesmerizing-abstract-green-swirl-art-design.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1");
  background-attachment: fixed;
  background-size: cover;
  height: 85vh;
}
</style>
