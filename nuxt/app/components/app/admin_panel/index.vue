<template>
  <div class="flex h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Sidebar Component -->
    <AdminSidebar v-model="isSidebarCollapsed" />

    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar">
      <!-- Header with Avatar and Dropdown -->
      <header class="flex items-center justify-between px-6 py-4 bg-white dark:bg-gray-800 shadow-sm">
        <h1 class="text-xl font-semibold text-gray-800 dark:text-white">Admin Dashboard</h1>
        <div class="flex items-center gap-4">
          <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Welcome, {{ auth.user.name }}</span>
          <UDropdown
            :items="userItems"
            :ui="{ item: { disabled: 'cursor-text select-text' } }"
            :popper="{ placement: 'bottom-end' }"
          >
            <UAvatar
              size="sm"
              :src="$storage(auth.user.avatar)"
              :alt="auth.user.name"
              :ui="{ rounded: 'rounded-full', ring: 'ring-2 ring-primary-500' }"
            />
          </UDropdown>
        </div>
      </header>

      <div class="p-6">
        <!-- Dashboard Summary Section -->
        <div class="mb-6">
          <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-4">Platform Overview</h2>
          <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <!-- Card 1: Total Users -->
            <UCard
              class="relative overflow-hidden rounded-lg shadow-sm border-0"
            >
              <div class="p-5 bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/30 dark:to-indigo-800/30">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-sm font-medium text-indigo-600 dark:text-indigo-300">Total Users</p>
                    <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ totalUsers }}</h3>
                  </div>
                  <div class="p-3 bg-indigo-500 rounded-full bg-opacity-10 w-[48px] h-[48px] flex justify-center items-center dark:bg-opacity-20">
                    <UIcon
                      name="mdi-account-group"
                      class="text-indigo-600 dark:text-indigo-300"
                      size="lg"
                    />
                  </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-indigo-500 dark:text-indigo-300">
                  <UIcon name="i-heroicons-arrow-trending-up" class="mr-1" />
                  <span>Growing steadily</span>
                </div>
              </div>
            </UCard>
            
            <!-- Card 2: Active Users -->
            <UCard
              class="relative overflow-hidden rounded-lg shadow-sm border-0"
            >
              <div class="p-5 bg-gradient-to-br from-teal-50 to-teal-100 dark:from-teal-900/30 dark:to-teal-800/30">
                <div class="flex justify-between items-center">
                  <div class="w-1/2">
                    <div class="flex gap-2 items-center">
                      <p class="text-sm font-medium text-teal-600 dark:text-teal-300 flex gap-3">Active Users </p><p class="text-teal-600 dark:text-teal-300" style="font-size: 0.6rem;">(Last 30 days)</p>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ activeUsers }}</h3>
                  </div>
                  <div class="p-3 bg-teal-500 rounded-full bg-opacity-10 w-[48px] h-[48px] flex justify-center items-center dark:bg-opacity-20">
                    <UIcon
                      name="mdi-account-check"
                      class="text-teal-600 dark:text-teal-300"
                      size="lg"
                    />
                  </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-teal-500 dark:text-teal-300">
                  <UIcon name="i-heroicons-user-circle" class="mr-1" />
                  <span>{{ Math.round((activeUsers / totalUsers) * 100) }}% engagement rate</span>
                </div>
              </div>
            </UCard>
            
            <!-- Card 3: Total Junkshops -->
            <UCard
              class="relative overflow-hidden rounded-lg shadow-sm border-0"
            >
              <div class="p-5 bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/30 dark:to-amber-800/30">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-sm font-medium text-amber-600 dark:text-amber-300">Total Registered Junkshops</p>
                    <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ totalJunkshops }}</h3>
                  </div>
                  <div class="p-3 bg-amber-500 rounded-full bg-opacity-10 w-[48px] h-[48px] flex justify-center items-center dark:bg-opacity-20">
                    <UIcon
                      name="mdi-home"
                      class="text-amber-600 dark:text-amber-300"
                      size="lg"
                    />
                  </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-amber-500 dark:text-amber-300">
                  <UIcon name="i-heroicons-map-pin" class="mr-1" />
                  <span>Across multiple locations</span>
                </div>
              </div>
            </UCard>
 
            <!-- Card 6: System Health -->
            <UCard
              class="relative w-[309.5%] overflow-hidden rounded-lg shadow-sm border-0"
            >
              <div class="p-5 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-sm font-medium text-blue-600 dark:text-blue-300">System Health</p>
                    <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-1">
                      {{ systemHealth === 'Excellent' ? '✅' : systemHealth === 'Good' ? '✅' : '⚠️' }} {{ systemHealth }}
                    </h3>
                  </div>
                  <div class="p-3 bg-blue-500 rounded-full bg-opacity-10 w-[48px] h-[48px] flex justify-center items-center dark:bg-opacity-20">
                    <UIcon
                      name="mdi-server"
                      class="text-blue-600 dark:text-blue-300"
                      size="lg"
                    />
                  </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-blue-500 dark:text-blue-300">
                  <UIcon name="i-heroicons-clock" class="mr-1" />
                  <span>{{ systemUptime }} uptime</span>
                </div>
              </div>
            </UCard>
          </div>
        </div>

        <!-- Analytics Section - Only User Growth Chart -->
        <div class="mb-8">
          <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-4">Analytics</h2>
          <UCard class="p-4">
            <div class="mb-4 flex justify-between items-center">
              <h3 class="text-md font-medium text-gray-700 dark:text-gray-200">User Growth</h3>
              <USelect
                v-model="userGrowthTimeframe"
                :options="timeframeOptions"
                size="sm"
                class="relative w-32"
              />
            </div>
            <div class="h-64">
              <!-- Chart component will go here - using a placeholder for now -->
              <div class="h-full w-full flex items-center justify-center bg-gray-50 dark:bg-gray-800 rounded-lg">
                <p class="text-gray-500 dark:text-gray-400">User Growth Chart</p>
              </div>
            </div>
          </UCard>
        </div>

        <!-- Recent Activity Section -->
        <div class="mb-8">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200">Recent Activity</h2>
            <!-- <UButton size="sm" color="primary" variant="soft" to="/dashboard/activity">View All</UButton> -->
          </div>
          
          <UCard class="p-4">
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
              <li v-for="(activity, index) in paginatedActivities" :key="index" class="py-3">
                <div class="flex items-start gap-4">
                  <div class="p-2 rounded-full" :class="getActivityIconClass(activity.type)">
                    <UIcon :name="getActivityIcon(activity.type)" class="text-white" />
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ activity.description }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ activity.timestamp }}</p>
                  </div>
                </div>
              </li>
            </ul>
            
            <!-- Pagination for Activities -->
            <div class="mt-4 flex justify-between items-center pt-3 border-t border-gray-200 dark:border-gray-700">
              <div class="text-sm text-gray-500 dark:text-gray-400">
                Showing {{ activityPagination.startIndex + 1 }} to {{ Math.min(activityPagination.endIndex + 1, recentActivities.length) }} of {{ recentActivities.length }}
              </div>
              <div class="flex space-x-2">
                <UButton
                  size="xs"
                  color="gray"
                  :disabled="activityPagination.currentPage <= 1"
                  @click="activityPagination.currentPage--"
                  icon="i-heroicons-chevron-left"
                />
                <UButton
                  size="xs"
                  color="gray"
                  :disabled="activityPagination.currentPage >= activityPagination.totalPages"
                  @click="activityPagination.currentPage++"
                  icon="i-heroicons-chevron-right"
                />
              </div>
            </div>
          </UCard>
        </div>

        <!-- Tables Section -->
        <div class="mt-8 grid grid-cols-1 gap-8">
          <!-- User Management Section -->
          <div>
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200">User Management</h2>
              <div class="flex items-center gap-2">
                <UButton size="sm" color="primary" variant="soft" icon="i-heroicons-funnel">Filter</UButton>
                <USelect
                  v-model="userPagination.perPage"
                  :options="pageSizeOptions"
                  size="sm"
                  class="relative w-24"
                />
              </div>
            </div>
            <AdminPanelUserTable 
              :current-page="userPagination.currentPage"
              :per-page="userPagination.perPage"
              @update:total-items="userPagination.totalItems = $event"
            />
          </div>
          
          <!-- Junkshop Management Section -->
          <div>
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200">Junkshop Management</h2>
              <div class="flex items-center gap-2">
                <UButton size="sm" color="primary" variant="soft" icon="i-heroicons-funnel">Filter</UButton>
                <USelect
                  v-model="junkshopPagination.perPage"
                  :options="pageSizeOptions"
                  size="sm"
                  class="relative w-24"
                />
              </div>
            </div>
            <AdminPanelJunkshopTable 
              :current-page="junkshopPagination.currentPage"
              :per-page="junkshopPagination.perPage"
              @update:total-items="junkshopPagination.totalItems = $event"
            />
          </div>
        </div>

        <!-- Bid Management Section -->
        <div class="mt-8">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200">Bid Management</h2>
            <UButton size="sm" color="amber" variant="soft" to="/dashboard/bids">View All Bids</UButton>
          </div>
          <AdminPanelBidManagement />
        </div>
      </div>
    </div>
  </div>
  
  <!-- Logout Confirmation Dialog -->
  <UiConfirmationDialog
    v-model:show="confirmLogout"
    title="Sign Out"
    message="Are you sure you want to sign out?"
    confirm-label="Yes, Sign Out"
    confirm-color="red"
    confirm-icon="i-heroicons-arrow-left-on-rectangle"
    @confirm="auth.logout"
  />
