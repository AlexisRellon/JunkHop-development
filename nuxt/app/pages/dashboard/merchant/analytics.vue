<template>
  <div class="p-6 bg-gray-50 dark:bg-gray-900">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Merchant Analytics</h1>
    
    <!-- Analytics Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <UCard class="bg-white dark:bg-gray-800 border-0 shadow-sm">
        <div class="flex flex-col">
          <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Total Transactions</div>
          <div class="text-2xl font-bold text-gray-800 dark:text-white mb-1">{{ formatNumber(stats.totalTransactions) }}</div>
          <div class="flex items-center text-xs">
            <UBadge :color="stats.transactionsTrend > 0 ? 'green' : 'red'" class="mr-1">
              {{ stats.transactionsTrend > 0 ? '+' : '' }}{{ stats.transactionsTrend }}%
            </UBadge>
            <span class="text-gray-500 dark:text-gray-400">vs. last month</span>
          </div>
        </div>
      </UCard>
      
      <UCard class="bg-white dark:bg-gray-800 border-0 shadow-sm">
        <div class="flex flex-col">
          <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Total Materials Acquired</div>
          <div class="text-2xl font-bold text-gray-800 dark:text-white mb-1">{{ formatNumber(stats.totalMaterials) }} kg</div>
          <div class="flex items-center text-xs">
            <UBadge :color="stats.materialsTrend > 0 ? 'green' : 'red'" class="mr-1">
              {{ stats.materialsTrend > 0 ? '+' : '' }}{{ stats.materialsTrend }}%
            </UBadge>
            <span class="text-gray-500 dark:text-gray-400">vs. last month</span>
          </div>
        </div>
      </UCard>
      
      <UCard class="bg-white dark:bg-gray-800 border-0 shadow-sm">
        <div class="flex flex-col">
          <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Avg. Quality Grade</div>
          <div class="text-2xl font-bold text-gray-800 dark:text-white mb-1">{{ stats.avgQualityGrade }}</div>
          <div class="flex items-center text-xs">
            <UBadge :color="stats.qualityTrend >= 0 ? 'green' : 'red'" class="mr-1">
              {{ stats.qualityTrend > 0 ? '+' : '' }}{{ stats.qualityTrend }}%
            </UBadge>
            <span class="text-gray-500 dark:text-gray-400">vs. last month</span>
          </div>
        </div>
      </UCard>
      
      <UCard class="bg-white dark:bg-gray-800 border-0 shadow-sm">
        <div class="flex flex-col">
          <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Environmental Impact</div>
          <div class="text-2xl font-bold text-gray-800 dark:text-white mb-1">{{ formatNumber(stats.co2Saved) }} kg CO₂</div>
          <div class="flex items-center text-xs">
            <UBadge color="green" class="mr-1">Saved</UBadge>
            <span class="text-gray-500 dark:text-gray-400">through recycling</span>
          </div>
        </div>
      </UCard>
    </div>
    
    <!-- Date Range Selector -->
    <div class="flex flex-wrap items-center gap-4 mb-6">
      <UButtonGroup>
        <UButton 
          v-for="period in datePeriods" 
          :key="period.value" 
          :color="selectedDatePeriod === period.value ? 'teal' : 'gray'"
          @click="selectedDatePeriod = period.value"
          size="sm"
        >
          {{ period.label }}
        </UButton>
      </UButtonGroup>
      
      <UPopover>
        <UButton color="gray" size="sm" icon="i-heroicons-calendar">
          Custom Range
        </UButton>
        <template #content>
          <div class="p-4 w-full max-w-sm">
            <UDatepicker v-model="customDateRange" mode="range" />
            <div class="flex justify-end mt-4">
              <UButton color="teal" size="sm" @click="applyCustomDateRange">Apply</UButton>
            </div>
          </div>
        </template>
      </UPopover>
      
      <div class="ml-auto">
        <UButton color="gray" size="sm" icon="i-heroicons-arrow-down-tray">
          Export Data
        </UButton>
      </div>
    </div>
    
    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Material Price Trends Chart -->
      <UCard class="bg-white dark:bg-gray-800 border-0 shadow-sm">
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Material Price Trends</h3>
            <USelect v-model="selectedMaterial" :options="materialOptions" size="sm" class="w-48" />
          </div>
        </template>
        
        <div v-if="isLoading" class="flex justify-center items-center h-64">
          <UIcon name="i-heroicons-arrow-path" class="animate-spin w-8 h-8 text-teal-500" />
        </div>
        <div v-else class="h-64">
          <PriceChart :data="priceData" />
        </div>
      </UCard>
      
      <!-- Material Acquisition by Type -->
      <UCard class="bg-white dark:bg-gray-800 border-0 shadow-sm">
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Material Acquisition by Type</h3>
            <USelect v-model="chartViewType" :options="chartViewOptions" size="sm" class="w-36" />
          </div>
        </template>
        
        <div v-if="isLoading" class="flex justify-center items-center h-64">
          <UIcon name="i-heroicons-arrow-path" class="animate-spin w-8 h-8 text-teal-500" />
        </div>
        <div v-else class="h-64">
          <MaterialChart :data="materialData" :type="chartViewType" />
        </div>
      </UCard>
    </div>
    
    <!-- Additional Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Transaction History Chart -->
      <UCard class="bg-white dark:bg-gray-800 border-0 shadow-sm">
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Transaction History</h3>
            <USelect v-model="transactionInterval" :options="intervalOptions" size="sm" class="w-36" />
          </div>
        </template>
        
        <div v-if="isLoading" class="flex justify-center items-center h-64">
          <UIcon name="i-heroicons-arrow-path" class="animate-spin w-8 h-8 text-teal-500" />
        </div>
        <div v-else class="h-64">
          <TransactionChart :data="transactionData" :interval="transactionInterval" />
        </div>
      </UCard>
      
      <!-- Environmental Impact Chart -->
      <UCard class="bg-white dark:bg-gray-800 border-0 shadow-sm">
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Environmental Impact</h3>
            <USelect v-model="impactMetric" :options="impactMetricOptions" size="sm" class="w-48" />
          </div>
        </template>
        
        <div v-if="isLoading" class="flex justify-center items-center h-64">
          <UIcon name="i-heroicons-arrow-path" class="animate-spin w-8 h-8 text-teal-500" />
        </div>
        <div v-else class="h-64">
          <ImpactChart :data="impactData" :metric="impactMetric" />
        </div>
      </UCard>
    </div>
    
    <!-- Recent Transactions Table -->
    <UCard class="bg-white dark:bg-gray-800 border-0 shadow-sm">
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Recent Transactions</h3>
          <UButton color="teal" variant="ghost" size="sm" :to="'/dashboard/merchant/transactions'">
            View All
          </UButton>
        </div>
      </template>
      
      <div v-if="isLoading" class="flex justify-center items-center py-8">
        <UIcon name="i-heroicons-arrow-path" class="animate-spin w-8 h-8 text-teal-500" />
      </div>
      
      <UTable v-else :rows="recentTransactions" :columns="transactionColumns">
        <template #date-data="{ row }">
          {{ formatDate(row.date) }}
        </template>
        
        <template #junkshop-data="{ row }">
          <div class="flex items-center">
            <UAvatar 
              :alt="row.junkshop" 
              :src="row.junkshopLogo" 
              size="sm" 
              class="mr-2"
              :fallback="row.junkshop.charAt(0)"
            />
            {{ row.junkshop }}
          </div>
        </template>
        
        <template #material-data="{ row }">
          <div class="flex items-center">
            <UBadge :color="getMaterialColor(row.material)" class="mr-2">
              {{ row.grade }}
            </UBadge>
            {{ row.material }}
          </div>
        </template>
        
        <template #amount-data="{ row }">
          {{ row.quantity }} kg
        </template>
        
        <template #price-data="{ row }">
          ₱{{ formatNumber(row.price) }}
        </template>
        
        <template #status-data="{ row }">
          <UBadge :color="getStatusColor(row.status)">
            {{ row.status }}
          </UBadge>
        </template>
      </UTable>
    </UCard>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import PriceChart from '~/components/app/charts/PriceChart.vue';
