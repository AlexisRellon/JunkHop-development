<template>
  <div>    
    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <UCard class="bg-amber-50 dark:bg-amber-900/30 border-0">
        <div class="flex items-center">
          <div class="p-3 bg-amber-100 dark:bg-amber-800/30 rounded-full mr-4">
            <UIcon name="i-heroicons-clock" class="text-amber-600 dark:text-amber-400" size="lg" />
          </div>
          <div>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Bids</div>
            <div class="text-2xl font-bold text-gray-800 dark:text-white">{{ stats.pending }}</div>
          </div>
        </div>
      </UCard>
      
      <UCard class="bg-emerald-50 dark:bg-emerald-900/30 border-0">
        <div class="flex items-center">
          <div class="p-3 bg-emerald-100 dark:bg-emerald-800/30 rounded-full mr-4">
            <UIcon name="i-heroicons-check" class="text-emerald-600 dark:text-emerald-400" size="lg" />
          </div>
          <div>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Approved Bids</div>
            <div class="text-2xl font-bold text-gray-800 dark:text-white">{{ stats.approved }}</div>
          </div>
        </div>
      </UCard>
      
      <UCard class="bg-red-50 dark:bg-red-900/30 border-0">
        <div class="flex items-center">
          <div class="p-3 bg-red-100 dark:bg-red-800/30 rounded-full mr-4">
            <UIcon name="i-heroicons-x-mark" class="text-red-600 dark:text-red-400" size="lg" />
          </div>
          <div>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Rejected Bids</div>
            <div class="text-2xl font-bold text-gray-800 dark:text-white">{{ stats.rejected }}</div>
          </div>
        </div>
      </UCard>
    </div>

    <!-- Search and Filter Controls -->
    <div class="flex flex-col md:flex-row gap-4 mb-6">
      <UInput :class="'relative'" v-model="searchQuery" icon="i-heroicons-magnifying-glass" placeholder="Search bids..." class="md:w-64" />
      
      <USelect v-model="statusFilter" :options="statusOptions" placeholder="Filter by status" class="relative md:w-48" />
      
      <USelect v-model="sortBy" :options="sortOptions" placeholder="Sort by" class="relative md:w-48" />
      
      <div class="flex-grow"></div>
      
      <UButton :class="'relative'" color="amber" @click="fetchBids" icon="i-heroicons-arrow-path" :loading="isLoading">
        Refresh
      </UButton>
    </div>

    <!-- Bids Table -->
    <UCard>
      <div v-if="isLoading" class="flex justify-center items-center py-12">
        <UIcon name="i-heroicons-arrow-path" class="animate-spin h-8 w-8 text-amber-500" />
      </div>
      
      <div v-else-if="filteredBids.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
        <UIcon name="i-heroicons-inbox" class="h-16 w-16 text-gray-400 mb-4" />
        <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">No bids found</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md">
          {{ searchQuery || statusFilter !== 'all' ? 'Try adjusting your filters' : 'There are no bids available at the moment' }}
        </p>
      </div>
      
      <UTable v-else :columns="columns" :rows="filteredBids" :loading="isLoading" hover>
        <!-- Custom Cell Renderers -->
        <template #item-data="{ row }">
          <div class="flex items-center">
            <UIcon name="i-heroicons-cube" class="text-blue-500 mr-2" />
            <div>
              <div class="font-medium text-gray-900 dark:text-white">{{ row.item?.name || 'Unknown Item' }}</div>
              <div class="text-xs text-gray-500">ID: {{ row.item_id }}</div>
            </div>
          </div>
        </template>
        
        <template #price-data="{ row }">
          <div class="font-medium">₱{{ Number(row.price_per_kg).toFixed(2) }}/kg</div>
          <div class="text-xs text-gray-500">Total: ₱{{ (Number(row.quantity) * Number(row.price_per_kg)).toFixed(2) }}</div>
        </template>
        
        <template #quantity-data="{ row }">
          <div>{{ Number(row.quantity).toFixed(0) }} kg</div>
        </template>
        
        <template #junkshop-data="{ row }">
          <div class="font-medium">{{ row.junkshop?.name || 'Unknown' }}</div>
          <div class="text-xs text-gray-500">{{ row.junkshop_id }}</div>
        </template>
        
        <template #created_at-data="{ row }">
          <div>{{ new Date(row.created_at).toLocaleDateString() }}</div>
          <div class="text-xs text-gray-500">{{ new Date(row.created_at).toLocaleTimeString() }}</div>
        </template>
        
        <template #status-data="{ row }">
          <UBadge 
            :color="row.status === 'pending' ? 'amber' : (row.status === 'accepted' ? 'emerald' : 'red')" 
            variant="subtle"
          >
            {{ row.status.charAt(0).toUpperCase() + row.status.slice(1) }}
          </UBadge>
        </template>
        
        <template #actions-data="{ row }">
          <div class="flex space-x-2">
            <UButton 
              @click="viewBidDetails(row)" 
              icon="i-heroicons-eye" 
              color="gray" 
              variant="ghost" 
              size="xs"
              :tooltip="{ text: 'View Details' }"
            />            
            <template v-if="row.status === 'pending'">              <UButton 
                @click="showApproveConfirmationDialog(row)" 
                icon="i-heroicons-check" 
                color="emerald" 
                variant="ghost" 
                size="xs"
                :tooltip="{ text: 'Approve Bid' }"
              />
              
              <UDropdown :items="rejectOptions(row)" :popper="{ placement: 'bottom-end' }">
                <UButton
                  icon="i-heroicons-x-mark"
                  color="red"
                  variant="ghost"
                  size="xs"
                  :tooltip="{ text: 'Reject Options' }"
                />
              </UDropdown>
            </template>
          </div>
        </template>
      </UTable>
      
      <!-- Pagination Controls -->
      <div class="flex justify-between items-center mt-4">
        <div class="text-sm text-gray-500">
          Showing {{ filteredBids.length ? ((pagination.currentPage - 1) * pagination.perPage) + 1 : 0 }} 
          to {{ Math.min(pagination.currentPage * pagination.perPage, filteredBids.length) }} 
          of {{ filteredBids.length }} bids
        </div>          <UPagination 
          v-if="Math.ceil(filteredBids.length / pagination.perPage) > 1"
          v-model="pagination.currentPage" 
          :page-count="Math.ceil(filteredBids.length / pagination.perPage)"
          :total="filteredBids.length"
          :ui="{
            wrapper: 'flex items-center gap-1',
            rounded: 'rounded-md',
            default: {
              size: 'sm'
            }
          }"
        />
      </div>
    </UCard>
      <!-- Bid Details Modal -->
    <UModal v-model="showBidDetailsModal">
      <UCard v-if="selectedBid" class="max-w-lg">
        <template #header>
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold">Bid Details</h3>
            <UButton icon="i-heroicons-x-mark" color="gray" variant="ghost" size="sm" @click="closeBidDetails" />
          </div>
        </template>
        
        <div class="space-y-4">
          <!-- Bid Overview -->
          <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <div class="text-sm text-gray-500">Item</div>
                <div class="font-medium">{{ selectedBid.item?.name || 'Unknown Item' }}</div>
              </div>
              
              <div>
                <div class="text-sm text-gray-500">Status</div>
                <UBadge 
                  :color="selectedBid.status === 'pending' ? 'amber' : (selectedBid.status === 'accepted' ? 'emerald' : 'red')" 
                  variant="subtle"
                >
                  {{ selectedBid.status.charAt(0).toUpperCase() + selectedBid.status.slice(1) }}
                </UBadge>
              </div>
              
              <div>
                <div class="text-sm text-gray-500">Quantity</div>
                <div class="font-medium">{{ Number(selectedBid.quantity).toFixed(0) }} kg</div>
              </div>
              
              <div>
                <div class="text-sm text-gray-500">Price</div>
                <div class="font-medium">₱{{ Number(selectedBid.price_per_kg).toFixed(2) }}/kg</div>
              </div>
              
              <div>
                <div class="text-sm text-gray-500">Total Value</div>
                <div class="font-medium">₱{{ (Number(selectedBid.quantity) * Number(selectedBid.price_per_kg)).toFixed(2) }}</div>
              </div>
              
              <div>
                <div class="text-sm text-gray-500">Created</div>
                <div class="font-medium">{{ new Date(selectedBid.created_at).toLocaleString() }}</div>
              </div>
            </div>
          </div>
          
          <!-- Junkshop Information -->
          <div>
            <h4 class="font-medium mb-2">Junkshop Information</h4>
            <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <div class="text-sm text-gray-500">Name</div>
                  <div class="font-medium">{{ selectedBid.junkshop?.name || 'Unknown' }}</div>
                </div>
                
                <div>
                  <div class="text-sm text-gray-500">ID</div>
                  <div class="font-medium text-xs">{{ selectedBid.junkshop_id }}</div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Notes -->
          <div v-if="selectedBid.notes">
            <h4 class="font-medium mb-2">Notes</h4>
            <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
              <p class="text-sm">{{ selectedBid.notes }}</p>
            </div>
          </div>
          
          <!-- Rejection Reason (if applicable) -->
          <div v-if="selectedBid.status === 'rejected' && selectedBid.rejection_reason">
            <h4 class="font-medium mb-2 text-red-500 dark:text-red-400">Rejection Reason</h4>
            <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg border border-red-200 dark:border-red-900/50">
              <p class="text-sm text-red-300">{{ selectedBid.rejection_reason }}</p>
            </div>
          </div>
        </div>
        
        <template #footer>
          <div class="flex justify-end gap-2">
            <UButton color="gray" variant="ghost" @click="closeBidDetails">
              Close
            </UButton>
              <template v-if="selectedBid.status === 'pending'">
              <UButton color="emerald" @click="showApproveConfirmationDialog(selectedBid)">
                Approve Bid
              </UButton>
              
              <UButton color="red" variant="soft" @click="openRejectModal">
                Reject Bid
              </UButton>
            </template>
          </div>
        </template>
      </UCard>
    </UModal>
      <!-- Rejection Reason Modal -->
    <UModal v-model="showRejectModal">
      <UCard v-if="selectedBid" class="max-w-lg">
        <template #header>
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold">Reject Bid</h3>
            <UButton icon="i-heroicons-x-mark" color="gray" variant="ghost" size="sm" @click="closeRejectModal" />
          </div>
        </template>
        
        <div class="space-y-4">
          <p class="text-sm text-gray-500">
            Please provide a reason for rejecting this bid. This will be visible to the junkshop owner.
          </p>
          
          <UFormGroup label="Rejection Reason" required>
            <UTextarea 
              v-model="rejectionReason" 
              placeholder="Enter reason for rejection..." 
              :rows="4"
              :ui="{ base: 'relative block w-full disabled:cursor-not-allowed disabled:opacity-75 focus:outline-none' }"
            />
          </UFormGroup>
        </div>
        
        <template #footer>
          <div class="flex justify-end gap-2">
            <UButton color="gray" variant="ghost" @click="closeRejectModal">
              Cancel
            </UButton>
            
            <UButton 
              color="red" 
              :disabled="!rejectionReason.trim()" 
              :loading="isUpdating"
              @click="rejectBidWithReason"
            >
              Reject Bid
            </UButton>
          </div>
        </template>
      </UCard>
    </UModal>

    <!-- Approve Confirmation Dialog -->
    <UiConfirmationDialog
      v-model:show="showApproveConfirmation"
      title="Approve Bid"
      message="Are you sure you want to approve this bid? This action will notify the junkshop owner."
      confirm-label="Yes, Approve"
      confirm-color="green"
      confirm-icon="i-heroicons-check"
      @confirm="confirmApprove"
    />    <!-- Reject Confirmation Dialog -->
    <UiConfirmationDialog
      v-model:show="showRejectConfirmation"
      title="Reject Bid"
      message="Are you sure you want to reject this bid without providing a reason? The junkshop will not receive feedback on why their bid was rejected."
      confirm-label="Yes, Reject"
      confirm-color="red"
      confirm-icon="i-heroicons-x-mark"
      @confirm="confirmReject"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const toast = useToast();

