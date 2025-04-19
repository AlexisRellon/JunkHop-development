<template>
  <div class="space-y-6">
    <UCard>
      <template #header>
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200">Bids Received</h3>
          <UBadge color="blue" v-if="pendingBidsCount > 0">{{ pendingBidsCount }} Pending</UBadge>
        </div>
      </template>
      
      <div v-if="loading" class="py-4 flex justify-center">
        <UIcon name="i-heroicons-arrow-path" class="animate-spin text-teal-500 w-6 h-6" />
      </div>
      
      <div v-else-if="receivedBids.length === 0" class="py-8 text-center">
        <UIcon name="i-heroicons-inbox" class="text-gray-400 mx-auto mb-2 w-12 h-12" />
        <h3 class="text-gray-600 dark:text-gray-400 mb-1">No Bids Received</h3>
        <p class="text-gray-500 dark:text-gray-500 mb-4">You haven't received any bids on your material requests yet</p>
      </div>
      
      <div v-else class="space-y-4">
        <div 
          v-for="bid in receivedBids" 
          :key="bid.ulid"
          class="border dark:border-gray-700 rounded-lg p-4 transition-all hover:bg-gray-50 dark:hover:bg-gray-800/50"
        >
          <div class="flex justify-between items-start">
            <div>
              <h4 class="font-medium text-gray-800 dark:text-gray-200">
                {{ bid.wantedMaterial?.item?.name || 'Material' }}
                <UBadge :color="getBidStatusColor(bid.status)" class="ml-2">
                  {{ formatBidStatus(bid.status) }}
                </UBadge>
              </h4>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Junkshop: {{ bid.junkshop?.name || 'Unknown Junkshop' }}
              </p>
              <div class="grid grid-cols-2 gap-2 mt-2">
                <div>
                  <span class="text-xs text-gray-500 dark:text-gray-500">Offered Price:</span>
                  <p class="text-sm font-medium text-green-600 dark:text-green-400">₱{{ bid.offered_price }} / kg</p>
                </div>
                <div>
                  <span class="text-xs text-gray-500 dark:text-gray-500">Quantity:</span>
                  <p class="text-sm font-medium">{{ bid.offered_quantity }} kg</p>
                </div>
                <div>
                  <span class="text-xs text-gray-500 dark:text-gray-500">Grade:</span>
                  <p class="text-sm font-medium">{{ bid.grade || 'Not specified' }}</p>
                </div>
                <div>
                  <span class="text-xs text-gray-500 dark:text-gray-500">Expires:</span>
                  <p class="text-sm font-medium">{{ formatDate(bid.expiry_date) || 'No expiration' }}</p>
                </div>
              </div>
              <div v-if="bid.message" class="mt-3">
                <span class="text-xs text-gray-500 dark:text-gray-500">Message:</span>
                <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">{{ bid.message }}</p>
              </div>
            </div>
            
            <div class="flex gap-2" v-if="bid.status === 'pending'">
              <UButton
                color="green"
                size="sm"
                icon="i-heroicons-check"
                @click="updateBidStatus(bid.ulid, 'accepted')"
              >
                Accept
              </UButton>
              
              <UButton
                color="red"
                size="sm" 
                icon="i-heroicons-x-mark"
                @click="updateBidStatus(bid.ulid, 'rejected')"
              >
                Reject
              </UButton>
            </div>
          </div>
        </div>
      </div>
    </UCard>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const toast = useToast();
const loading = ref(true);
const receivedBids = ref([]);

// Computed property for pending bids count
const pendingBidsCount = computed(() => {
  return receivedBids.value.filter(bid => bid.status === 'pending').length;
});

// Fetch received bids
const fetchReceivedBids = async () => {
  try {
    loading.value = true;
    const response = await $fetch('/material-bids/received-bids');
    receivedBids.value = response;
  } catch (error) {
    console.error('Failed to fetch received bids:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to load bids.',
      color: 'red'
    });
  } finally {
    loading.value = false;
  }
};

// Update bid status (accept/reject)
const updateBidStatus = async (bidId, status) => {
  try {
    const response = await $fetch(`/material-bids/${bidId}/status`, {
      method: 'PUT',
      body: { status }
    });
    
    // Update the bid in the local array
    const index = receivedBids.value.findIndex(bid => bid.ulid === bidId);
    if (index !== -1) {
      receivedBids.value[index].status = status;
    }
    
    // If accepted, update other bids for the same material
    if (status === 'accepted') {
      const acceptedBid = receivedBids.value[index];
      receivedBids.value.forEach(bid => {
        if (bid.ulid !== bidId && bid.wanted_material_id === acceptedBid.wanted_material_id) {
          bid.status = 'rejected';
        }
      });
    }
    
    toast.add({
      title: 'Success',
      description: `Bid ${status === 'accepted' ? 'accepted' : 'rejected'} successfully.`,
      color: status === 'accepted' ? 'green' : 'blue'
    });
  } catch (error) {
    console.error(`Failed to ${status} bid:`, error);
    toast.add({
      title: 'Error',
      description: `Failed to ${status} bid.`,
      color: 'red'
    });
  }
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

// Get color for bid status badge
const getBidStatusColor = (status) => {
  switch (status) {
    case 'pending': return 'amber';
    case 'accepted': return 'green';
    case 'rejected': return 'red';
    case 'expired': return 'gray';
    default: return 'gray';
  }
};

// Format bid status for display
const formatBidStatus = (status) => {
  return status.charAt(0).toUpperCase() + status.slice(1);
};

// Load data on component mount
onMounted(() => {
  fetchReceivedBids();
});
</script>