import MaterialChart from '~/components/app/charts/MaterialChart.vue';
import TransactionChart from '~/components/app/charts/TransactionChart.vue';
import ImpactChart from '~/components/app/charts/ImpactChart.vue';

const isLoading = ref(true);
const toast = useToast();

// Date range selection
const selectedDatePeriod = ref('30d');
const customDateRange = ref([]);
const datePeriods = [
  { label: '7 Days', value: '7d' },
  { label: '30 Days', value: '30d' },
  { label: '90 Days', value: '90d' },
  { label: 'YTD', value: 'ytd' },
  { label: '1 Year', value: '1y' },
];

// Chart configurations
const selectedMaterial = ref('all');
const materialOptions = [
  { label: 'All Materials', value: 'all' },
  { label: 'Paper', value: 'paper' },
  { label: 'Plastics', value: 'plastic' },
  { label: 'Metals', value: 'metal' },
  { label: 'Glass', value: 'glass' },
  { label: 'Electronics', value: 'electronics' },
];

const chartViewType = ref('pie');
const chartViewOptions = [
  { label: 'Pie Chart', value: 'pie' },
  { label: 'Bar Chart', value: 'bar' },
];

const transactionInterval = ref('monthly');
const intervalOptions = [
  { label: 'Daily', value: 'daily' },
  { label: 'Weekly', value: 'weekly' },
  { label: 'Monthly', value: 'monthly' },
];