// State variables
const bids = ref([]);
const selectedBid = ref(null);
const isLoading = ref(false);
const isUpdating = ref(false);
const showBidDetailsModal = ref(false);
const showRejectModal = ref(false);
const rejectionReason = ref('');
const searchQuery = ref('');
const statusFilter = ref('all');
const sortBy = ref('newest');
const pagination = ref({
  currentPage: 1,
  perPage: 10,
  total: 0
});

// Confirmation dialog controls
const showApproveConfirmation = ref(false);
const showRejectConfirmation = ref(false);
const bidToAction = ref(null);

// Stats
const stats = ref({
  pending: 0,
  approved: 0,
  rejected: 0
});

// Options for dropdowns
const statusOptions = [
  { label: 'All Statuses', value: 'all' },
  { label: 'Pending', value: 'pending' },
  { label: 'Approved', value: 'accepted' },
  { label: 'Rejected', value: 'rejected' }
];

const sortOptions = [
  { label: 'Newest First', value: 'newest' },
  { label: 'Oldest First', value: 'oldest' },
  { label: 'Highest Price', value: 'price_desc' },
  { label: 'Lowest Price', value: 'price_asc' },
  { label: 'Largest Quantity', value: 'quantity_desc' },
  { label: 'Smallest Quantity', value: 'quantity_asc' }
];

