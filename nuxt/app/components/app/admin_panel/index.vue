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
                  <div>
                    <p class="text-sm font-medium text-teal-600 dark:text-teal-300">Active Users</p>
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
                class="w-32"
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
            <UButton size="sm" color="primary" variant="soft" to="/dashboard/activity">View All</UButton>
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
        <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-2">
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
                  class="w-24"
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
                  class="w-24"
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
      </div>
    </div>
  </div>
</template>

<script setup>
import AdminSidebar from "./admin_sidebar.vue";
import AdminPanelUserTable from "./admin_panel_user_table.vue";
import AdminPanelJunkshopTable from "./admin_panel_junkshop_table.vue";
import { ref, onMounted, computed, watch } from "vue";
import { useAuthStore } from "@/stores/auth";

const auth = useAuthStore();
const { $storage } = useNuxtApp();

// Sidebar state
const isSidebarCollapsed = ref(false);

const totalUsers = ref(0);
const activeUsers = ref(0);
const totalJunkshops = ref(0);
const totalTransactions = ref(0);
const totalRevenue = ref(0);

const systemHealth = ref('Excellent');
const systemUptime = ref('99.9%');

// Analytics state - keeping only user growth timeframe
const userGrowthTimeframe = ref('monthly');
const timeframeOptions = [
  { label: 'Weekly', value: 'weekly' },
  { label: 'Monthly', value: 'monthly' },
  { label: 'Yearly', value: 'yearly' }
];

// Recent activity
const recentActivities = ref([
  { type: 'user', description: 'New user registered: John Doe', timestamp: '10 minutes ago' },
  { type: 'junkshop', description: 'Green Recycling Junkshop updated their information', timestamp: '1 hour ago' },
  { type: 'transaction', description: 'Transaction #12345 completed', timestamp: '3 hours ago' },
  { type: 'system', description: 'System maintenance scheduled for tomorrow', timestamp: '5 hours ago' },
  { type: 'user', description: 'User Maria Garcia updated their profile', timestamp: '1 day ago' }
]);

const isDarkMode = ref(true);


// Get activity icon and class based on type
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

// User Items dropdown
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

const updateStatistics = () => {
  const userTable = document.querySelectorAll(".user-table-row");
  const junkshopTable = document.querySelectorAll(".junkshop-table-row");
  totalUsers.value = userTable.length || 6;
  totalJunkshops.value = junkshopTable.length || 10;
  // Assuming active users are a subset of total users
  activeUsers.value = userTable.length || 2; // Update this logic as needed
  
  // Placeholder values for new metrics
  totalTransactions.value = 245;
  totalRevenue.value = 127850;
};

// Function to fetch data for the dashboard statistics
const fetchDashboardData = async () => {
  try {
    // Use correct API paths without duplication
    const [usersResponse, junkshopsResponse] = await Promise.all([
      $fetch('/users', {
        headers: {
          Authorization: `Bearer ${auth.token}`
        }
      }),
      $fetch('/junkshop', {
        headers: {
          Authorization: `Bearer ${auth.token}`
        }
      })
    ]);

    // Update statistics based on API responses
    if (Array.isArray(usersResponse)) {
      totalUsers.value = usersResponse.length;
      activeUsers.value = usersResponse.filter(user => user.active).length ||
                           Math.floor(usersResponse.length / 2); // Fallback approximation
      userPagination.value.totalItems = usersResponse.length;
    }

    if (Array.isArray(junkshopsResponse)) {
      totalJunkshops.value = junkshopsResponse.length;
      junkshopPagination.value.totalItems = junkshopsResponse.length;
    }

    console.log('Dashboard data fetched successfully');
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
    // Fallback to DOM-based counting if API fails
    updateStatistics();
  }
};

// Update the onMounted hook to use the API data
onMounted(() => {
  fetchDashboardData();
  // Fallback to DOM method as backup
  updateStatistics();
});

watch([totalUsers, totalJunkshops], () => {
  updateStatistics();
});

// Add pagination controls
const pageSizeOptions = [
  { label: '5', value: 5 },
  { label: '10', value: 10 },
  { label: '25', value: 25 },
  { label: '50', value: 50 },
];

// User table pagination
const userPagination = ref({
  currentPage: 1,
  perPage: 10,
  totalItems: 0
});

// Junkshop table pagination
const junkshopPagination = ref({
  currentPage: 1,
  perPage: 10,
  totalItems: 0
});

// Activity pagination
const activityPagination = ref({
  currentPage: 1,
  itemsPerPage: 3,
  totalItems: 0,
  totalPages: 0,
  startIndex: 0,
  endIndex: 0
});

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

// Update when pagination params change
watch([userPagination, junkshopPagination], () => {
  updateStatistics();
}, { deep: true });

// Reset page when page size changes
watch(() => userPagination.value.perPage, () => {
  userPagination.value.currentPage = 1;
});

watch(() => junkshopPagination.value.perPage, () => {
  junkshopPagination.value.currentPage = 1;
});
</script>

<style scoped>
/* Add subtle animations to cards for a more interactive feel */
.u-card {
  transition: all 0.3s ease;
}

.u-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Custom scrollbar class for specific components */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

/* Smooth pagination transitions */
.u-pagination {
  transition: all 0.2s ease;
}

/* Page size dropdown styling */
.page-size-select {
  min-width: 70px;
}
</style>
