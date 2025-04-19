<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Material Requests</h2>
      <UButton 
        color="teal" 
        @click="showNewRequestModal = true"
        icon="i-heroicons-plus"
      >
        New Request
      </UButton>
    </div>
    
    <!-- Loading state -->
    <div v-if="loading" class="py-12 flex justify-center">
      <UIcon name="i-heroicons-arrow-path" class="animate-spin text-teal-500 w-8 h-8" />
    </div>
    
    <!-- Empty state -->
    <UCard v-else-if="myMaterialRequests.length === 0">
      <div class="py-12 text-center">
        <UIcon name="i-heroicons-shopping-bag" class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" />
        <h3 class="mt-2 text-base font-semibold text-gray-900 dark:text-white">No material requests</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Create your first material request to start connecting with junkshops.
        </p>
        <div class="mt-6">
          <UButton
            color="teal"
            @click="showNewRequestModal = true"
            icon="i-heroicons-plus"
          >
            Create Your First Request
          </UButton>
        </div>
      </div>
    </UCard>
    
    <!-- List of material requests -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <UCard
        v-for="request in myMaterialRequests" 
        :key="request.ulid"
        :class="!request.is_active ? 'opacity-75 border-dashed' : ''"
        class="dark:bg-gray-800 transition-all"
      >
        <template #header>
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold flex items-center gap-2">
              {{ request.item?.name || 'Material' }}
              <UBadge v-if="!request.is_active" color="gray" class="text-xs">Inactive</UBadge>
            </h3>
            <UDropdown :items="getRequestActions(request)">
              <UButton color="gray" variant="ghost" icon="i-heroicons-ellipsis-vertical" size="xs" />
            </UDropdown>
          </div>
        </template>
        
        <div class="space-y-2">
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Quantity:</span>
            <span class="font-medium">{{ request.quantity }} kg</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Desired Price:</span>
            <span class="font-medium text-green-600 dark:text-green-400">₱{{ request.desired_price }} / kg</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Grade:</span>
            <span class="font-medium">{{ request.grade || 'Any' }}</span>
          </div>
          <div class="flex justify-between" v-if="request.deadline">
            <span class="text-gray-600 dark:text-gray-400">Deadline:</span>
            <span class="font-medium">{{ formatDate(request.deadline) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-400">Visibility:</span>
            <span class="font-medium">{{ request.is_public ? 'Public' : 'Private' }}</span>
          </div>
          <div v-if="request.description" class="mt-3">
            <div class="text-gray-600 dark:text-gray-400 text-sm">Description:</div>
            <p class="text-gray-800 dark:text-gray-300 text-sm mt-1">{{ request.description }}</p>
          </div>
        </div>
        
        <template #footer>
          <div class="flex justify-between">
            <UButton
              @click="viewRequestDetails(request)"
              color="blue"
              variant="ghost"
              icon="i-heroicons-information-circle"
              size="sm"
            >
              View Details
            </UButton>
            
            <UButton
              @click="viewRequestBids(request)"
              color="teal"
              variant="ghost"
              icon="i-heroicons-document-text"
              size="sm"
              :disabled="!request.bid_count"
            >
              {{ request.bid_count || 0 }} {{ request.bid_count === 1 ? 'Bid' : 'Bids' }}
            </UButton>
          </div>
        </template>
      </UCard>
    </div>
    
    <!-- New/Edit Material Request Modal -->
    <UModal v-model="showNewRequestModal" :ui="{ width: 'sm:max-w-lg' }">
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
              @click="closeRequestModal"
              size="sm"
              square
            />
          </div>
        </template>
        
        <UForm :state="formState" class="space-y-4" @submit="saveRequest">
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
          
          <UFormGroup label="Material Grade" name="grade">
            <USelect
              v-model="formState.grade"
              :options="gradeOptions"
              option-attribute="label"
              value-attribute="value"
              placeholder="Select material grade preference (optional)"
            />
          </UFormGroup>
          
          <UFormGroup label="Request Deadline" name="deadline">
            <UInput 
              v-model="formState.deadline" 
              type="date"
              :min="minDate"
            />
          </UFormGroup>
          
          <UFormGroup label="Description (Optional)" name="description">
            <UTextarea 
              v-model="formState.description" 
              placeholder="Describe your requirements for this material"
              :rows="3"
            />
          </UFormGroup>
          
          <UFormGroup name="is_public">
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
              @click="closeRequestModal"
            >
              Cancel
            </UButton>
            
            <UButton
              type="submit"
              color="teal"
              :loading="isSaving"
            >
              {{ editMode ? 'Update Request' : 'Create Request' }}
            </UButton>
          </div>
        </UForm>
      </UCard>
    </UModal>
    
    <!-- Request Details Modal -->
    <UModal v-model="showDetailsModal" v-if="selectedRequest">
      <UCard>
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
            <h4 class="text-lg font-medium text-gray-800 dark:text-white">{{ selectedRequest.item?.name }}</h4>
            <UBadge v-if="!selectedRequest.is_active" color="gray" class="ml-2">Inactive</UBadge>
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <div class="text-sm text-gray-500 dark:text-gray-400">Quantity Needed</div>
              <div class="font-medium">{{ selectedRequest.quantity }} kg</div>
            </div>
            <div>
              <div class="text-sm text-gray-500 dark:text-gray-400">Desired Price</div>
              <div class="font-medium text-green-600 dark:text-green-400">₱{{ selectedRequest.desired_price }} / kg</div>
            </div>
            <div>
              <div class="text-sm text-gray-500 dark:text-gray-400">Grade Preference</div>
              <div class="font-medium">{{ selectedRequest.grade || 'Any' }}</div>
            </div>
            <div v-if="selectedRequest.deadline">
              <div class="text-sm text-gray-500 dark:text-gray-400">Deadline</div>
              <div class="font-medium">{{ formatDate(selectedRequest.deadline) }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-500 dark:text-gray-400">Visibility</div>
              <div class="font-medium">{{ selectedRequest.is_public ? 'Public' : 'Private' }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-500 dark:text-gray-400">Status</div>
              <div class="font-medium">{{ selectedRequest.is_active ? 'Active' : 'Inactive' }}</div>
            </div>
          </div>
          
          <div v-if="selectedRequest.description">
            <div class="text-sm text-gray-500 dark:text-gray-400">Description</div>
            <p class="text-gray-800 dark:text-gray-300 mt-1">{{ selectedRequest.description }}</p>
          </div>
          
          <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
              Request Statistics
            </div>
            <div class="grid grid-cols-3 gap-4 mt-2">
              <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Bids</div>
                <div class="text-lg font-semibold text-teal-600 dark:text-teal-400">{{ selectedRequest.bid_count || 0 }}</div>
              </div>
              <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg">
                <div class="text-sm text-gray-500 dark:text-gray-400">Accepted</div>
                <div class="text-lg font-semibold text-green-600 dark:text-green-400">{{ selectedRequest.accepted_bids || 0 }}</div>
              </div>
              <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg">
                <div class="text-sm text-gray-500 dark:text-gray-400">Pending</div>
                <div class="text-lg font-semibold text-amber-600 dark:text-amber-400">{{ selectedRequest.pending_bids || 0 }}</div>
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
            
            <div class="flex gap-2">
              <UButton
                color="teal"
                variant="soft"
                icon="i-heroicons-document-text"
                @click="viewRequestBids(selectedRequest)"
                :disabled="!selectedRequest.bid_count"
              >
                View Bids
              </UButton>
              
              <UButton
                color="blue"
                variant="soft"
                icon="i-heroicons-pencil-square"
                @click="editRequest(selectedRequest)"
              >
                Edit
              </UButton>
            </div>
          </div>
        </template>
      </UCard>
    </UModal>
    
    <!-- View Bids Modal -->
    <UModal v-model="showBidsModal" v-if="selectedRequest" :ui="{ width: 'sm:max-w-3xl' }">
      <UCard>
        <template #header>
          <div class="flex justify-between items-center">
            <h3 class="text-xl font-semibold dark:text-white">
              Bids for {{ selectedRequest.item?.name }}
            </h3>
            <UButton
              color="gray" 
              variant="ghost"
              icon="i-heroicons-x-mark"
              @click="showBidsModal = false"
              size="sm"
              square
            />
          </div>
        </template>
        
        <div v-if="loadingBids" class="py-4 flex justify-center">
          <UIcon name="i-heroicons-arrow-path" class="animate-spin text-teal-500 w-6 h-6" />
        </div>
        
        <div v-else-if="requestBids.length === 0" class="py-8 text-center">
          <UIcon name="i-heroicons-inbox" class="text-gray-400 mx-auto mb-2 w-12 h-12" />
          <h3 class="text-gray-600 dark:text-gray-400 mb-1">No Bids Received</h3>
          <p class="text-gray-500 dark:text-gray-500 mb-4">You haven't received any bids on this material request yet</p>
        </div>
        
        <div v-else class="space-y-4">
          <div 
            v-for="bid in requestBids" 
            :key="bid.ulid"
            class="border dark:border-gray-700 rounded-lg p-4 transition-all hover:bg-gray-50 dark:hover:bg-gray-800/50"
          >
            <div class="flex justify-between items-start">
              <div>
                <h4 class="font-medium text-gray-800 dark:text-gray-200">
                  {{ bid.junkshop?.name || 'Unknown Junkshop' }}
                  <UBadge :color="getBidStatusColor(bid.status)" class="ml-2">
                    {{ formatBidStatus(bid.status) }}
                  </UBadge>
                </h4>
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
                    <span class="text-xs text-gray-500 dark:text-gray-500">Submitted:</span>
                    <p class="text-sm font-medium">{{ formatDate(bid.created_at) }}</p>
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
        
        <template #footer>
          <div class="flex justify-end">
            <UButton
              color="gray"
              variant="ghost"
              @click="showBidsModal = false"
            >
              Close
            </UButton>
          </div>
        </template>
      </UCard>
    </UModal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';

const toast = useToast();

// State variables
const loading = ref(true);
const loadingBids = ref(false);
const isSaving = ref(false);
const myMaterialRequests = ref([]);
const requestBids = ref([]);
const selectedRequest = ref(null);

// Modal controls
const showNewRequestModal = ref(false);
const showDetailsModal = ref(false);
const showBidsModal = ref(false);
const editMode = ref(false);

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

// Options for select inputs
const itemOptions = ref([]);
const gradeOptions = [
  { label: 'Any Grade', value: null },
  { label: 'Grade A (Premium)', value: 'A' },
  { label: 'Grade B (Standard)', value: 'B' },
  { label: 'Grade C (Basic)', value: 'C' }
];

// Computed property for minimum date (tomorrow)
const minDate = computed(() => {
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  return tomorrow.toISOString().split('T')[0];
});

// Fetch all available material items
const fetchItems = async () => {
  try {
    const response = await $fetch('/materials');
    itemOptions.value = response.map(item => ({
      label: item.name,
      value: item.id
    }));
  } catch (error) {
    console.error('Failed to fetch materials', error);
    toast.add({
      title: 'Error',
      description: 'Failed to load material types',
      color: 'red'
    });
  }
};

// Fetch the merchant's material requests
const fetchMyRequests = async () => {
  try {
    loading.value = true;
    const response = await $fetch('/marketplace/my-listings');
    myMaterialRequests.value = response;
  } catch (error) {
    console.error('Failed to fetch your material requests', error);
    toast.add({
      title: 'Error',
      description: 'Failed to load your material requests',
      color: 'red'
    });
  } finally {
    loading.value = false;
  }
};

// Fetch bids for a specific material request
const fetchRequestBids = async (requestId) => {
  try {
    loadingBids.value = true;
    const response = await $fetch(`/material-bids/wanted-material/${requestId}`);
    requestBids.value = response;
  } catch (error) {
    console.error('Failed to fetch bids', error);
    toast.add({
      title: 'Error',
      description: 'Failed to load bids for this request',
      color: 'red'
    });
  } finally {
    loadingBids.value = false;
  }
};

// Save a new or updated material request
const saveRequest = async () => {
  try {
    isSaving.value = true;
    
    if (editMode.value) {
      // Update existing request
      await $fetch(`/marketplace/wanted-materials/${selectedRequest.value.ulid}`, {
        method: 'PUT',
        body: formState
      });
      
      toast.add({
        title: 'Success',
        description: 'Material request updated successfully',
        color: 'green'
      });
    } else {
      // Create new request
      await $fetch('/marketplace/wanted-materials', {
        method: 'POST',
        body: formState
      });
      
      toast.add({
        title: 'Success',
        description: 'Material request created successfully',
        color: 'green'
      });
    }
    
    // Refresh the list and close modal
    await fetchMyRequests();
    closeRequestModal();
  } catch (error) {
    console.error('Failed to save material request', error);
    toast.add({
      title: 'Error',
      description: 'Failed to save material request',
      color: 'red'
    });
  } finally {
    isSaving.value = false;
  }
};

// Edit an existing request
const editRequest = (request) => {
  selectedRequest.value = request;
  editMode.value = true;
  
  // Populate form with request data
  formState.item_id = request.item_id;
  formState.quantity = request.quantity;
  formState.desired_price = request.desired_price;
  formState.grade = request.grade;
  formState.deadline = request.deadline;
  formState.description = request.description || '';
  formState.is_public = request.is_public;
  
  // Close details modal if open
  showDetailsModal.value = false;
  // Open edit modal
  showNewRequestModal.value = true;
};

// Toggle the active status of a request
const toggleRequestActive = async (request) => {
  try {
    await $fetch(`/marketplace/wanted-materials/${request.ulid}/toggle-active`, {
      method: 'POST'
    });
    
    // Update request in local array
    const index = myMaterialRequests.value.findIndex(r => r.ulid === request.ulid);
    if (index !== -1) {
      myMaterialRequests.value[index].is_active = !myMaterialRequests.value[index].is_active;
    }
    
    toast.add({
      title: 'Success',
      description: `Request ${myMaterialRequests.value[index].is_active ? 'activated' : 'deactivated'} successfully`,
      color: 'blue'
    });
  } catch (error) {
    console.error('Failed to toggle request status', error);
    toast.add({
      title: 'Error',
      description: 'Failed to update request status',
      color: 'red'
    });
  }
};

// Delete a material request
const deleteRequest = async (request) => {
  if (confirm('Are you sure you want to delete this material request?')) {
    try {
      await $fetch(`/marketplace/wanted-materials/${request.ulid}`, {
        method: 'DELETE'
      });
      
      // Remove from local array
      myMaterialRequests.value = myMaterialRequests.value.filter(r => r.ulid !== request.ulid);
      
      toast.add({
        title: 'Success',
        description: 'Material request deleted successfully',
        color: 'blue'
      });
    } catch (error) {
      console.error('Failed to delete request', error);
      toast.add({
        title: 'Error',
        description: 'Failed to delete material request',
        color: 'red'
      });
    }
  }
};

// View request details
const viewRequestDetails = (request) => {
  selectedRequest.value = request;
  showDetailsModal.value = true;
};

// View bids for a request
const viewRequestBids = async (request) => {
  selectedRequest.value = request;
  showBidsModal.value = true;
  await fetchRequestBids(request.ulid);
};

// Update a bid's status (accept/reject)
const updateBidStatus = async (bidId, status) => {
  try {
    await $fetch(`/material-bids/${bidId}/status`, {
      method: 'PUT',
      body: { status }
    });
    
    // Update bid in local array
    const index = requestBids.value.findIndex(b => b.ulid === bidId);
    if (index !== -1) {
      requestBids.value[index].status = status;
    }
    
    // If accepted, update other bids
    if (status === 'accepted') {
      requestBids.value.forEach(bid => {
        if (bid.ulid !== bidId) {
          bid.status = 'rejected';
        }
      });
      
      // Update request stats
      if (selectedRequest.value) {
        selectedRequest.value.accepted_bids = 1;
        selectedRequest.value.pending_bids = 0;
      }
    }
    
    toast.add({
      title: 'Success',
      description: `Bid ${status === 'accepted' ? 'accepted' : 'rejected'} successfully`,
      color: status === 'accepted' ? 'green' : 'blue'
    });
  } catch (error) {
    console.error('Failed to update bid status', error);
    toast.add({
      title: 'Error',
      description: 'Failed to update bid status',
      color: 'red'
    });
  }
};

// Close the request modal and reset form
const closeRequestModal = () => {
  // Reset form
  formState.item_id = null;
  formState.quantity = null;
  formState.desired_price = null;
  formState.grade = null;
  formState.deadline = null;
  formState.description = '';
  formState.is_public = true;
  
  // Reset edit mode
  editMode.value = false;
  selectedRequest.value = null;
  
  // Close modal
  showNewRequestModal.value = false;
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

// Get actions for material request dropdown menu
const getRequestActions = (request) => {
  return [
    {
      label: 'Edit',
      icon: 'i-heroicons-pencil-square',
      click: () => editRequest(request)
    },
    {
      label: request.is_active ? 'Deactivate' : 'Activate',
      icon: request.is_active ? 'i-heroicons-pause' : 'i-heroicons-play',
      click: () => toggleRequestActive(request)
    },
    {
      label: 'Delete',
      icon: 'i-heroicons-trash',
      click: () => deleteRequest(request)
    },
    {
      label: 'View Bids',
      icon: 'i-heroicons-document-text',
      click: () => viewRequestBids(request),
      disabled: !request.bid_count
    }
  ];
};

// Load data on component mount
onMounted(async () => {
  await Promise.all([
    fetchItems(),
    fetchMyRequests()
  ]);
});
</script>