// Table columns
const columns = [
  { key: 'item', label: 'Item' },
  { key: 'quantity', label: 'Quantity' },
  { key: 'price', label: 'Price' },
  { key: 'junkshop', label: 'Junkshop' },
  { key: 'created_at', label: 'Created' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: 'Actions' }
];

// Computed properties
const filteredBids = computed(() => {
  let result = [...bids.value];

  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(bid => 
      (bid.item?.name || '').toLowerCase().includes(query) ||
      (bid.junkshop?.name || '').toLowerCase().includes(query) ||
      bid.notes?.toLowerCase().includes(query)
    );
  }

  // Apply status filter
  if (statusFilter.value !== 'all') {
    result = result.filter(bid => bid.status === statusFilter.value);
  }

  // Apply sorting
  switch (sortBy.value) {
    case 'newest':
      result.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      break;
    case 'oldest':
      result.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
      break;
    case 'price_desc':
      result.sort((a, b) => Number(b.price_per_kg) - Number(a.price_per_kg));
      break;
    case 'price_asc':
      result.sort((a, b) => Number(a.price_per_kg) - Number(b.price_per_kg));
      break;
    case 'quantity_desc':
      result.sort((a, b) => Number(b.quantity) - Number(a.quantity));
      break;
    case 'quantity_asc':
      result.sort((a, b) => Number(a.quantity) - Number(b.quantity));
      break;
  }

  return result;
});

