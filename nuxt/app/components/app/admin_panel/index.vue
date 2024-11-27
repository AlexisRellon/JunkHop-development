<template>
  <div class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Sidebar -->
    <div class="w-64 bg-white shadow-lg dark:bg-gray-800">
      <div class="p-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
          Admin Panel
        </h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
          Manage your users from here.
        </p>
      </div>
      <UDivider />
      <h3 class="px-6 py-2 text-lg font-semibold text-gray-700 dark:text-gray-300">
        Navigation
      </h3>
      <UVerticalNavigation
        :links="links"
        :ui="{
          wrapper: 'border-s border-gray-200 dark:border-gray-800 space-y-2',
          base: 'flex items-center gap-3 h-12 group border-s -ms-px leading-6 before:hidden',
          padding: 'p-0 ps-4',
          rounded: '',
          size: 'text-md',
          ring: '',
          active:
            'hover:bg-teal-600/20 text-primary-600 dark:text-primary-500 border-current font-semibold',
          inactive:
            'hover:bg-gray-100 dark:hover:bg-gray-700 border-gray-300/20 hover:border-primary-500 dark:hover:border-primary-600/50 text-gray-700 hover:text-primary-800 dark:text-gray-400 dark:hover:text-primary-400',
          icon: 'group-hover:text-primary-500 dark:group-hover:text-primary-400',
        }"
      />
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8 overflow-y-auto">
      <!-- Header with Avatar and Dropdown -->
      <header
        class="flex items-center justify-end gap-4 p-4 "
      >
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
        <!-- Card 1 -->
        <UCard
          class="relative p-6 text-white rounded-lg shadow-lg bg-gradient-to-r from-indigo-500 to-indigo-400 dark:from-indigo-600 dark:to-indigo-400"
        >
          <UIcon
            name="mdi-account-group"
            class="absolute h-24 text-indigo-900 top-4 right-4 opacity-20 md:text-6xl"
            style="font-size: 5.5rem"
          />
          <h3 class="text-2xl font-bold">Total Users</h3>
          <p class="mt-4 text-4xl">{{ totalUsers }}</p>
        </UCard>
        <!-- Card 2 -->
        <UCard
          class="relative p-6 text-white rounded-lg shadow-lg bg-gradient-to-r from-teal-500 to-teal-400 dark:from-teal-600 dark:to-teal-400"
        >
          <UIcon
            name="mdi-account-check"
            class="absolute h-24 text-teal-900 top-4 right-4 opacity-20 md:text-6xl"
            style="font-size: 5.5rem"
          />
          <h3 class="text-2xl font-bold">Active Users</h3>
          <p class="mt-4 text-4xl">{{ activeUsers }}</p>
        </UCard>
        <!-- Card 3 -->
        <UCard
          class="relative p-6 text-white rounded-lg shadow-lg bg-gradient-to-r from-amber-500 to-amber-400 dark:from-amber-600 dark:to-amber-400"
        >
          <UIcon
            name="mdi-home"
            class="absolute h-24 top-4 right-4 text-amber-900 opacity-20 md:text-6xl"
            style="font-size: 5.5rem"
          />
          <h3 class="text-2xl font-bold">Total Junkshop Listed</h3>
          <p class="mt-4 text-4xl">{{ totalJunkshops }}</p>
        </UCard>
        <!-- User Table Component -->
        <AdminPanelUserTable />
        <!-- Junkshop Table Component -->
        <AdminPanelJunkshopTable />
      </div>
    </div>
  </div>
</template>

<script setup>
import AdminPanelUserTable from "./admin_panel_user_table.vue";
import AdminPanelJunkshopTable from "./admin_panel_junkshop_table.vue";
import { ref, onMounted, computed } from "vue";
import { useAuthStore } from "@/stores/auth";

const auth = useAuthStore();
const { $storage } = useNuxtApp();

const totalUsers = ref(0);
const activeUsers = ref(0);
const totalJunkshops = ref(0);

const isDarkMode = ref(true);

const userItems = computed(() => [
  [
    {
      label: isDarkMode.value ? "Light Mode" : "Dark Mode",
      icon: "i-heroicons-moon-20-solid",
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
      label: "Go Back",
      to: "/",
      icon: "i-heroicons-arrow-left-on-rectangle",
    },
    {
      label: "Sign out",
      click: auth.logout,
      icon: "i-heroicons-arrow-left-on-rectangle",
    },
  ],
]);

const links = [
  { label: "Dashboard", to: "#", icon: "mdi-view-dashboard" },
  { label: "Users", to: "#", icon: "mdi-account-group" },
  { label: "Junkshops", to: "#", icon: "mdi-home" },
];

const fetchDashboardStatistics = async () => {
  try {
    const data = await $fetch("/dashboard-statistics", {
      method: "GET",
    });
    totalUsers.value = data.statistics.total_users;
    activeUsers.value = data.statistics.online_users;
    totalJunkshops.value = data.statistics.total_junkshops;
    console.log("Dashboard statistics:", data);
  } catch (error) {
    console.error("Error fetching dashboard statistics:", error);
    console.error(
      "Error details:",
      error.response ? error.response.data : error.message
    );
  }
};

onMounted(async () => {
  try {
    await fetchDashboardStatistics();
  } catch (error) {
    console.error("Error during mounted hook:", error);
  }
});
</script>