</template>

<script setup>
import AdminSidebar from "./admin_sidebar.vue";
import AdminPanelUserTable from "./admin_panel_user_table.vue";
import AdminPanelJunkshopTable from "./admin_panel_junkshop_table.vue";
import AdminPanelBidManagement from "./admin_panel_bid_management.vue";
import { ref, onMounted, onUnmounted, computed, watch } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useNuxtApp } from '#app';

const confirmLogout = ref(false);

const auth = useAuthStore();
const { $storage } = useNuxtApp();
const nuxtApp = useNuxtApp();
const toast = useToast();

// Sidebar state
const isSidebarCollapsed = ref(false);

// Table pagination controls
const pageSizeOptions = [
  { label: '5', value: 5 },
  { label: '10', value: 10 },
  { label: '25', value: 25 },
  { label: '50', value: 50 },
];

// Pagination states
const userPagination = ref({
  currentPage: 1,
  perPage: 10,
  totalItems: 0
});

const junkshopPagination = ref({
  currentPage: 1,
  perPage: 10,
  totalItems: 0
});

const activityPagination = ref({
  currentPage: 1,
  itemsPerPage: 3,
  totalItems: 0,
  totalPages: 0,
  startIndex: 0,
  endIndex: 0
});

// Dashboard statistics
const totalUsers = ref(0);
const activeUsers = ref(0);
const totalJunkshops = ref(0);
const userGrowthData = ref([]);
const bidStats = ref({
  total: 0,
  pending: 0,
  accepted: 0,
  rejected: 0
});

