<template>
  <div class="p-8 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Material Marketplace</h1>
        <p class="text-gray-600 dark:text-gray-400">Manage your material requests and view listings</p>
      </div>
      
      <UButton 
        color="teal" 
        @click="showNewListingModal = true"
        icon="i-heroicons-plus"
      >
        New Material Request
      </UButton>
    </div>
    
    <!-- Tabs -->
    <UTabs :items="tabs" :default-index="0" @change="onTabChange">
      <template #default="{ selectedTabId }">
        <div class="mt-4">
          <!-- My Listings Tab -->
          <div v-if="selectedTabId === 'my-listings'" class="space-y-6">
            <div v-if="isLoading" class="py-12 flex justify-center">
              <UIcon name="i-heroicons-arrow-path" class="animate-spin text-teal-500 w-8 h-8" />
            </div>
            
            <div v-else-if="myListings.length === 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
              <div class="mb-4 bg-amber-100 dark:bg-amber-900/30 rounded-full inline-flex p-4">
                <UIcon name="i-heroicons-document-text" class="text-amber-500 w-8 h-8" />
              </div>
              <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">No Material Requests</h3>
              <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
                You haven't created any material requests yet. Create a listing to let junkshops know what materials you're looking for.
              </p>
              <UButton 
                color="teal" 
                @click="showNewListingModal = true"
                icon="i-heroicons-plus"
              >
                Create Your First Request
              </UButton>
            </div>
            
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <UCard
                v-for="listing in myListings" 
                :key="listing.ulid"
                :class="!listing.is_active ? 'opacity-75 border-dashed' : ''"
                class="dark:bg-gray-800 transition-all"
              >
                <template #header>
                  <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                      {{ listing.item?.name || 'Material' }}
                      <UBadge v-if="!listing.is_active" color="gray" class="text-xs">Inactive</UBadge>
                    </h3>
                    <UDropdown :items="getListingActions(listing)">
                      <UButton color="gray" variant="ghost" icon="i-heroicons-ellipsis-vertical" size="xs" />
                    </UDropdown>
                  </div>
                </template>
                
                <div class="space-y-2">
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Quantity:</span>
                    <span class="font-medium">{{ listing.quantity }} kg</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Desired Price:</span>
                    <span class="font-medium">₱{{ listing.desired_price }} / kg</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Grade:</span>
                    <span class="font-medium">{{ listing.grade || 'Any' }}</span>
                  </div>
                  <div class="flex justify-between" v-if="listing.deadline">
                    <span class="text-gray-600 dark:text-gray-400">Deadline:</span>
                    <span class="font-medium">{{ formatDate(listing.deadline) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Visibility:</span>
                    <span class="font-medium">{{ listing.is_public ? 'Public' : 'Private' }}</span>
                  </div>
                  <div v-if="listing.description" class="mt-3">
                    <div class="text-gray-600 dark:text-gray-400 text-sm">Description:</div>
                    <p class="text-gray-800 dark:text-gray-300 text-sm mt-1">{{ listing.description }}</p>
                  </div>
                </div>
                
                <template #footer>
                  <div class="flex justify-between">
                    <UButton
                      @click="editListing(listing)"
                      color="blue"
                      variant="ghost"
                      icon="i-heroicons-pencil-square"
                      size="sm"
                    >
                      Edit
                    </UButton>
                    
                    <UButton
                      @click="toggleListingActive(listing)"
                      :color="listing.is_active ? 'gray' : 'teal'"
                      variant="ghost"
                      :icon="listing.is_active ? 'i-heroicons-pause' : 'i-heroicons-play'"
                      size="sm"
                    >
                      {{ listing.is_active ? 'Deactivate' : 'Activate' }}
                    </UButton>
                  </div>
                </template>
              </UCard>
            </div>
          </div>
          
          <!-- Marketplace Tab -->
          <div v-if="selectedTabId === 'marketplace'" class="space-y-6">            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-4">
              <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Filter Listings</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
              </div>
              
              <div class="flex justify-end mt-4">
                <UButton @click="applyFilters" color="teal" icon="i-heroicons-funnel" size="sm">
                  Apply Filters
                </UButton>
              </div>
            </div>
            
            <div v-if="isLoadingMarketplace" class="py-12 flex justify-center">
              <UIcon name="i-heroicons-arrow-path" class="animate-spin text-teal-500 w-8 h-8" />
            </div>
            
            <div v-else-if="marketplaceListings.length === 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
              <div class="mb-4 bg-blue-100 dark:bg-blue-900/30 rounded-full inline-flex p-4">
                <UIcon name="i-heroicons-shopping-bag" class="text-blue-500 w-8 h-8" />
              </div>
              <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">No Listings Found</h3>
              <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">
                There are no material requests matching your filters. Try adjusting your search criteria or check back later.
              </p>
            </div>
            
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <UCard
                v-for="listing in marketplaceListings" 
                :key="listing.ulid"
                class="dark:bg-gray-800 transition-all"
              >
                <template #header>
                  <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold">{{ listing.item?.name || 'Material' }}</h3>
                    <UBadge color="amber">Request</UBadge>
                  </div>
                </template>
                
                <div class="space-y-2">
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Merchant:</span>
                    <span class="font-medium">{{ listing.merchant?.business_name || 'Unknown' }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Quantity:</span>
                    <span class="font-medium">{{ listing.quantity }} kg</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Offering:</span>
                    <span class="font-medium text-green-600 dark:text-green-400">₱{{ listing.desired_price }} / kg</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Grade:</span>
                    <span class="font-medium">{{ listing.grade || 'Any' }}</span>
                  </div>
                  <div class="flex justify-between" v-if="listing.deadline">
                    <span class="text-gray-600 dark:text-gray-400">Deadline:</span>
                    <span class="font-medium">{{ formatDate(listing.deadline) }}</span>
                  </div>
                  <div v-if="listing.description" class="mt-3">
                    <div class="text-gray-600 dark:text-gray-400 text-sm">Description:</div>
                    <p class="text-gray-800 dark:text-gray-300 text-sm mt-1">{{ listing.description }}</p>
                  </div>
                </div>
                
                <template #footer>
                  <div class="flex justify-between">                    <UButton
                      color="blue"
                      variant="ghost"
                      icon="i-heroicons-information-circle"
                      size="sm"
                      @click="viewListingDetails(listing)"
                    >
                      View Details
                    </UButton>
                    
                    <UButton
                      color="teal"
                      variant="soft"
                      icon="i-heroicons-currency-dollar"
                      size="sm"
                      @click="placeBid(listing)"
                    >
                      Place Bid
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
  
  <!-- Create/Edit Material Request Modal -->
  <UModal v-model="showNewListingModal" :ui="{ width: 'sm:max-w-lg' }">
    <UCard class="dark:bg-gray-800">
      <template #header>
        <div class="flex justify-between items-center">
          <h3 class="text-xl font-semibold dark:text-white">
            {{ editMode ? 'Edit Material Request' : 'New Material Request' }}
          </h3>
          <UButton
            color="gray" 
            variant="ghost"
            icon="i-heroicons-x-mark"
            @click="closeListingModal"
            size="sm"
            square
          />
        </div>
      </template>
      
      <UForm :state="formState" class="space-y-4" @submit="saveListing">
        <UFormGroup label="Material Type" name="item_id" required>
          <USelect
            v-model="formState.item_id"
            :options="itemOptions"
            option-attribute="label"
            value-attribute="value"
            placeholder="Select material type"
            required
          />
        </UFormGroup>
        
        <div class="grid grid-cols-2 gap-4">
          <UFormGroup label="Quantity (kg)" name="quantity" required>
            <UInput 
              v-model="formState.quantity" 
              type="number"
              min="0"
              step="0.01"
              required
            />
          </UFormGroup>
          
          <UFormGroup label="Desired Price (₱/kg)" name="desired_price" required>
            <UInput 
              v-model="formState.desired_price" 
              type="number"
              min="0"
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
          
          <UFormGroup label="Deadline (Optional)" name="deadline">
            <UInput 
              v-model="formState.deadline" 
              type="date"
              :min="minDate"
            />
          </UFormGroup>
        </div>
        
        <UFormGroup label="Description (Optional)" name="description">
          <UTextarea 
            v-model="formState.description" 
            placeholder="Describe any specific requirements or details about the materials you're looking for"
            :rows="3"
          />
        </UFormGroup>
        
        <UFormGroup>
          <div class="flex items-center">
            <UCheckbox v-model="formState.is_public" name="is_public" />
            <label for="is_public" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
              Make this request public in the marketplace
            </label>
          </div>
        </UFormGroup>
        
        <div class="flex justify-end gap-2 mt-6">
          <UButton
            color="gray"
            variant="ghost"
            @click="closeListingModal"
          >
            Cancel
          </UButton>
          
          <UButton
            type="submit"
            color="teal"
            :loading="isSavingListing"
          >
            {{ editMode ? 'Update Request' : 'Create Request' }}
          </UButton>
        </div>
      </UForm>
    </UCard>
  </UModal>
  
  <!-- View Listing Details Modal -->
  <UModal v-model="showDetailsModal" :ui="{ width: 'sm:max-w-lg' }">
    <UCard class="dark:bg-gray-800" v-if="selectedListing">
      <template #header>
        <div class="flex justify-between items-center">
          <h3 class="text-xl font-semibold dark:text-white">
            Material Request Details
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
          <h4 class="text-lg font-medium text-gray-800 dark:text-white">{{ selectedListing.item?.name }}</h4>
          <div class="flex items-center mt-1">
            <UIcon name="i-heroicons-building-storefront" class="text-teal-500 mr-2" />
            <span class="text-gray-600 dark:text-gray-400">{{ selectedListing.merchant?.business_name }}</span>
          </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
          <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Quantity Needed</div>
            <div class="font-medium">{{ selectedListing.quantity }} kg</div>
          </div>
          <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Offered Price</div>
            <div class="font-medium text-green-600 dark:text-green-400">₱{{ selectedListing.desired_price }} / kg</div>
          </div>
          <div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Grade Preference</div>
            <div class="font-medium">{{ selectedListing.grade || 'Any' }}</div>
          </div>
          <div v-if="selectedListing.deadline">
            <div class="text-sm text-gray-500 dark:text-gray-400">Deadline</div>
            <div class="font-medium">{{ formatDate(selectedListing.deadline) }}</div>
          </div>
        </div>
        
        <div v-if="selectedListing.description">
          <div class="text-sm text-gray-500 dark:text-gray-400">Description</div>
          <p class="text-gray-800 dark:text-gray-300 mt-1">{{ selectedListing.description }}</p>
        </div>
        
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
          <div class="text-sm text-gray-500 dark:text-gray-400">Contact Information</div>
          <div class="mt-2">
            <div class="flex items-center">
              <UIcon name="i-heroicons-map-pin" class="text-teal-500 mr-2" />
              <span>{{ selectedListing.merchant?.address || 'Address not provided' }}</span>
            </div>
            <div class="flex items-center mt-1">
              <UIcon name="i-heroicons-phone" class="text-teal-500 mr-2" />
              <span>{{ selectedListing.merchant?.contact || 'Contact not provided' }}</span>
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
            icon="i-heroicons-chat-bubble-left-ellipsis"
          >
            Contact Merchant
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
const isLoading = ref(true);
const isLoadingMarketplace = ref(true);
const isSavingListing = ref(false);
const myListings = ref([]);
const marketplaceListings = ref([]);
const showNewListingModal = ref(false);
const showDetailsModal = ref(false);
const editMode = ref(false);
const selectedListing = ref(null);

// Tab state
const activeTab = ref('my-listings');
const tabs = [
  { id: 'my-listings', label: 'My Requests' },
  { id: 'marketplace', label: 'Material Marketplace' }
];

// Filter state
const filters = reactive({
  itemId: null,
  minPrice: null,
  maxPrice: null,
  grade: null
});

// Form state
const formState = reactive({
  item_id: null,
  quantity: null,
  desired_price: null,
  grade: null,
  deadline: null,
  description: '',
  is_public: true
});

// Computed properties
const minDate = computed(() => {
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  return tomorrow.toISOString().split('T')[0];
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
    fetchItems(),
    fetchMyListings()
  ]);
});

// Fetch all available material items
const fetchItems = async () => {
  try {
    const response = await $fetch('/materials');
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

// Fetch the merchant's listings
const fetchMyListings = async () => {
  try {
    isLoading.value = true;
    const response = await $fetch('/marketplace/my-listings');
    myListings.value = response;
  } catch (error) {
    console.error('Failed to fetch your material listings', error);
    toast.error('Failed to load your material requests');
  } finally {
    isLoading.value = false;
  }
};

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
    
    const url = `/marketplace/wanted-materials${params.toString() ? `?${params.toString()}` : ''}`;
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

// Handle tab change
const onTabChange = (tabId) => {
  activeTab.value = tabId;
  
  // Load marketplace data if switching to that tab
  if (tabId === 'marketplace' && marketplaceListings.value.length === 0) {
    fetchMarketplaceListings();
  }
};

// Save a new or updated listing
const saveListing = async () => {
  try {
    isSavingListing.value = true;
    
    if (editMode.value) {
      // Update existing listing
      await $fetch(`/marketplace/wanted-materials/${selectedListing.value.ulid}`, {
        method: 'PUT',
        body: formState
      });
      
      toast.success('Material request updated successfully');
    } else {
      // Create new listing
      await $fetch('/marketplace/wanted-materials', {
        method: 'POST',
        body: formState
      });
      
      toast.success('Material request created successfully');
    }
    
    // Refresh listings and close modal
    await fetchMyListings();
    closeListingModal();
  } catch (error) {
    console.error('Failed to save material request', error);
    toast.error('Failed to save material request');
  } finally {
    isSavingListing.value = false;
  }
};

// Edit an existing listing
const editListing = (listing) => {
  selectedListing.value = listing;
  editMode.value = true;
  
  // Populate form with listing data
  formState.item_id = listing.item_id;
  formState.quantity = listing.quantity;
  formState.desired_price = listing.desired_price;
  formState.grade = listing.grade;
  formState.deadline = listing.deadline;
  formState.description = listing.description || '';
  formState.is_public = listing.is_public;
  
  showNewListingModal.value = true;
};

// Close the listing modal and reset form
const closeListingModal = () => {
  // Reset form state
  formState.item_id = null;
  formState.quantity = null;
  formState.desired_price = null;
  formState.grade = null;
  formState.deadline = null;
  formState.description = '';
  formState.is_public = true;
  
  // Reset edit mode
  editMode.value = false;
  selectedListing.value = null;
  
  // Close modal
  showNewListingModal.value = false;
};

// Toggle the active status of a listing
const toggleListingActive = async (listing) => {
  try {
    const response = await $fetch(`/marketplace/wanted-materials/${listing.ulid}/toggle-active`, {
      method: 'POST'
    });
    
    // Update the listing in the local array
    const index = myListings.value.findIndex(item => item.ulid === listing.ulid);
    if (index !== -1) {
      myListings.value[index].is_active = !myListings.value[index].is_active;
    }
    
    toast.success(response.message);
  } catch (error) {
    console.error('Failed to toggle listing status', error);
    toast.error('Failed to update listing status');
  }
};

// View listing details
const viewListingDetails = (listing) => {
  selectedListing.value = listing;
  showDetailsModal.value = true;
};

// Generate actions for listing dropdown menu
const getListingActions = (listing) => {
  return [
    {
      label: 'Edit',
      icon: 'i-heroicons-pencil-square',
      click: () => editListing(listing)
    },
    {
      label: listing.is_active ? 'Deactivate' : 'Activate',
      icon: listing.is_active ? 'i-heroicons-pause' : 'i-heroicons-play',
      click: () => toggleListingActive(listing)
    },
    {
      label: 'Delete',
      icon: 'i-heroicons-trash',
      click: async () => {
        if (confirm('Are you sure you want to delete this listing?')) {
          try {
            await $fetch(`/marketplace/wanted-materials/${listing.ulid}`, {
              method: 'DELETE'
            });
            
            // Remove from local array
            myListings.value = myListings.value.filter(item => item.ulid !== listing.ulid);
            
            toast.success('Material request deleted successfully');
          } catch (error) {
            console.error('Failed to delete listing', error);
            toast.error('Failed to delete material request');
          }
        }
      }
    }
  ];
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