const impactMetric = ref('co2');
const impactMetricOptions = [
  { label: 'CO₂ Emissions Saved', value: 'co2' },
  { label: 'Landfill Space Saved', value: 'landfill' },
  { label: 'Water Saved', value: 'water' },
  { label: 'Energy Saved', value: 'energy' },
];

// Stats data
const stats = reactive({
  totalTransactions: 158,
  transactionsTrend: 12.5,
  totalMaterials: 2450,
  materialsTrend: 8.2,
  avgQualityGrade: 'B+',
  qualityTrend: 5.3,
  co2Saved: 1250,
});

// Chart data
const priceData = ref([
  { date: '2025-01-01', price: 12.5 },
  { date: '2025-02-01', price: 12.8 },
  { date: '2025-03-01', price: 13.2 },
  { date: '2025-04-01', price: 15.0 },
  { date: '2025-05-01', price: 14.2 },
  { date: '2025-06-01', price: 14.8 },
  { date: '2025-07-01', price: 16.3 },
]);

const materialData = ref([
  { name: 'Paper', value: 35, color: 'blue' },
  { name: 'Plastics', value: 25, color: 'green' },
  { name: 'Metals', value: 20, color: 'red' },
  { name: 'Glass', value: 15, color: 'purple' },
  { name: 'Electronics', value: 5, color: 'orange' },
]);

const transactionData = ref([
  { date: '2025-01', count: 12, volume: 150 },
  { date: '2025-02', count: 18, volume: 220 },
  { date: '2025-03', count: 22, volume: 310 },
  { date: '2025-04', count: 25, volume: 380 },
  { date: '2025-05', count: 30, volume: 420 },
  { date: '2025-06', count: 28, volume: 380 },
  { date: '2025-07', count: 35, volume: 590 },
]);

