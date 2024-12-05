<template>
  <div class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Sidebar -->
    <div
      class="flex flex-col justify-between w-64 py-8 bg-white shadow-lg dark:bg-gray-800"
    >
      <div>
        <div class="p-6">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
            Admin Panel
          </h1>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Manage your users from here.
          </p>
        </div>
        <UDivider />
        <h3
          class="px-6 py-2 text-lg font-semibold text-gray-700 dark:text-gray-300"
        >
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
      <UButton
        class="flex items-center gap-2 px-6 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300"
        icon="i-heroicons-arrow-left-on-rectangle"
        to="/"
        label="Go Back"
        :trailing="false"
        color="teal"
        variant="ghost"
        size="lg"
        :class="{'rounded-none hover:bg-gray-200 dark:hover:bg-gray-700' : true}"
      />
    </div>

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
        <AppAdminPanelUserTable />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
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