const systemHealth = ref('Excellent');
const systemUptime = ref('99.9%');

// Analytics state
const userGrowthTimeframe = ref('monthly');
const timeframeOptions = [
  { label: 'Weekly', value: 'weekly' },
  { label: 'Monthly', value: 'monthly' },
  { label: 'Yearly', value: 'yearly' }
];

// Recent activities
const recentActivities = ref([]);

// Function to fetch dashboard data
const fetchDashboardData = async () => {
  try {
    const response = await $fetch('/admin/dashboard/statistics', {
      headers: {
        Authorization: `Bearer ${auth.token}`
      }
    });

    if (response.success && response.data) {
      const { statistics, recentActivities: activities, userGrowth, systemHealth: health } = response.data;
      
      // Update statistics
      if (statistics?.users) {
        totalUsers.value = statistics.users.total;
        activeUsers.value = statistics.users.active;
        userPagination.value.totalItems = statistics.users.total;
      }
      
      if (statistics?.junkshops) {
        totalJunkshops.value = statistics.junkshops.total;
        junkshopPagination.value.totalItems = statistics.junkshops.total;
      }
      
      if (statistics?.bids) {
        bidStats.value = statistics.bids;
      }

      // Update activities
      if (activities) {
        recentActivities.value = activities;
      }

      // Update growth data
      if (userGrowth) {
        userGrowthData.value = userGrowth;
      }

      // Update system health
      if (health) {
        systemHealth.value = health.status;
        systemUptime.value = health.uptime;
      }
    }
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to load dashboard statistics',
      color: 'red'
    });
  }
};

