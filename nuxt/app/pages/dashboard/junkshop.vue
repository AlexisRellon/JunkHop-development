<template>
  <div class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Sidebar Component -->
    <AdminSidebar v-model="isSidebarCollapsed" />

    <!-- Main Content -->
    <div class="flex-1 p-8 overflow-y-auto">
      <!-- Header with Avatar and Dropdown -->
      <header class="flex items-center justify-end gap-4 p-4">
        <p>Welcome, {{ auth.user.name }}</p>
        <UDropdown
          :items="userItems"
          :ui="{ item: { disabled: 'cursor-text select-text' } }"
          :popper="{ placement: 'bottom-end' }"
        >
          <UAvatar
            size="sm"
            :src="$storage(auth.user.avatar)"
            :alt="auth.user.name"
            :ui="{ rounded: 'rounded-md' }"
          />
        </UDropdown>
      </header>
      <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        <!-- User Table Component -->
        <AppAdminPanelJunkshopTable />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { useAuthStore } from "@/stores/auth";
import AdminSidebar from "../../components/app/admin_panel/admin_sidebar.vue";

const auth = useAuthStore();
const { $storage } = useNuxtApp();

// Sidebar state - will be managed by the AdminSidebar component
const isSidebarCollapsed = ref(false);

const totalUsers = ref(0);
const activeUsers = ref(0);
const totalJunkshops = ref(0);

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

        router.push("/");
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

const links = [
  { label: "Dashboard", to: "/dashboard", icon: "mdi-view-dashboard" },
  { label: "Users", to: "/dashboard/users", icon: "mdi-account-group" },
  { label: "Junkshops", to: "/dashboard/junkshop", icon: "mdi-home" },
  { label: "API Documentation", to: "/dashboard/api", icon: "mdi-api" },
];

const updateStatistics = () => {
  const userTable = document.querySelectorAll(".user-table-row");
  const junkshopTable = document.querySelectorAll(".junkshop-table-row");
  totalUsers.value = userTable.length;
  totalJunkshops.value = junkshopTable.length;
  // Assuming active users are a subset of total users
  activeUsers.value = userTable.length; // Update this logic as needed
};

onMounted(() => {
  updateStatistics();
});

watch([totalUsers, totalJunkshops], () => {
  updateStatistics();
});
</script>