const impactData = ref([
  { date: '2025-01', co2: 120, landfill: 10, water: 500, energy: 80 },
  { date: '2025-02', co2: 150, landfill: 12, water: 580, energy: 90 },
  { date: '2025-03', co2: 180, landfill: 15, water: 620, energy: 110 },
  { date: '2025-04', co2: 200, landfill: 18, water: 700, energy: 130 },
  { date: '2025-05', co2: 220, landfill: 20, water: 750, energy: 140 },
  { date: '2025-06', co2: 210, landfill: 19, water: 720, energy: 135 },
  { date: '2025-07', co2: 250, landfill: 22, water: 800, energy: 160 },
]);

// Recent transactions
const transactionColumns = [
  { key: 'date', label: 'Date' },
  { key: 'junkshop', label: 'Junkshop' },
  { key: 'material', label: 'Material' },
  { key: 'amount', label: 'Amount' },
  { key: 'price', label: 'Price' },
  { key: 'status', label: 'Status' },
];

const recentTransactions = ref([
  { 
    id: 1, 
    date: '2025-04-15', 
    junkshop: 'Green Earth Recycling', 
    junkshopLogo: null,
    material: 'Copper Wires', 
    grade: 'A',
    quantity: 25, 
    price: 3750,
    status: 'completed' 
  },
  { 
    id: 2, 
    date: '2025-04-10', 
    junkshop: 'Metro Scrap Buyers', 
    junkshopLogo: null,
    material: 'Aluminum Cans', 
    grade: 'B+',
    quantity: 80, 
    price: 2400,
    status: 'completed' 
  },
  { 
    id: 3, 
    date: '2025-04-05', 
    junkshop: 'Urban Recyclers Inc.', 
    junkshopLogo: null,
    material: 'Mixed Plastics', 
    grade: 'B',
    quantity: 120, 
    price: 1800,
    status: 'completed' 
  },
  { 
    id: 4, 
    date: '2025-04-01', 
    junkshop: 'Green Earth Recycling', 
    junkshopLogo: null,
    material: 'Paper Waste', 
    grade: 'B+',
    quantity: 150, 
    price: 1500,
    status: 'completed' 
  },
  { 
    id: 5, 
    date: '2025-03-25', 
    junkshop: 'Metro Scrap Buyers', 
    junkshopLogo: null,
    material: 'Steel Scraps', 
    grade: 'A',
    quantity: 50, 
    price: 2250,
    status: 'completed' 
  },
]);

// Utility functions
const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num);
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  });
};

const getMaterialColor = (material) => {
  const materialColors = {
    'Copper Wires': 'amber',
    'Aluminum Cans': 'slate',
    'Mixed Plastics': 'blue',
    'Paper Waste': 'yellow',
    'Steel Scraps': 'gray',
    'Glass': 'emerald',
    'Electronics': 'red',
  };
  
  return materialColors[material] || 'teal';
};

const getStatusColor = (status) => {
  const statusColors = {
    'completed': 'green',
    'pending': 'yellow',
    'cancelled': 'red',
    'processing': 'blue'
  };
  
  return statusColors[status] || 'gray';
};

const applyCustomDateRange = () => {
  if (customDateRange.value && customDateRange.value.length === 2) {
    selectedDatePeriod.value = 'custom';
    loadData();
  }
};

// API functions
const loadData = async () => {
  try {
    isLoading.value = true;
    
    // In a production environment, these would be API calls
    // Example: const response = await $fetch('/api/v1/merchant/analytics', { params: { period: selectedDatePeriod.value } });
    
    // Simulate API delay for demo
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    // For demo purposes, we'll just use the static data defined above
    isLoading.value = false;
  } catch (error) {
    console.error('Error loading analytics data:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to load analytics data. Please try again.',
      color: 'red'
    });
    isLoading.value = false;
  }
};

// Watch for changes to reload data
watch([selectedDatePeriod, selectedMaterial, chartViewType, transactionInterval, impactMetric], () => {
  if (selectedDatePeriod.value !== 'custom') {
    loadData();
  }
});

onMounted(() => {
  loadData();
});

// Set page metadata
definePageMeta({
  layout: 'dashboard',
  title: 'Merchant Analytics'
});
</script>
