<template>
  <div class="p-8 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Price Bidding System</h1>
        <p class="text-gray-600 dark:text-gray-400">Submit and manage bids for bulk materials</p>
      </div>
      
      <UButton 
        color="teal" 
        @click="showNewBidModal = true"
        icon="i-heroicons-currency-dollar"
      >
        New Bid
      </UButton>
    </div>
    
    <!-- Tabs -->
    <UTabs :items="tabs" :default-index="0" @change="onTabChange">
      <template #default="{ selectedTabId }">
        <div class="mt-4">
          <!-- My Bids Tab -->
          <div v-if="selectedTabId === 'my-bids'" class="space-y-6">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6">
              <UCard class="dark:bg-gray-800 border-l-4 border-blue-500">
                <div class="text-center">
                  <div class="text-sm text-gray-500 dark:text-gray-400">Total Bids</div>
                  <div class="text-3xl font-bold text-gray-800 dark:text-white mt-1">
                    {{ bidStats.total_bids || 0 }}
                  </div>
                </div>
              </UCard>
              
              <UCard class="dark:bg-gray-800 border-l-4 border-amber-500">
                <div class="text-center">
                  <div class="text-sm text-gray-500 dark:text-gray-400">Pending</div>
                  <div class="text-3xl font-bold text-gray-800 dark:text-white mt-1">
                    {{ bidStats.pending_bids || 0 }}
                  </div>
                </div>
              </UCard>
              
              <UCard class="dark:bg-gray-800 border-l-4 border-green-500">
                <div class="text-center">
                  <div class="text-sm text-gray-500 dark:text-gray-400">Accepted</div>
                  <div class="text-3xl font-bold text-gray-800 dark:text-white mt-1">
                    {{ bidStats.accepted_bids || 0 }}
                  </div>
                </div>
              </UCard>
              
              <UCard class="dark:bg-gray-800 border-l-4 border-indigo-500">
                <div class="text-center">
                  <div class="text-sm text-gray-500 dark:text-gray-400">Success Rate</div>
                  <div class="text-3xl font-bold text-gray-800 dark:text-white mt-1">
                    {{ bidStats.success_rate || 0 }}%
                  </div>
                </div>
              </UCard>
            </div>
            
            <!-- Filtering Options -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-4">
              <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Filter Bids</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <UFormGroup label="Status">
                  <USelect
                    v-model="filters.status"
                    :options="statusOptions"
                    placeholder="All statuses"
                  />
                </UFormGroup>
                
                <UFormGroup label="Junkshop">
                  <USelect
                    v-model="filters.junkshop_id"
                    :options="junkshopOptions"
                    option-attribute="label"
                    value-attribute="value"
                    placeholder="All junkshops"
                  />
                </UFormGroup>
                
                <UFormGroup label="Material Type">
                  <USelect
                    v-model="filters.item_id"
                    :options="itemOptions"
                    option-attribute="label"
                    value-attribute="value"
                    placeholder="All materials"
                  />
                </UFormGroup>
              </div>
              
              <div class="flex justify-end mt-4">
                <UButton @click="applyFilters" color="teal" icon="i-heroicons-funnel" size="sm">
                  Apply Filters
                </UButton>
              </div>
            </div>
            
            <div v-if="isLoading" class="py-12 flex justify-center">
              <UIcon name="i-heroicons-arrow-path" class="animate-spin text-teal-500 w-8 h-8" />
            </div>
            
            <div v-else-if="myBids.length === 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
              <div class="mb-4 bg-blue-100 dark:bg-blue-900/30 rounded-full inline-flex p-4">
                <UIcon name="i-heroicons-currency-dollar" class="text-blue-500 w-8 h-8" />
              </div>
              <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">No Bids Yet</h3>
              <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
                You haven't submitted any bids yet. Create a bid to offer competitive rates for bulk materials from junkshops.
              </p>
              <UButton 
                color="teal" 
                @click="showNewBidModal = true"
                icon="i-heroicons-currency-dollar"
              >
                Submit Your First Bid
              </UButton>
            </div>
            
            <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-4">
              <UCard
                v-for="bid in myBids" 
                :key="bid.ulid"
                :class="getStatusClass(bid.status)"
                class="dark:bg-gray-800 transition-all"
              >
                <template #header>
                  <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                      {{ bid.item?.name || 'Material' }}
                      <UBadge :color="getStatusColor(bid.status)" class="text-xs">{{ formatStatus(bid.status) }}</UBadge>
                    </h3>
                    <UDropdown :items="getBidActions(bid)">
                      <UButton color="gray" variant="ghost" icon="i-heroicons-ellipsis-vertical" size="xs" />
                    </UDropdown>
                  </div>
                </template>
                
                <div class="space-y-2">
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Junkshop:</span>
                    <span class="font-medium">{{ bid.junkshop?.name || 'Unknown' }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Quantity:</span>
                    <span class="font-medium">{{ bid.quantity }} kg</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Your Offer:</span>
                    <span class="font-medium text-green-600 dark:text-green-400">₱{{ bid.price_per_kg }} / kg</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Total Value:</span>
                    <span class="font-medium text-green-600 dark:text-green-400">₱{{ calculateTotal(bid) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Grade:</span>
                    <span class="font-medium">{{ bid.grade || 'Any' }}</span>
                  </div>
                  <div class="flex justify-between" v-if="bid.expiration_date">
                    <span class="text-gray-600 dark:text-gray-400">Expires:</span>
                    <span :class="isExpiringSoon(bid.expiration_date) ? 'font-medium text-amber-600 dark:text-amber-400' : 'font-medium'">
                      {{ formatDate(bid.expiration_date) }}
                    </span>
                  </div>
                  <div v-if="bid.notes" class="mt-3">
                    <div class="text-gray-600 dark:text-gray-400 text-sm">Notes:</div>
                    <p class="text-gray-800 dark:text-gray-300 text-sm mt-1">{{ bid.notes }}</p>
                  </div>
                </div>
                
                <template #footer>
                  <div class="flex justify-between">
                    <UButton
                      v-if="bid.status === 'pending'"
                      @click="editBid(bid)"
                      color="blue"
                      variant="ghost"
                      icon="i-heroicons-pencil-square"
                      size="sm"
                    >
                      Edit
                    </UButton>
                    
                    <UButton
                      v-if="bid.status === 'pending'"
                      @click="confirmWithdrawBid(bid)"
                      color="red"
                      variant="ghost"
                      icon="i-heroicons-x-mark"
                      size="sm"
                    >
                      Withdraw
                    </UButton>
                    
                    <UButton
                      v-if="bid.status === 'accepted'"
                      color="teal"
                      variant="ghost"
                      icon="i-heroicons-document-text"
                      size="sm"
                    >
                      Generate Invoice
                    </UButton>
                  </div>
                </template>
              </UCard>
            </div>
          </div>
          
          <!-- Received Bids Tab (for Junkshop owners) -->
          <div v-if="selectedTabId === 'received-bids'" class="space-y-6">
            <!-- Junkshop Selection -->
            <UFormGroup label="Select Junkshop" v-if="userJunkshops.length > 1">
              <USelect
                v-model="selectedJunkshop"
                :options="userJunkshops.map(j => ({ label: j.name, value: j.ulid }))"
                option-attribute="label"
                value-attribute="value"
                placeholder="Select a junkshop"
                @change="loadReceivedBids"
              />
            </UFormGroup>
            
            <div v-if="isLoadingReceived" class="py-12 flex justify-center">
              <UIcon name="i-heroicons-arrow-path" class="animate-spin text-teal-500 w-8 h-8" />
            </div>
            
            <div v-else-if="receivedBids.length === 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
              <div class="mb-4 bg-gray-100 dark:bg-gray-700 rounded-full inline-flex p-4">
                <UIcon name="i-heroicons-inbox" class="text-gray-500 w-8 h-8" />
              </div>
              <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">No Bids Received</h3>
              <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">
                You haven't received any bids for your junkshop yet. When merchants submit bids, they will appear here.
              </p>
            </div>
            
            <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-4">
              <UCard
                v-for="bid in receivedBids" 
                :key="bid.ulid"
                :class="getStatusClass(bid.status)"
                class="dark:bg-gray-800 transition-all"
              >
                <template #header>
                  <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                      {{ bid.item?.name || 'Material' }}
                      <UBadge :color="getStatusColor(bid.status)" class="text-xs">{{ formatStatus(bid.status) }}</UBadge>
                    </h3>
                  </div>
                </template>
                
                <div class="space-y-2">
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Merchant:</span>
                    <span class="font-medium">{{ bid.merchant?.business_name || 'Unknown' }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Quantity:</span>
                    <span class="font-medium">{{ bid.quantity }} kg</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Offered Price:</span>
                    <span class="font-medium text-green-600 dark:text-green-400">₱{{ bid.price_per_kg }} / kg</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Total Value:</span>
                    <span class="font-medium text-green-600 dark:text-green-400">₱{{ calculateTotal(bid) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Grade:</span>
                    <span class="font-medium">{{ bid.grade || 'Any' }}</span>
                  </div>
                  <div class="flex justify-between" v-if="bid.expiration_date">
                    <span class="text-gray-600 dark:text-gray-400">Expires:</span>
                    <span :class="isExpiringSoon(bid.expiration_date) ? 'font-medium text-amber-600 dark:text-amber-400' : 'font-medium'">
                      {{ formatDate(bid.expiration_date) }}
                    </span>
                  </div>
                  <div v-if="bid.notes" class="mt-3">
                    <div class="text-gray-600 dark:text-gray-400 text-sm">Notes:</div>
                    <p class="text-gray-800 dark:text-gray-300 text-sm mt-1">{{ bid.notes }}</p>
                  </div>
                </div>
                
                <template #footer>
                  <div class="flex justify-between" v-if="bid.status === 'pending'">
                    <UButton
                      @click="respondToBid(bid, 'accept')"
                      color="green"
                      variant="ghost"
                      icon="i-heroicons-check"
                      size="sm"
                    >
                      Accept
                    </UButton>
                    
                    <UButton
                      @click="respondToBid(bid, 'reject')"
                      color="red"
                      variant="ghost"
                      icon="i-heroicons-x-mark"
                      size="sm"
                    >
                      Reject
                    </UButton>
                  </div>
                  
                  <div class="flex justify-end" v-else>
                    <UButton
                      color="blue"
                      variant="ghost"
                      icon="i-heroicons-information-circle"
                      size="sm"
                      @click="viewBidDetails(bid)"
                    >
                      View Details
                    </UButton>
                  </div>
                </template>
              </UCard>
            </div>
          </div>
        </div>
      </template>
    </UTabs>
  </div>
  
  <!-- Create/Edit Bid Modal -->
  <UModal v-model="showNewBidModal" :ui="{ width: 'sm:max-w-lg' }">
    <UCard class="dark:bg-gray-800">
      <template #header>
        <div class="flex justify-between items-center">
          <h3 class="text-xl font-semibold dark:text-white">
            {{ editMode ? 'Edit Bid' : 'New Bid' }}
          </h3>
          <UButton
            color="gray" 
            variant="ghost"
            icon="i-heroicons-x-mark"
            @click="closeBidModal"
            size="sm"
            square
          />
        </div>
      </template>
      
      <UForm :state="formState" class="space-y-4" @submit="saveBid">
        <UFormGroup label="Junkshop" name="junkshop_id" required>
          <USelect
            v-model="formState.junkshop_id"
            :options="junkshopOptions"
            option-attribute="label"
            value-attribute="value"
            placeholder="Select junkshop"
            required
            :disabled="editMode"
          />
        </UFormGroup>
        
        <UFormGroup label="Material Type" name="item_id" required>
          <USelect
            v-model="formState.item_id"
            :options="itemOptions.filter(i => i.value !== null)"
            option-attribute="label"
            value-attribute="value"
            placeholder="Select material type"
            required
            :disabled="editMode"
          />
        </UFormGroup>
        
        <div class="grid grid-cols-2 gap-4">
          <UFormGroup label="Quantity (kg)" name="quantity" required>
            <UInput 
              v-model="formState.quantity" 
              type="number"
              min="0.01"
              step="0.01"
              required
            />
          </UFormGroup>
          
          <UFormGroup label="Price per kg (₱)" name="price_per_kg" required>
            <UInput 
              v-model="formState.price_per_kg" 
              type="number"
              min="0.01"
              step="0.01"
              required
            />
          </UFormGroup>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
          <UFormGroup label="Grade Preference" name="grade">
            <USelect
              v-model="formState.grade"
              :options="gradeOptions"
              placeholder="Any grade"
            />
          </UFormGroup>
          
          <UFormGroup label="Expires On (Optional)" name="expiration_date">
            <UInput 
              v-model="formState.expiration_date" 
              type="date"
              :min="minDate"
            />
          </UFormGroup>
        </div>
        
        <UFormGroup label="Notes (Optional)" name="notes">
          <UTextarea 
            v-model="formState.notes" 
            placeholder="Add any notes or requirements for the junkshop"
            :rows="3"
          />
        </UFormGroup>
        
        <div class="mt-4 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
          <div class="text-lg font-medium text-gray-800 dark:text-white mb-1">Bid Summary</div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-600 dark:text-gray-400">Quantity:</span>
            <span class="font-medium">{{ formState.quantity || 0 }} kg</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-600 dark:text-gray-400">Price per kg:</span>
            <span class="font-medium">₱{{ formState.price_per_kg || 0 }}</span>
          </div>
          <div class="flex justify-between text-sm mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">
            <span class="text-gray-600 dark:text-gray-400">Total Value:</span>
            <span class="font-bold text-green-600 dark:text-green-400">₱{{ calculateBidTotal() }}</span>
          </div>
        </div>
        
        <div class="flex justify-end gap-2 mt-6">
          <UButton
            color="gray"
            variant="ghost"
            @click="closeBidModal"
          >
            Cancel
          </UButton>
          
          <UButton
            type="submit"
            color="teal"
            :loading="isSaving"
          >
            {{ editMode ? 'Update Bid' : 'Submit Bid' }}
          </UButton>
        </div>
      </UForm>
    </UCard>
  </UModal>
  
  <!-- View Bid Details Modal -->
  <UModal v-model="showDetailsModal" :ui="{ width: 'sm:max-w-lg' }">
    <UCard class="dark:bg-gray-800" v-if="selectedBid">
      <template #header>
        <div class="flex justify-between items-center">
          <h3 class="text-xl font-semibold dark:text-white">
            Bid Details
          </h3>
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
            <UBadge :color="getStatusColor(selectedBid.status)" class="mr-2">{{ formatStatus(selectedBid.status) }}</UBadge>
            <span class="text-gray-600 dark:text-gray-400">
              {{ formatDate(selectedBid.created_at) }}
            </span>
          </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
          <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Merchant</div>
            <div class="font-medium">{{ selectedBid.merchant?.business_name }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Junkshop</div>
            <div class="font-medium">{{ selectedBid.junkshop?.name }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Quantity</div>
            <div class="font-medium">{{ selectedBid.quantity }} kg</div>
          </div>
          <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Price per kg</div>
            <div class="font-medium text-green-600 dark:text-green-400">₱{{ selectedBid.price_per_kg }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Total Value</div>
            <div class="font-medium text-green-600 dark:text-green-400">₱{{ calculateTotal(selectedBid) }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Grade</div>
            <div class="font-medium">{{ selectedBid.grade || 'Any' }}</div>
          </div>
        </div>
        
        <div v-if="selectedBid.notes">
          <div class="text-sm text-gray-500 dark:text-gray-400">Notes</div>
          <p class="text-gray-800 dark:text-gray-300 mt-1">{{ selectedBid.notes }}</p>
        </div>
        
        <div v-if="selectedBid.expiration_date">
          <div class="text-sm text-gray-500 dark:text-gray-400">Expiration Date</div>
          <div class="font-medium">{{ formatDate(selectedBid.expiration_date) }}</div>
        </div>
        
        <div v-if="selectedBid.status === 'accepted' || selectedBid.status === 'rejected'" class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
          <div class="text-sm text-gray-500 dark:text-gray-400">
            {{ selectedBid.status === 'accepted' ? 'Accepted On' : 'Rejected On' }}
          </div>
          <div class="font-medium">
            {{ formatDate(selectedBid.status === 'accepted' ? selectedBid.accepted_at : selectedBid.rejected_at) }}
          </div>
        </div>
      </div>
      
      <template #footer>
        <div class="flex justify-end">
          <UButton
            color="gray"
            @click="showDetailsModal = false"
          >
            Close
          </UButton>
        </div>
      </template>
    </UCard>
  </UModal>
  
  <!-- Confirm Withdraw Modal -->
  <UModal v-model="showConfirmModal">
    <UCard class="dark:bg-gray-800">
      <template #header>
        <div class="text-xl font-semibold">Confirm Withdrawal</div>
      </template>
      
      <p class="mb-4">Are you sure you want to withdraw this bid? This action cannot be undone.</p>
      
      <template #footer>
        <div class="flex justify-end gap-2">
          <UButton color="gray" variant="ghost" @click="showConfirmModal = false">
            Cancel
          </UButton>
          <UButton 
            color="red" 
            @click="withdrawBid"
            :loading="isWithdrawing"
          >
            Withdraw Bid
          </UButton>
        </div>
      </template>
    </UCard>
  </UModal>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

// State variables
const isLoading = ref(true);
const isLoadingReceived = ref(true);
const isSaving = ref(false);
const isWithdrawing = ref(false);
const myBids = ref([]);
const receivedBids = ref([]);
const showNewBidModal = ref(false);
const showDetailsModal = ref(false);
const showConfirmModal = ref(false);
const editMode = ref(false);
const selectedBid = ref(null);
const bidStats = ref({
  total_bids: 0,
  pending_bids: 0,
  accepted_bids: 0,
  rejected_bids: 0,
  withdrawn_bids: 0,
  expired_bids: 0,
  success_rate: 0
});

// For junkshop owners
const userJunkshops = ref([]);
const selectedJunkshop = ref(null);

// Tab state
const activeTab = ref('my-bids');
const tabs = [
  { id: 'my-bids', label: 'My Bids' },
  { id: 'received-bids', label: 'Received Bids' }
];

// Filter state
const filters = reactive({
  status: null,
  junkshop_id: null,
  item_id: null
});

// Form state
const formState = reactive({
  junkshop_id: null,
  item_id: null,
  quantity: null,
  price_per_kg: null,
  grade: null,
  expiration_date: null,
  notes: '',
  wanted_material_id: null
});

// Options for dropdowns
const statusOptions = [
  { label: 'All Statuses', value: null },
  { label: 'Pending', value: 'pending' },
  { label: 'Accepted', value: 'accepted' },
  { label: 'Rejected', value: 'rejected' },
  { label: 'Withdrawn', value: 'withdrawn' },
  { label: 'Expired', value: 'expired' }
];

const gradeOptions = [
  { label: 'Any Grade', value: null },
  { label: 'Grade A (Premium)', value: 'A' },
  { label: 'Grade B (Standard)', value: 'B' },
  { label: 'Grade C (Basic)', value: 'C' }
];

// Item and junkshop options (will be loaded from API)
const itemOptions = ref([]);
const junkshopOptions = ref([]);

// Computed properties
const minDate = computed(() => {
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  return tomorrow.toISOString().split('T')[0];
});

// Load data on component mount
onMounted(async () => {
  await Promise.all([
    fetchItems(),
    fetchJunkshops(),
    fetchMyBids(),
    fetchBidStats(),
    fetchUserJunkshops()
  ]);
});

// Fetch all available material items
const fetchItems = async () => {
  try {
    const response = await $fetch('/api/items');
    itemOptions.value = response.map(item => ({
      label: item.name,
      value: item.id
    }));
    
    // Add an "All materials" option for filters
    itemOptions.value.unshift({
      label: 'All Materials',
      value: null
    });
  } catch (error) {
    console.error('Failed to fetch materials', error);
    toast.error('Failed to load material types');
  }
};

// Fetch all junkshops
const fetchJunkshops = async () => {
  try {
    const response = await $fetch('/junkshops');
    junkshopOptions.value = response.map(junkshop => ({
      label: junkshop.name,
      value: junkshop.ulid
    }));
    
    // Add an "All junkshops" option for filters
    junkshopOptions.value.unshift({
      label: 'All Junkshops',
      value: null
    });
  } catch (error) {
    console.error('Failed to fetch junkshops', error);
    toast.error('Failed to load junkshops');
  }
};

// Fetch the user's junkshops (if they're an owner)
const fetchUserJunkshops = async () => {
  try {
    const response = await $fetch('/my-junkshops');
    
    if (response && response.length > 0) {
      userJunkshops.value = response;
      selectedJunkshop.value = response[0].ulid;
      
      // Only load received bids if there are junkshops
      loadReceivedBids();
    }
  } catch (error) {
    console.error('Failed to fetch user junkshops', error);
  }
};

// Fetch the user's bids
const fetchMyBids = async () => {
  try {
    isLoading.value = true;
    
    // Build query parameters
    const params = new URLSearchParams();
    if (filters.status) params.append('status', filters.status);
    if (filters.junkshop_id) params.append('junkshop_id', filters.junkshop_id);
    if (filters.item_id) params.append('item_id', filters.item_id);
    
    const url = `/bids/my-bids${params.toString() ? `?${params.toString()}` : ''}`;
    const response = await $fetch(url);
    
    myBids.value = response;
  } catch (error) {
    console.error('Failed to fetch your bids', error);
    toast.error('Failed to load your bids');
  } finally {
    isLoading.value = false;
  }
};

// Fetch bid statistics
const fetchBidStats = async () => {
  try {
    const response = await $fetch('/bids/stats/merchant');
    bidStats.value = response;
  } catch (error) {
    console.error('Failed to fetch bid statistics', error);
  }
};

// Load received bids for the selected junkshop
const loadReceivedBids = async () => {
  if (!selectedJunkshop.value) return;
  
  try {
    isLoadingReceived.value = true;
    
    const response = await $fetch(`/bids/junkshop/${selectedJunkshop.value}/bids`);
    receivedBids.value = response;
  } catch (error) {
    console.error('Failed to fetch received bids', error);
    toast.error('Failed to load received bids');
  } finally {
    isLoadingReceived.value = false;
  }
};

// Apply filters to bids
const applyFilters = () => {
  fetchMyBids();
};

// Handle tab change
const onTabChange = (tabId) => {
  activeTab.value = tabId;
  
  // Load received bids if switching to that tab and we have a junkshop
  if (tabId === 'received-bids' && userJunkshops.value.length > 0 && receivedBids.value.length === 0) {
    loadReceivedBids();
  }
};

// Save a new or updated bid
const saveBid = async () => {
  try {
    isSaving.value = true;
    
    if (editMode.value) {
      // Update existing bid
      await $fetch(`/bids/${selectedBid.value.ulid}`, {
        method: 'PUT',
        body: formState
      });
      
      toast.success('Bid updated successfully');
    } else {
      // Create new bid
      await $fetch('/bids', {
        method: 'POST',
        body: formState
      });
      
      toast.success('Bid submitted successfully');
    }
    
    // Refresh bids and stats
    await Promise.all([
      fetchMyBids(),
      fetchBidStats()
    ]);
    
    closeBidModal();
  } catch (error) {
    console.error('Failed to save bid', error);
    toast.error('Failed to save bid');
  } finally {
    isSaving.value = false;
  }
};

// Edit an existing bid
const editBid = (bid) => {
  selectedBid.value = bid;
  editMode.value = true;
  
  // Populate form with bid data
  formState.junkshop_id = bid.junkshop_id;
  formState.item_id = bid.item_id;
  formState.quantity = bid.quantity;
  formState.price_per_kg = bid.price_per_kg;
  formState.grade = bid.grade;
  formState.expiration_date = bid.expiration_date;
  formState.notes = bid.notes || '';
  formState.wanted_material_id = bid.wanted_material_id;
  
  showNewBidModal.value = true;
};

// Close the bid modal and reset form
const closeBidModal = () => {
  // Reset form state
  formState.junkshop_id = null;
  formState.item_id = null;
  formState.quantity = null;
  formState.price_per_kg = null;
  formState.grade = null;
  formState.expiration_date = null;
  formState.notes = '';
  formState.wanted_material_id = null;
  
  // Reset edit mode
  editMode.value = false;
  selectedBid.value = null;
  
  // Close modal
  showNewBidModal.value = false;
};

// Confirm withdrawal of a bid
const confirmWithdrawBid = (bid) => {
  selectedBid.value = bid;
  showConfirmModal.value = true;
};

// Withdraw a bid
const withdrawBid = async () => {
  if (!selectedBid.value) return;
  
  try {
    isWithdrawing.value = true;
    
    await $fetch(`/bids/${selectedBid.value.ulid}/withdraw`, {
      method: 'POST'
    });
    
    // Update the bid in the local array
    const index = myBids.value.findIndex(bid => bid.ulid === selectedBid.value.ulid);
    if (index !== -1) {
      myBids.value[index].status = 'withdrawn';
    }
    
    // Refresh bid statistics
    await fetchBidStats();
    
    toast.success('Bid withdrawn successfully');
    showConfirmModal.value = false;
  } catch (error) {
    console.error('Failed to withdraw bid', error);
    toast.error('Failed to withdraw bid');
  } finally {
    isWithdrawing.value = false;
  }
};

// Respond to a bid (accept or reject)
const respondToBid = async (bid, action) => {
  try {
    await $fetch(`/bids/${bid.ulid}/${action}`, {
      method: 'POST'
    });
    
    // Update the bid in the local array
    const index = receivedBids.value.findIndex(item => item.ulid === bid.ulid);
    if (index !== -1) {
      receivedBids.value[index].status = action === 'accept' ? 'accepted' : 'rejected';
      if (action === 'accept') {
        receivedBids.value[index].accepted_at = new Date().toISOString();
      } else {
        receivedBids.value[index].rejected_at = new Date().toISOString();
      }
    }
    
    toast.success(`Bid ${action === 'accept' ? 'accepted' : 'rejected'} successfully`);
  } catch (error) {
    console.error(`Failed to ${action} bid`, error);
    toast.error(`Failed to ${action} bid`);
  }
};

// View bid details
const viewBidDetails = (bid) => {
  selectedBid.value = bid;
  showDetailsModal.value = true;
};

// Calculate the total price of a bid
const calculateTotal = (bid) => {
  if (!bid || !bid.quantity || !bid.price_per_kg) return '0.00';
  return (parseFloat(bid.quantity) * parseFloat(bid.price_per_kg)).toFixed(2);
};

// Calculate the total of the current form
const calculateBidTotal = () => {
  if (!formState.quantity || !formState.price_per_kg) return '0.00';
  return (parseFloat(formState.quantity) * parseFloat(formState.price_per_kg)).toFixed(2);
};

// Format the status for display
const formatStatus = (status) => {
  if (!status) return '';
  
  return status.charAt(0).toUpperCase() + status.slice(1);
};

// Get the color for a status badge
const getStatusColor = (status) => {
  switch (status) {
    case 'pending': return 'amber';
    case 'accepted': return 'green';
    case 'rejected': return 'red';
    case 'withdrawn': return 'gray';
    case 'expired': return 'gray';
    default: return 'gray';
  }
};

// Get the class for a bid card based on status
const getStatusClass = (status) => {
  switch (status) {
    case 'pending': return 'border-l-4 border-amber-500';
    case 'accepted': return 'border-l-4 border-green-500';
    case 'rejected': return 'border-l-4 border-red-500';
    case 'withdrawn': return 'border-l-4 border-gray-500 opacity-70';
    case 'expired': return 'border-l-4 border-gray-500 opacity-70';
    default: return '';
  }
};

// Check if a date is expiring soon (within 3 days)
const isExpiringSoon = (dateString) => {
  if (!dateString) return false;
  
  const expirationDate = new Date(dateString);
  const now = new Date();
  const diffTime = expirationDate - now;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  
  return diffDays >= 0 && diffDays <= 3;
};

// Generate actions for bid dropdown menu
const getBidActions = (bid) => {
  const actions = [];
  
  if (bid.status === 'pending') {
    actions.push(
      {
        label: 'Edit Bid',
        icon: 'i-heroicons-pencil-square',
        click: () => editBid(bid)
      },
      {
        label: 'Withdraw Bid',
        icon: 'i-heroicons-x-mark',
        click: () => confirmWithdrawBid(bid)
      }
    );
  }
  
  actions.push({
    label: 'View Details',
    icon: 'i-heroicons-information-circle',
    click: () => viewBidDetails(bid)
  });
  
  return actions;
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

// Define page metadata
definePageMeta({
  layout: "dashboard",
  middleware: ["auth"]
});
</script>