// Watch for pagination changes
watch(
  [
    () => userPagination.value.currentPage,
    () => junkshopPagination.value.currentPage,
    () => userPagination.value.perPage,
    () => junkshopPagination.value.perPage,
  ],
  () => {
    fetchDashboardData();
  }
);

// Computed property for paginated activities
const paginatedActivities = computed(() => {
  const start = (activityPagination.value.currentPage - 1) * activityPagination.value.itemsPerPage;
  const end = start + activityPagination.value.itemsPerPage - 1;
  
  activityPagination.value.startIndex = start;
  activityPagination.value.endIndex = end;
  activityPagination.value.totalItems = recentActivities.value.length;
  activityPagination.value.totalPages = Math.ceil(recentActivities.value.length / activityPagination.value.itemsPerPage);
  
  return recentActivities.value.slice(start, end + 1);
});

// User preferences
const isDarkMode = ref(true);
const userItems = computed(() => [
  [{
    label: !isDarkMode.value ? "Light Mode" : "Dark Mode",
    icon: isDarkMode.value ? "i-heroicons-moon-20-solid" : "i-heroicons-sun-20-solid",
    type: "checkbox",
    checked: isDarkMode.value,
    click: () => {
      isDarkMode.value = !isDarkMode.value;
      document.documentElement.classList.toggle("dark");
    },
  }],
  [{
    label: "Sign out",
    click: () => confirmLogout.value = true,
    icon: "i-heroicons-arrow-left-on-rectangle",
  }],
]);

// Activity icons
const getActivityIcon = (type) => {
  switch (type) {
    case 'user': return 'mdi-account';
    case 'junkshop': return 'mdi-home';
    case 'transaction': return 'mdi-receipt';
    case 'system': return 'mdi-server';
    default: return 'mdi-information';
  }
};

const getActivityIconClass = (type) => {
  switch (type) {
    case 'user': return 'bg-indigo-500';
    case 'junkshop': return 'bg-amber-500';
    case 'transaction': return 'bg-violet-500';
    case 'system': return 'bg-blue-500';
    default: return 'bg-gray-500';
  }
};

// Start auto-refresh when component is mounted
onMounted(() => {
  // Initial fetch
  fetchDashboardData();

  // Set up auto-refresh every 15 minutes (900,000 ms) to optimize API usage costs
  const refreshInterval = setInterval(fetchDashboardData, 900000);
  
  // Also add a manual refresh capability for when users need fresh data
  const isRefreshing = ref(false);
  const manualRefresh = async () => {
    isRefreshing.value = true;
    await fetchDashboardData();
    isRefreshing.value = false;
  };

  // Clean up interval on component unmount
  onUnmounted(() => {
    clearInterval(refreshInterval);
  });
});
</script>

<style scoped>
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 transparent;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 8px;
  background-color: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: #a0aec0;
}
</style>
