<template>
  <div class="p-8">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Material Marketplace</h1>
      <p class="text-gray-600 dark:text-gray-400">Browse verified material listings from junkshops</p>
    </div>

    <!-- Filter Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
      <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Filter Materials</h3>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <UFormGroup label="Material Type">
          <USelect
            v-model="filters.itemId"
            :options="itemOptions"
            placeholder="All materials"
            option-attribute="label"
            value-attribute="value"
          />
        </UFormGroup>
        
        <UFormGroup label="Grade">
          <USelect
            v-model="filters.grade"
            :options="gradeOptions"
            placeholder="Any grade"
          />
        </UFormGroup>

        <UFormGroup label="Price Range">
          <div class="flex gap-2">
            <UInput
              v-model="filters.minPrice"
              type="number"
              placeholder="Min ₱"
              min="0"
            />
            <UInput
              v-model="filters.maxPrice"
              type="number"
              placeholder="Max ₱"
              min="0"
            />
          </div>
        </UFormGroup>
      </div>
      
      <div class="flex justify-end mt-4">
        <UButton @click="applyFilters" color="teal" icon="i-heroicons-funnel" size="sm">
          Apply Filters
        </UButton>
      </div>
    </div>

    <!-- Listings Grid -->
    <div v-if="isLoadingMarketplace" class="py-12 flex justify-center">
      <UIcon name="i-heroicons-arrow-path" class="animate-spin text-teal-500 w-8 h-8" />
    </div>
    
    <div v-else-if="marketplaceListings.length === 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
      <div class="mb-4 bg-blue-100 dark:bg-blue-900/30 rounded-full inline-flex p-4">
        <UIcon name="i-heroicons-shopping-bag" class="text-blue-500 w-8 h-8" />
      </div>
      <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">No Verified Bids Available</h3>
      <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">
        There are currently no verified material bids available. Try adjusting your filters or check back later.
      </p>
    </div>
    
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <UCard
        v-for="bid in marketplaceListings" 
        :key="bid.ulid"
        class="dark:bg-gray-800 transition-all hover:border-teal-500"
      >
        <template #header>
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold">{{ bid.item?.name }}</h3>
            <UBadge color="emerald">Verified</UBadge>
          </div>
        </template>
        
        <div class="space-y-2">
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Junkshop:</span>
            <span class="font-medium">{{ bid.junkshop?.name }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Available Quantity:</span>
            <span class="font-medium">{{ bid.quantity }} kg</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Price:</span>
            <span class="font-medium text-green-600 dark:text-green-400">₱{{ formatPrice(bid.price_per_kg) }} / kg</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Grade:</span>
            <span class="font-medium">{{ bid.grade || 'Any' }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Verified On:</span>
            <span class="font-medium">{{ formatDate(bid.accepted_at) }}</span>
          </div>
          <div v-if="bid.notes" class="mt-3">
            <div class="text-gray-600 dark:text-gray-400 text-sm">Notes:</div>
            <p class="text-gray-800 dark:text-gray-300 text-sm mt-1">{{ bid.notes }}</p>
          </div>
        </div>
        
        <template #footer>
          <div class="flex justify-between">
            <UButton
              color="blue"
              variant="ghost"
              icon="i-heroicons-information-circle"
              size="sm"
              @click="viewBidDetails(bid)"
            >
              View Details
            </UButton>
            
            <UButton
              color="teal"
              variant="soft"
              icon="i-heroicons-phone"
              size="sm"
              @click="contactJunkshop(bid)"
            >
              Contact
            </UButton>
          </div>
        </template>
      </UCard>
    </div>
  </div>
  
  <!-- Bid Details Modal -->
  <UModal v-model="showDetailsModal" :ui="{ width: 'sm:max-w-lg' }">
    <UCard class="dark:bg-gray-800" v-if="selectedBid">
      <template #header>
        <div class="flex justify-between items-center">
          <h3 class="text-xl font-semibold dark:text-white">Bid Details</h3>
          <UButton
            color="gray" 
            variant="ghost"
            icon="i-heroicons-x-mark"
            @click="showDetailsModal = false"
            size="sm"
            square
          />
        </div>
      </template>
      
      <div class="space-y-4">
        <div>
          <h4 class="text-lg font-medium text-gray-800 dark:text-white">{{ selectedBid.item?.name }}</h4>
          <div class="flex items-center mt-1">
            <UIcon name="i-heroicons-building-storefront" class="text-teal-500 mr-2" />
            <span class="text-gray-600 dark:text-gray-400">{{ selectedBid.junkshop?.name }}</span>
          </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
          <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Available Quantity</div>
            <div class="font-medium">{{ selectedBid.quantity }} kg</div>
          </div>
          <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Price per KG</div>
            <div class="font-medium text-green-600 dark:text-green-400">₱{{ formatPrice(selectedBid.price_per_kg) }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Grade</div>
            <div class="font-medium">{{ selectedBid.grade || 'Any' }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Total Value</div>
            <div class="font-medium text-green-600 dark:text-green-400">₱{{ formatPrice(selectedBid.quantity * selectedBid.price_per_kg) }}</div>
          </div>
        </div>
        
        <div v-if="selectedBid.notes">
          <div class="text-sm text-gray-500 dark:text-gray-400">Notes</div>
          <p class="text-gray-800 dark:text-gray-300 mt-1">{{ selectedBid.notes }}</p>
        </div>
        
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
          <div class="text-sm text-gray-500 dark:text-gray-400">Contact Information</div>
          <div class="mt-2">
            <div class="flex items-center">
              <UIcon name="i-heroicons-map-pin" class="text-teal-500 mr-2" />
              <span>{{ selectedBid.junkshop?.address || 'Address not provided' }}</span>
            </div>
            <div class="flex items-center mt-1">
              <UIcon name="i-heroicons-phone" class="text-teal-500 mr-2" />
              <span>{{ selectedBid.junkshop?.contact || 'Contact not provided' }}</span>
            </div>
          </div>
        </div>
      </div>
      
      <template #footer>
        <div class="flex justify-between">
          <UButton
            color="gray"
            variant="ghost"
            @click="showDetailsModal = false"
          >
            Close
          </UButton>
          
          <UButton
            color="teal"
            icon="i-heroicons-phone"
            @click="contactJunkshop(selectedBid)"
          >
            Contact Junkshop
          </UButton>
        </div>
      </template>
    </UCard>
  </UModal>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';

const toast = useToast();

// State variables
const isLoadingMarketplace = ref(true);
const marketplaceListings = ref([]);
const showDetailsModal = ref(false);
const selectedBid = ref(null);

// Filter state
const filters = reactive({
  itemId: null,
  minPrice: null,
  maxPrice: null,
  grade: null
});

// Options for dropdowns
const gradeOptions = [
  { label: 'Any Grade', value: null },
  { label: 'Grade A (Premium)', value: 'A' },
  { label: 'Grade B (Standard)', value: 'B' },
  { label: 'Grade C (Basic)', value: 'C' }
];

// Item options (will be loaded from API)
const itemOptions = ref([]);

// Load data on component mount
onMounted(async () => {
  await Promise.all([
    fetchMarketplaceListings()
  ]);
});

// Fetch marketplace listings
const fetchMarketplaceListings = async () => {
  try {
    isLoadingMarketplace.value = true;
    
    // Build query parameters
    const params = new URLSearchParams();
    if (filters.itemId) params.append('item_id', filters.itemId);
    if (filters.minPrice) params.append('min_price', filters.minPrice);
    if (filters.maxPrice) params.append('max_price', filters.maxPrice);
    if (filters.grade) params.append('grade', filters.grade);
    
    const url = `/marketplace/bids${params.toString() ? `?${params.toString()}` : ''}`;
    const response = await $fetch(url);
    
    marketplaceListings.value = response;
  } catch (error) {
    console.error('Failed to fetch marketplace listings', error);
    toast.error('Failed to load marketplace listings');
  } finally {
    isLoadingMarketplace.value = false;
  }
};

// Apply filters to marketplace listings
const applyFilters = () => {
  fetchMarketplaceListings();
};

// View bid details
const viewBidDetails = (bid) => {
  selectedBid.value = bid;
  showDetailsModal.value = true;
};

// Contact junkshop
const contactJunkshop = (bid) => {
  // Implement contact functionality
  if (!bid.junkshop?.contact) {
    toast.info('Contact information not available');
    return;
  }
  
  // Copy contact to clipboard
  navigator.clipboard.writeText(bid.junkshop.contact).then(() => {
    toast.success('Contact number copied to clipboard');
  });
};

// Format date for display
const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

// Format price with 2 decimal places
const formatPrice = (price) => {
  return Number(price).toFixed(2);
};

// Define page metadata
definePageMeta({
  middleware: ['auth', 'role-merchant']
});
</script>
