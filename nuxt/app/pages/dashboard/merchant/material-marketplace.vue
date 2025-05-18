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
      <!-- Bid Cards -->
      <UCard
        v-for="bid in marketplaceListings" 
        :key="bid.ulid"
        class="dark:bg-gray-800 transition-all hover:border-teal-500"
      >
        <template #header>
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold">{{ bid.item?.name }}</h3>
            <UBadge v-if="bid.is_bidding_enabled" color="amber">Bidding Open</UBadge>
            <UBadge v-else color="emerald">Verified</UBadge>
          </div>
        </template>
        
        <div class="space-y-2">
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Junkshop:</span>
            <span class="font-medium">{{ bid.junkshop?.name }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Available Quantity:</span>
            <span class="font-medium">{{ bid.quantity }}</span>
          </div>
          <div class="flex justify-between" v-if="bid.is_bidding_enabled">
            <span class="text-gray-600 dark:text-gray-400">Starting Bid:</span>
            <span class="font-medium text-green-600 dark:text-green-400">₱{{ formatPrice(bid.starting_bid) }}</span>
          </div>
          <div class="flex justify-between" v-if="bid.is_bidding_enabled && bid.current_bid">
            <span class="text-gray-600 dark:text-gray-400">Current Bid:</span>
            <span class="font-medium text-amber-600 dark:text-amber-400">₱{{ formatPrice(bid.current_bid) }}</span>
          </div>
          <div class="flex justify-between" v-if="!bid.is_bidding_enabled">
            <span class="text-gray-600 dark:text-gray-400">Price:</span>
            <span class="font-medium text-green-600 dark:text-green-400">₱{{ formatPrice(bid.price_per_kg) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Grade:</span>
            <span class="font-medium">{{ bid.grade || 'Any' }}</span>
          </div>
          <div class="flex justify-between" v-if="bid.is_bidding_enabled">
            <span class="text-gray-600 dark:text-gray-400">Bidding Period:</span>
            <span class="font-medium">{{ formatDate(bid.start_date) }} - {{ formatDate(bid.end_date) }}</span>
          </div>
          <div class="flex justify-between" v-if="bid.is_bidding_enabled && bid.formatted_remaining_time">
            <span class="text-gray-600 dark:text-gray-400">Time Remaining:</span>
            <span class="font-medium text-amber-600">{{ bid.formatted_remaining_time }}</span>
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
              v-if="bid.is_bidding_enabled && isBiddingActive(bid)"
              color="amber"
              variant="soft"
              icon="i-heroicons-currency-dollar"
              size="sm"
              @click="openBidModal(bid)"
            >
              Place Bid
            </UButton>
            
            <UButton
              v-else
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
  <UModal v-model="showDetailsModal" :ui="{ width: 'w-2/3 sm:max-w-lg' }">
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
            <div class="font-medium">{{ selectedBid.quantity }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Starting Bid</div>
            <div class="font-medium text-green-600 dark:text-green-400">₱{{ formatPrice(selectedBid.starting_bid || selectedBid.price_per_kg) }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Current Bid</div>
            <div class="font-medium text-amber-600 dark:text-amber-400">
              {{ selectedBid.current_bid ? '₱' + formatPrice(selectedBid.current_bid) : 'No bids yet' }}
            </div>
          </div>
          <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Grade</div>
            <div class="font-medium">{{ selectedBid.grade || 'Any' }}</div>
          </div>
          <div v-if="selectedBid.is_bidding_enabled">
            <div class="text-sm text-gray-500 dark:text-gray-400">Bidding Start Date</div>
            <div class="font-medium">{{ formatDate(selectedBid.start_date, true) || 'Not specified' }}</div>
          </div>
          <div v-if="selectedBid.is_bidding_enabled">
            <div class="text-sm text-gray-500 dark:text-gray-400">Bidding End Date</div>
            <div class="font-medium">{{ formatDate(selectedBid.end_date, true) || 'Not specified' }}</div>
          </div>
          <div v-if="selectedBid.is_bidding_enabled">
            <div class="text-sm text-gray-500 dark:text-gray-400">Bidding Status</div>
            <div class="font-medium">
              <UBadge :color="getBiddingStatusColor(selectedBid)">{{ getBiddingStatusText(selectedBid) }}</UBadge>
            </div>
          </div>
          <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Total Value</div>
            <div class="font-medium text-green-600 dark:text-green-400">₱{{ formatPrice(selectedBid.quantity * (selectedBid.starting_bid || selectedBid.price_per_kg)) }}</div>
          </div>
        </div>
        
        <div v-if="selectedBid.notes">
          <div class="text-sm text-gray-500 dark:text-gray-400">Notes</div>
          <p class="text-gray-800 dark:text-gray-300 mt-1">{{ selectedBid.notes }}</p>
        </div>
        
        <!-- Bid History Section -->
        <div v-if="selectedBid.is_bidding_enabled && bidHistory.length > 0" class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
          <div class="flex justify-between items-center mb-3">
            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Bid History</div>
            <UBadge color="amber">{{ bidHistory.length }} bids</UBadge>
          </div>
          
          <div class="max-h-48 overflow-y-auto bg-gray-50 dark:bg-gray-700/50 rounded-lg pr-1">
            <!-- Timeline view for bid history -->
            <div class="relative pl-8 py-2">
              <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-amber-300 dark:bg-amber-600"></div>
              
              <div v-for="(historyItem, index) in bidHistory" :key="index" 
                   class="relative mb-2 last:mb-0">
                <!-- Timeline node -->
                <div class="absolute left-[-1.25rem] top-2 w-4 h-4 rounded-full border-2 border-amber-400 dark:border-amber-600" 
                     :class="{'bg-amber-400 dark:bg-amber-600': historyItem.is_current_user, 'bg-white dark:bg-gray-800': !historyItem.is_current_user}">
                </div>
                
                <!-- Bid content -->
                <div class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm">
                  <div class="flex justify-between items-center">
                    <div class="flex items-center">
                      <UAvatar 
                        :ui="{ring: historyItem.is_current_user ? 'amber-500 dark:amber-400' : 'gray-300 dark:gray-600'}"
                        size="sm"
                        class="mr-2"
                        :src="null"
                        :alt="historyItem.merchant_name">
                        <UIcon name="i-heroicons-user-circle" class="text-gray-500" />
                      </UAvatar>
                      <div>
                        <div class="text-sm font-medium flex items-center gap-1">
                          {{ historyItem.merchant_name }}
                          <span v-if="historyItem.is_current_user" 
                                class="text-xs py-0.5 px-1 bg-teal-100 dark:bg-teal-900 text-teal-600 dark:text-teal-300 rounded">You</span>
                        </div>
                        <div class="text-xs text-gray-500">
                          {{ formatDate(historyItem.created_at, true) }}
                        </div>
                      </div>
                    </div>
                    <div class="text-sm font-medium text-green-600 dark:text-green-400">
                      ₱{{ formatPrice(historyItem.bid_amount) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div v-if="isBiddingActive(selectedBid)" class="mt-3 flex justify-center">
            <UButton 
              color="amber" 
              @click="openBidModal(selectedBid)"
              size="sm"
              icon="i-heroicons-hand-raised"
            >
              Place a New Bid
            </UButton>
          </div>
        </div>
        
        <div v-if="selectedBid.is_bidding_enabled && selectedBid.current_bidder_id" class="border-t border-gray-200 dark:border-gray-700 pt-4">
          <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Current Highest Bid</div>
          <div class="bg-amber-50 dark:bg-amber-900/30 p-3 rounded-lg">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Bidder</div>
                <div class="font-medium">{{ selectedBid.current_bidder_id === auth?.user?.ulid ? 'You' : 'Another merchant' }}</div>
              </div>
              <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Bid Amount</div>
                <div class="font-medium text-amber-600 dark:text-amber-400">₱{{ formatPrice(selectedBid.current_bid) }}</div>
              </div>
            </div>
          </div>
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
            v-if="selectedBid.is_bidding_enabled && isBiddingActive(selectedBid)"
            color="amber"
            icon="i-heroicons-currency-dollar"
            @click="openBidModal(selectedBid)"
          >
            Place Bid
          </UButton>
          
          <UButton
            v-else
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
  
  <!-- Place Bid Modal -->
  <UModal v-model="showBidModal" :ui="{ width: 'sm:max-w-md' }">
    <UCard v-if="bidTarget">
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-base font-semibold text-gray-900 dark:text-white">
            Place Bid on {{ bidTarget.item?.name }}
          </h3>
          <UButton color="gray" variant="ghost" icon="i-heroicons-x-mark" class="-my-1" @click="showBidModal = false" />
        </div>
      </template>
      
      <div class="space-y-4">
        <div class="bg-amber-50 dark:bg-amber-900/30 p-3 rounded-lg">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <div class="text-xs text-gray-500 dark:text-gray-400">Item</div>
              <div class="text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ bidTarget.item?.name }}
              </div>
            </div>
            <div>
              <div class="text-xs text-gray-500 dark:text-gray-400">Junkshop</div>
              <div class="text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ bidTarget.junkshop?.name }}
              </div>
            </div>
            <div>
              <div class="text-xs text-gray-500 dark:text-gray-400">Starting Bid</div>
              <div class="text-sm font-medium text-gray-700 dark:text-gray-200">
                ₱{{ formatPrice(bidTarget.starting_bid || bidTarget.price_per_kg) }}
              </div>
            </div>
            <div>
              <div class="text-xs text-gray-500 dark:text-gray-400">Current Highest Bid</div>
              <div class="text-sm font-medium text-amber-600 dark:text-amber-400">
                {{ bidTarget.current_bid ? '₱' + formatPrice(bidTarget.current_bid) : 'No bids yet' }}
              </div>
            </div>
            <div>
              <div class="text-xs text-gray-500 dark:text-gray-400">Minimum Bid</div>
              <div class="text-sm font-medium text-gray-700 dark:text-gray-200">
                ₱{{ formatPrice(calculateMinimumBid(bidTarget)) }}
              </div>
            </div>
            <div>
              <div class="text-xs text-gray-500 dark:text-gray-400">Ends In</div>
              <div class="text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ daysUntilEnd(bidTarget.end_date) }} days
              </div>
            </div>
          </div>
        </div>
        
        <UFormGroup label="Your Bid Amount (₱)" required>
          <UInput 
            v-model="bidAmount" 
            type="number" 
            :min="calculateMinimumBid(bidTarget)" 
            step="0.01" 
            placeholder="Enter your bid amount"
            icon="i-heroicons-currency-dollar" 
            @input="validateBidAmount"
          />
          <div v-if="bidError" class="text-xs text-red-500 mt-1">{{ bidError }}</div>
        </UFormGroup>
        
        <UFormGroup label="Notes (Optional)">
          <UTextarea 
            v-model="bidNotes" 
            placeholder="Additional notes or comments" 
            :rows="3" 
          />
        </UFormGroup>
      </div>
      
      <template #footer>
        <div class="flex justify-end gap-2">
          <UButton color="gray" variant="ghost" @click="showBidModal = false">
            Cancel
          </UButton>
          
          <UButton 
            color="amber" 
            :loading="isPlacingBid"
            :disabled="isPlacingBid || !isValidBid || !!bidError"
            @click="submitBid"
          >
            Place Bid
          </UButton>
        </div>
      </template>
    </UCard>
  </UModal>

  <!-- Bid Success Modal -->
  <UModal v-model="showBidSuccessModal">
    <UCard>
      <div class="p-6 text-center">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
          <UIcon name="i-heroicons-check" class="h-6 w-6 text-green-600" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Bid Placed Successfully</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
          Your bid has been registered. You will be notified if you are outbid or if you win the bid.
        </p>
        <div class="flex justify-center">
          <UButton color="green" @click="showBidSuccessModal = false">
            Continue
          </UButton>
        </div>
      </div>
    </UCard>
  </UModal>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';

const toast = useToast();
// const auth = useAuth();

// State variables
const isLoadingMarketplace = ref(true);
const marketplaceListings = ref([]);
const showDetailsModal = ref(false);
const selectedBid = ref(null);
const showBidModal = ref(false);
const bidTarget = ref(null);
const bidAmount = ref(null);
const bidNotes = ref('');
const isPlacingBid = ref(false);
const bidError = ref(null);
const showBidSuccessModal = ref(false);
const bidHistory = ref([]);
const isLoadingBidHistory = ref(false);

// Computed values
const isValidBid = computed(() => {
  if (!bidAmount.value || !bidTarget.value) return false;
  
  const minBid = calculateMinimumBid(bidTarget.value);
  return parseFloat(bidAmount.value) >= minBid;
});

// Filter state
const filters = reactive({
  itemId: null,
  minPrice: null,
  maxPrice: null,
  grade: null,
  biddingOnly: false
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
    fetchMarketplaceListings(),
    fetchItemOptions()
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
    if (filters.biddingOnly) params.append('bidding_only', true);
    
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

// Fetch item options for filter dropdown
const fetchItemOptions = async () => {
  try {
    const items = await $fetch('/items');
    itemOptions.value = items.map(item => ({
      label: item.name,
      value: item.id
    }));
  } catch (error) {
    console.error('Failed to fetch items', error);
  }
};

// Apply filters to marketplace listings
const applyFilters = () => {
  fetchMarketplaceListings();
};

// View bid details
const viewBidDetails = async (bid) => {
  selectedBid.value = bid;
  showDetailsModal.value = true;
  
  // Fetch bid history if this is a bidding item
  if (bid.is_bidding_enabled) {
    await fetchBidHistory(bid.ulid);
  }
};

// Fetch bid history for a specific bid
const fetchBidHistory = async (bidUlid) => {
  try {
    isLoadingBidHistory.value = true;
    bidHistory.value = []; // Reset history
    
    const response = await $fetch(`/bidding/history/${bidUlid}`);
    bidHistory.value = response || [];
  } catch (error) {
    console.error('Failed to fetch bid history', error);
    // Don't show an error toast as this is not critical
  } finally {
    isLoadingBidHistory.value = false;
  }
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

// Check if bidding is active for this bid
const isBiddingActive = (bid) => {
  if (!bid.is_bidding_enabled || !bid.start_date || !bid.end_date) return false;
  
  const now = new Date();
  const startDate = new Date(bid.start_date);
  const endDate = new Date(bid.end_date);
  
  return now >= startDate && now <= endDate;
};

// Get bidding status text
const getBiddingStatusText = (bid) => {
  if (!bid.is_bidding_enabled) return 'Direct Purchase';
  
  const now = new Date();
  const startDate = new Date(bid.start_date);
  const endDate = new Date(bid.end_date);
  
  if (now < startDate) return 'Bidding Not Started';
  if (now > endDate) return 'Bidding Ended';
  return 'Bidding Active';
};

// Get bidding status color
const getBiddingStatusColor = (bid) => {
  if (!bid.is_bidding_enabled) return 'gray';
  
  const now = new Date();
  const startDate = new Date(bid.start_date);
  const endDate = new Date(bid.end_date);
  
  if (now < startDate) return 'blue';
  if (now > endDate) return 'red';
  return 'green';
};

// Calculate days until end date
const daysUntilEnd = (endDateStr) => {
  if (!endDateStr) return 0;
  
  const now = new Date();
  const endDate = new Date(endDateStr);
  const diffTime = endDate - now;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  
  return Math.max(0, diffDays);
};

// Calculate minimum bid amount
const calculateMinimumBid = (bid) => {
  if (!bid) return 0;
  
  const startingBid = bid.starting_bid || bid.price_per_kg;
  const currentBid = bid.current_bid || 0;
  
  // If no bids yet, minimum is the starting bid
  if (currentBid === 0) return startingBid;
  
  // Otherwise, minimum is current bid + 5% increment (JunkHop recommended algorithm)
  return parseFloat(currentBid) * 1.05;
};

// Open bid modal
const openBidModal = (bid) => {
  bidTarget.value = bid;
  bidAmount.value = calculateMinimumBid(bid).toFixed(2);
  bidNotes.value = '';
  bidError.value = null;
  showBidModal.value = true;
};

// Validate bid amount
const validateBidAmount = () => {
  if (!bidTarget.value || !bidAmount.value) {
    bidError.value = null;
    return;
  }
  
  const minBid = calculateMinimumBid(bidTarget.value);
  
  if (parseFloat(bidAmount.value) < minBid) {
    bidError.value = `Bid amount must be at least ₱${minBid.toFixed(2)}`;
  } else {
    bidError.value = null;
  }
};

// Submit bid
const submitBid = async () => {
  if (!isValidBid.value) return;
  
  try {
    isPlacingBid.value = true;
    
    const response = await $fetch(`/bidding/place-bid/${bidTarget.value.ulid}`, {
      method: 'POST',
      body: {
        bid_amount: parseFloat(bidAmount.value),
        notes: bidNotes.value || null
      }
    });
    
    // Show success message
    showBidModal.value = false;
    showBidSuccessModal.value = true;
    
    // Refresh the marketplace listings
    await fetchMarketplaceListings();
    
  } catch (error) {
    console.error('Failed to place bid', error);
    toast.error(error.response?.data?.message || 'Failed to place bid');
  } finally {
    isPlacingBid.value = false;
  }
};

// Format date for display
const formatDate = (dateString, includeTime = false) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  
  if (includeTime) {
    return date.toLocaleDateString('en-US', { 
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }
  
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

// Function to calculate time remaining in a readable format
const calculateTimeRemaining = (endDateStr) => {
  if (!endDateStr) return '';
  
  const now = new Date();
  const endDate = new Date(endDateStr);
  const diffTime = Math.max(0, endDate - now);
  
  const days = Math.floor(diffTime / (1000 * 60 * 60 * 24));
  const hours = Math.floor((diffTime % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  const minutes = Math.floor((diffTime % (1000 * 60 * 60)) / (1000 * 60));
  
  if (days > 0) return `${days}d ${hours}h`;
  if (hours > 0) return `${hours}h ${minutes}m`;
  return `${minutes}m ${Math.floor((diffTime % (1000 * 60)) / 1000)}s`;
};

// Define page metadata
definePageMeta({
  middleware: ['auth', 'role-merchant']
});
</script>
