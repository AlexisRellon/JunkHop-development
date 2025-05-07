<template>
  <div>
    <!-- Activity List -->
    <UCard class="p-4">
      <div class="mb-4 flex justify-between items-center">
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2">
            <UInput
              v-model="searchQuery"
              icon="i-heroicons-magnifying-glass"
              placeholder="Search activities..."
              @input="handleSearch"
            />
          </div>
          <USelect
            v-model="filterType"
            :options="activityTypes"
            placeholder="Filter by type"
            size="sm"
            class="relative w-40"
          />
        </div>
        <USelect
          v-model="pagination.perPage"
          :options="pageSizeOptions"
          size="sm"
          class="relative w-24"
        />
      </div>

      <ul class="divide-y divide-gray-200 dark:divide-gray-700">
        <li v-for="(activity, index) in paginatedActivities" :key="index" class="py-4">
          <div class="flex items-start gap-4">
            <div class="p-2 rounded-full" :class="getActivityIconClass(activity.type)">
              <UIcon :name="getActivityIcon(activity.type)" class="text-white" />
            </div>
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ activity.description }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ activity.timestamp }}</p>
            </div>
          </div>
        </li>
      </ul>

      <!-- Empty State -->
      <div v-if="!paginatedActivities.length" class="py-12 text-center">
        <UIcon name="i-heroicons-inbox" class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No activities found</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          {{ searchQuery ? 'Try adjusting your search or filter' : 'Activities will appear here' }}
        </p>
      </div>

      <!-- Pagination -->
      <div v-if="activities.length" class="mt-4 flex justify-between items-center pt-3 border-t border-gray-200 dark:border-gray-700">
        <div class="text-sm text-gray-500 dark:text-gray-400">
          Showing {{ pagination.startIndex + 1 }} to {{ Math.min(pagination.endIndex + 1, filteredActivities.length) }} of {{ filteredActivities.length }}
        </div>
        <div class="flex space-x-2">
          <UButton
            size="xs"
            color="gray"
            :disabled="pagination.currentPage <= 1"
            @click="pagination.currentPage--"
            icon="i-heroicons-chevron-left"
          />
          <UButton
            size="xs"
            color="gray"
            :disabled="pagination.currentPage >= pagination.totalPages"
            @click="pagination.currentPage++"
            icon="i-heroicons-chevron-right"
          />
        </div>
      </div>
    </UCard>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useNuxtApp } from '#app';

const auth = useAuthStore();
const nuxtApp = useNuxtApp();
const toast = useToast();

// State
const activities = ref([]);
const searchQuery = ref('');
const filterType = ref(''); // Initialize as empty string instead of null
const loading = ref(false);

// Pagination
const pagination = ref({
  currentPage: 1,
  perPage: 10,
  totalItems: 0,
  totalPages: 0,
  startIndex: 0,
  endIndex: 0
});

const pageSizeOptions = [
  { label: '10', value: 10 },
  { label: '25', value: 25 },
  { label: '50', value: 50 },
  { label: '100', value: 100 }
];

const activityTypes = [
  { label: 'All', value: '' },
  { label: 'User', value: 'user' },
  { label: 'Junkshop', value: 'junkshop' },
  { label: 'Merchant', value: 'merchant' },
  { label: 'Transaction', value: 'transaction' },
  { label: 'Security', value: 'security' },
  { label: 'Data', value: 'data' },
  { label: 'Email', value: 'email' },
  { label: 'Performance', value: 'performance' },
  { label: 'Admin', value: 'admin' },
  { label: 'System', value: 'system' }
];

// Computed
const filteredActivities = computed(() => {
  let result = activities.value;

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(activity => 
      activity.description.toLowerCase().includes(query)
    );
  }  // Only filter by type if a specific type is selected (not 'All')
  if (filterType.value && filterType.value !== '') {
    result = result.filter(activity => 
      activity.type === filterType.value
    );
  }

  return result;
});

const paginatedActivities = computed(() => {
  const start = (pagination.value.currentPage - 1) * pagination.value.perPage;
  const end = start + pagination.value.perPage;
  
  pagination.value.startIndex = start;
  pagination.value.endIndex = end - 1;
  pagination.value.totalItems = filteredActivities.value.length;
  pagination.value.totalPages = Math.ceil(filteredActivities.value.length / pagination.value.perPage);
  
  return filteredActivities.value.slice(start, end);
});

// Methods
const fetchActivities = async () => {
  try {
    loading.value = true;
    const response = await $fetch('/admin/activities', {
      headers: {
        Authorization: `Bearer ${auth.token}`
      }
    });

    if (response.success && response.data) {
      activities.value = response.data;
    }
  } catch (error) {
    console.error('Error fetching activities:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to load activities',
      color: 'red'
    });
  } finally {
    loading.value = false;
  }
};

// Start auto-refresh when component is mounted
onMounted(() => {
  // Initial fetch
  fetchActivities();

  // Set up auto-refresh every 30 seconds
  const refreshInterval = setInterval(fetchActivities, 30000);

  // Clean up interval on component unmount
  onUnmounted(() => {
    clearInterval(refreshInterval);
  });
});

const handleSearch = () => {
  pagination.value.currentPage = 1;
};

const getActivityIcon = (type) => {
  switch (type) {
    case 'user': return 'i-heroicons-user-circle';
    case 'junkshop': return 'i-heroicons-home';
    case 'merchant': return 'i-heroicons-shopping-bag';
    case 'transaction': return 'i-heroicons-receipt-percent';
    case 'security': return 'i-heroicons-shield-check';
    case 'data': return 'i-heroicons-database';
    case 'email': return 'i-heroicons-envelope';
    case 'performance': return 'i-heroicons-chart-bar';
    case 'admin': return 'i-heroicons-cog-6-tooth';
    case 'system': return 'i-heroicons-cog';
    default: return 'i-heroicons-question-mark-circle';
  }
};

const getActivityIconClass = (type) => {
  switch (type) {
    case 'user': return 'bg-indigo-500';
    case 'junkshop': return 'bg-amber-500';
    case 'merchant': return 'bg-emerald-500';
    case 'transaction': return 'bg-violet-500';
    case 'security': return 'bg-red-500';
    case 'data': return 'bg-cyan-500';
    case 'email': return 'bg-orange-500';
    case 'performance': return 'bg-yellow-500';
    case 'admin': return 'bg-purple-500';
    case 'system': return 'bg-blue-500';
    default: return 'bg-gray-500';
  }
};

// Watch
watch([filterType, () => pagination.value.perPage], () => {
  pagination.value.currentPage = 1;
});

// Initial fetch
onMounted(() => {
  fetchActivities();
});
</script>
