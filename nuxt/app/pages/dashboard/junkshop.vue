<template>
  <div class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Sidebar Component -->
    <AdminSidebar v-model="isSidebarCollapsed" />

    <!-- Main Content -->
    <div class="flex flex-col flex-1 overflow-hidden">
      <!-- Header with Avatar and Breadcrumb -->
      <header class="flex items-center justify-between px-6 py-4 bg-white dark:bg-gray-800 shadow-sm">
        <div class="flex items-center">
          <h1 class="text-xl font-semibold text-gray-800 dark:text-white">Junkshop Management</h1>
          <nav class="ml-4">
            <ol class="flex text-sm">
              <li class="flex items-center">
                <NuxtLink to="/dashboard" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">Dashboard</NuxtLink>
                <UIcon name="i-heroicons-chevron-right" class="w-4 h-4 mx-2 text-gray-400" />
              </li>
              <li>
                <span class="text-gray-700 dark:text-gray-300">Junkshops</span>
              </li>
            </ol>
          </nav>
        </div>
        <div class="flex items-center gap-4">
          <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Welcome, {{ auth.user.name }}</span>
          <UAvatar 
            size="sm" 
            :src="$storage(auth.user.avatar)" 
            :alt="auth.user.name" 
            :ui="{ rounded: 'rounded-full', ring: 'ring-2 ring-teal-500' }" 
          />
        </div>
      </header>

      <div class="flex-1 p-6 overflow-y-auto">
        <!-- Main content area that will take remaining height -->
        <div class="h-full">
          <AppAdminPanelJunkshopTable />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useAuthStore } from "@/stores/auth";
import AdminSidebar from "../../components/app/admin_panel/admin_sidebar.vue";

const auth = useAuthStore();
const { $storage } = useNuxtApp();

// Sidebar state
const isSidebarCollapsed = ref(false);
const isDarkMode = ref(true);

const userItems = computed(() => [
  [
    {
      label: !isDarkMode.value ? "Light Mode" : "Dark Mode",
      icon: isDarkMode.value ? "i-heroicons-moon-20-solid" : "i-heroicons-sun-20-solid",
      type: "checkbox",
      checked: isDarkMode.value,
      click: () => {
        isDarkMode.value = !isDarkMode.value;
        document.documentElement.classList.toggle("dark");
      },
    },
  ],
  [
    {
      label: "Sign out",
      click: auth.logout,
      icon: "i-heroicons-arrow-left-on-rectangle",
    },
  ],
]);
</script>

<style scoped>
/* Ensure the UCard takes full height of its container */
:deep(.u-card) {
  height: 100%;
  display: flex;
  flex-direction: column;
}

/* Ensure the table container takes up remaining space */
:deep(.u-card > div) {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}
</style>