// Fetch all bids
const fetchBids = async () => {
  isLoading.value = true;
    try {
      const response = await $fetch('/admin/bids', {
      headers: {
        Authorization: `Bearer ${auth.token}`
      }
    });
    
    if (Array.isArray(response)) {
      bids.value = response;
      
      // Update stats
      stats.value = {
        pending: response.filter(bid => bid.status === 'pending').length,
        approved: response.filter(bid => bid.status === 'accepted').length,
        rejected: response.filter(bid => bid.status === 'rejected').length
      };
    }
    
  } catch (error) {
    console.error('Error fetching bids:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to load bids. Please try again.',
      color: 'red'
    });
    
    // For demo purposes, use sample data if API fails
    useSampleData();
  } finally {
    isLoading.value = false;
  }
};

// View bid details
const viewBidDetails = (bid) => {
  selectedBid.value = bid;
  showBidDetailsModal.value = true;
};

// Close bid details modal
const closeBidDetails = () => {
  showBidDetailsModal.value = false;
  selectedBid.value = null;
};

// Open reject modal
const openRejectModal = () => {
  if (selectedBid.value) {
    showRejectWithReasonModal(selectedBid.value);
  }
};

// Close reject modal
const closeRejectModal = () => {
  showRejectModal.value = false;
  rejectionReason.value = '';
};

// Show approve confirmation dialog
const showApproveConfirmationDialog = (bid) => {
  bidToAction.value = bid;
  showApproveConfirmation.value = true;
};

// Show reject confirmation dialog (for direct rejection without reason)
const showRejectConfirmationDialog = (bid) => {
  bidToAction.value = bid;
  showRejectConfirmation.value = true;
};

// Show reject with reason modal
const showRejectWithReasonModal = (bid) => {
  selectedBid.value = bid;
  rejectionReason.value = '';
  showRejectModal.value = true;
};

// Confirm approve action
const confirmApprove = () => {
  if (bidToAction.value) {
    updateBidStatus(bidToAction.value.id, 'accepted');
  }
};

// Confirm reject action (direct rejection without reason)
const confirmReject = () => {
  if (bidToAction.value) {
    // Use a default rejection reason when using quick reject
    rejectionReason.value = "Bid rejected by administrator.";
    updateBidStatus(bidToAction.value.id, 'rejected');
  }
};

// Reject options for dropdown
const rejectOptions = (bid) => {
  return [
    [
      {
        label: 'Reject',
        icon: 'i-heroicons-x-mark',
        click: () => showRejectConfirmationDialog(bid)
      },
      {
        label: 'Reject with Reason',
        icon: 'i-heroicons-chat-bubble-left-text',
        click: () => showRejectWithReasonModal(bid)
      }
    ]
  ];
};

// Update bid status
const updateBidStatus = async (bidId, status) => {
  if (!bidId || !status) return;
    isUpdating.value = true;
    try {
      // Make API call to update bid status
      const response = await $fetch(`/admin/bids/${bidId}/status`, {
        method: 'PUT',
        body: { 
          status,
          rejection_reason: status === 'rejected' ? rejectionReason.value : null
        },
        headers: {
          Authorization: `Bearer ${auth.token}`,
          'Content-Type': 'application/json'
        }
      });
      // Update the bid in the local state with the response
    const index = bids.value.findIndex(bid => bid.id === bidId);
    if (index !== -1) {
      // Update with response data to ensure we have all updated fields
      bids.value[index] = response;
      
      // Update stats
      updateStats();
    }
    
    // Close modals
    closeBidDetails();
    closeRejectModal();
    
    // Show success message
    toast.add({
      title: 'Success',
      description: `Bid has been ${status === 'accepted' ? 'approved' : 'rejected'}.`,
      color: status === 'accepted' ? 'green' : 'amber'
    });
  } catch (error) {
    console.error(`Error updating bid status:`, error);
    toast.add({
      title: 'Error',
      description: 'Failed to update bid status. Please try again.',
      color: 'red'
    });
    
    // For demo purposes, update the UI even if the API fails
    const index = bids.value.findIndex(bid => bid.id === bidId);
    if (index !== -1) {
      bids.value[index].status = status;
      if (status === 'rejected' && rejectionReason.value) {
        bids.value[index].rejection_reason = rejectionReason.value;
      }
      updateStats();
      closeBidDetails();
      closeRejectModal();
    }
  } finally {
    isUpdating.value = false;
  }
};

// Reject bid with reason
const rejectBidWithReason = () => {
  if (!selectedBid.value) return;
  if (!rejectionReason.value.trim()) {
    toast.add({
      title: 'Error',
      description: 'Please provide a reason for rejection.',
      color: 'red'
    });
    return;
  }
  
  updateBidStatus(selectedBid.value.id, 'rejected');
};

// Update stats based on current bids
const updateStats = () => {
  stats.value = {
    pending: bids.value.filter(bid => bid.status === 'pending').length,
    approved: bids.value.filter(bid => bid.status === 'accepted').length,
    rejected: bids.value.filter(bid => bid.status === 'rejected').length
  };
};

// Sample data for demonstration purposes
const useSampleData = () => {
  bids.value = [
    {
      id: 1,
      item_id: 101,
      item: { name: 'Aluminum Cans' },
      quantity: 250,
      price_per_kg: 15.5,
      junkshop_id: '01JSJC05JCG6Z3C1YTQA6D8YGE',
      junkshop: { name: 'Green Earth Recycling' },
      status: 'pending',
      notes: 'Clean and crushed cans, ready for pickup.',
      created_at: '2025-04-22T10:30:00.000Z',
      updated_at: '2025-04-22T10:30:00.000Z'
    },
    {
      id: 2,
      item_id: 102,
      item: { name: 'Copper Wire' },
      quantity: 50,
      price_per_kg: 180,
      junkshop_id: '01JSJC05JCG6Z3C1YTQA6D8YGF',
      junkshop: { name: 'Metro Scrap Buyers' },
      status: 'accepted',
      notes: 'Clean copper wire, no insulation.',
      created_at: '2025-04-21T14:45:00.000Z',
      updated_at: '2025-04-22T09:15:00.000Z',
      accepted_at: '2025-04-22T09:15:00.000Z'
    },
    {
      id: 3,
      item_id: 103,
      item: { name: 'Cardboard' },
      quantity: 500,
      price_per_kg: 5,
      junkshop_id: '01JSJC05JCG6Z3C1YTQA6D8YGE',
      junkshop: { name: 'Green Earth Recycling' },
      status: 'rejected',
      notes: 'Dry and flattened cardboard boxes.',
      rejection_reason: 'Price is too high for current market rates.',
      created_at: '2025-04-20T11:20:00.000Z',
      updated_at: '2025-04-21T08:30:00.000Z',
      rejected_at: '2025-04-21T08:30:00.000Z'
    }
  ];
  
  updateStats();
};

// Lifecycle hooks
onMounted(() => {
  fetchBids();
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: #4b5563;
}
</style>
