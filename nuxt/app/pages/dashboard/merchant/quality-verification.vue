<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Material Quality Verification</h1>
    
    <UCard class="mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200">Material Quality Standards</h2>
        <UBadge color="teal" variant="soft">New Feature</UBadge>
      </div>
      
      <p class="text-gray-600 dark:text-gray-400 mb-4">
        Ensure the quality of materials you source from junkshops with our verification tools. Upload photos, verify material grades, 
        and maintain quality standards for your business needs.
      </p>
    </UCard>

    <!-- Grade Reference Guide -->
    <UCard class="mb-6">
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200">Material Grading Guide</h3>
          <UButton color="teal" variant="ghost" icon="i-heroicons-information-circle" @click="showGradeInfo = true">
            Learn More
          </UButton>
        </div>
      </template>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <UCard class="bg-green-50 dark:bg-green-900/30 border-0">
          <div class="text-center">
            <h4 class="text-lg font-semibold text-green-700 dark:text-green-400">Grade A</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Premium quality materials with minimal contamination (<5%)</p>
          </div>
        </UCard>
        
        <UCard class="bg-yellow-50 dark:bg-yellow-900/30 border-0">
          <div class="text-center">
            <h4 class="text-lg font-semibold text-yellow-700 dark:text-yellow-400">Grade B</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Good quality materials with moderate contamination (5-15%)</p>
          </div>
        </UCard>
        
        <UCard class="bg-orange-50 dark:bg-orange-900/30 border-0">
          <div class="text-center">
            <h4 class="text-lg font-semibold text-orange-700 dark:text-orange-400">Grade C</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Basic quality materials with significant contamination (>15%)</p>
          </div>
        </UCard>
      </div>
    </UCard>

    <!-- My Connected Junkshops with Material Quality -->
    <UCard class="mb-6">
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200">Connected Junkshops Quality Check</h3>
          <UButton 
            :to="'/dashboard/merchant/connections'" 
            color="blue" 
            variant="ghost" 
            icon="i-heroicons-plus-circle"
          >
            Manage Connections
          </UButton>
        </div>
      </template>
      
      <!-- Loading state -->
      <div v-if="loading" class="flex justify-center py-8">
        <UIcon name="i-heroicons-arrow-path" class="animate-spin text-teal-500 w-8 h-8" />
      </div>
      
      <!-- No connections state -->
      <div v-else-if="connections.length === 0" class="text-center py-8">
        <UIcon name="i-heroicons-building-storefront" class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600 mb-3" />
        <h4 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">No Connected Junkshops</h4>
        <p class="text-gray-600 dark:text-gray-400 mb-4">
          Connect with junkshops to verify material quality and standards.
        </p>
        <UButton :to="'/finder'" color="teal" icon="i-heroicons-map">
          Find Junkshops
        </UButton>
      </div>
      
      <!-- Connection list -->
      <div v-else>
        <UTable :rows="connections" :columns="connectionColumns">
          <template #junkshop-data="{ row }">
            <div class="flex items-center gap-2">
              <UAvatar
                :src="row.logo ? $storage(row.logo) : ''"
                :alt="row.name"
                size="sm"
                :fallback="row.name?.charAt(0) || 'J'"
                color="teal"
              />
              <span>{{ row.name }}</span>
            </div>
          </template>
          <template #materials-data="{ row }">
            <div class="flex flex-wrap gap-1">
              <UBadge
                v-for="(item, i) in (row.items?.slice(0, 3) || [])"
                :key="i"
                color="gray"
                size="xs"
              >
                {{ item.name }}
              </UBadge>
              <UBadge v-if="row.items?.length > 3" color="gray" size="xs">
                +{{ row.items.length - 3 }} more
              </UBadge>
            </div>
          </template>
          <template #actions-data="{ row }">
            <UButton
              color="teal"
              variant="soft"
              size="xs"
              icon="i-heroicons-camera"
              @click="openVerificationModal(row)"
            >
              Verify Items
            </UButton>
          </template>
        </UTable>
      </div>
    </UCard>

    <!-- Material Verification Requests -->
    <UCard>
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200">Verification Requests</h3>
          <UBadge v-if="pendingVerifications.length > 0" color="amber" size="sm">{{ pendingVerifications.length }} pending</UBadge>
        </div>
      </template>
      
      <!-- Loading state -->
      <div v-if="loading" class="flex justify-center py-8">
        <UIcon name="i-heroicons-arrow-path" class="animate-spin text-teal-500 w-8 h-8" />
      </div>
      
      <!-- Empty verifications state -->
      <div v-else-if="verificationRequests.length === 0" class="text-center py-8">
        <UIcon name="i-heroicons-document-check" class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600 mb-3" />
        <h4 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">No Verification Requests</h4>
        <p class="text-gray-600 dark:text-gray-400">
          High-value transactions will appear here for quality verification.
        </p>
      </div>
      
      <!-- Verification requests list -->
      <div v-else class="space-y-4">
        <div 
          v-for="request in verificationRequests" 
          :key="request.id"
          class="border dark:border-gray-700 rounded-lg p-4 transition-all hover:bg-gray-50 dark:hover:bg-gray-800/50"
        >
          <div class="flex justify-between items-start">
            <div>
              <h4 class="font-medium text-gray-800 dark:text-gray-200">
                {{ request.itemName }}
                <UBadge :color="getStatusColor(request.status)" class="ml-2">
                  {{ request.status }}
                </UBadge>
              </h4>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Junkshop: {{ request.junkshopName }}
              </p>
              <div class="flex items-center gap-2 mt-1">
                <UIcon name="i-heroicons-scale" class="text-gray-500" />
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ request.quantity }} kg</span>
              </div>
              <div class="flex items-center gap-2 mt-1">
                <UIcon name="i-heroicons-currency-dollar" class="text-gray-500" />
                <span class="text-sm text-gray-600 dark:text-gray-400">₱{{ request.price.toFixed(2) }}</span>
              </div>
            </div>
            
            <div class="flex flex-col gap-2">
              <UButton 
                v-if="request.status === 'pending'"
                color="teal" 
                variant="soft" 
                size="sm" 
                icon="i-heroicons-check"
                @click="verifyRequest(request.id, 'approved')"
              >
                Approve
              </UButton>
              
              <UButton 
                v-if="request.status === 'pending'"
                color="red" 
                variant="soft" 
                size="sm" 
                icon="i-heroicons-x-mark"
                @click="verifyRequest(request.id, 'rejected')"
              >
                Reject
              </UButton>
              
              <UButton
                color="blue"
                variant="ghost"
                size="sm"
                icon="i-heroicons-photo"
                @click="viewVerificationPhotos(request)"
              >
                View Photos
              </UButton>
            </div>
          </div>
        </div>
      </div>
    </UCard>
  </div>
  
  <!-- Material Grade Info Modal -->
  <UModal v-model="showGradeInfo">
    <UCard>
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200">Material Grading System</h3>
          <UButton color="gray" variant="ghost" icon="i-heroicons-x-mark" @click="showGradeInfo = false" />
        </div>
      </template>
      
      <div class="space-y-6">
        <div>
          <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Grade A (Premium)</h4>
          <ul class="list-disc pl-5 space-y-1 text-gray-600 dark:text-gray-400">
            <li>Clean, sorted materials with minimal contamination (less than 5%)</li>
            <li>No mixed materials or foreign substances</li>
            <li>Ready for direct recycling or manufacturing</li>
            <li>Fetches highest market prices</li>
          </ul>
        </div>
        
        <div>
          <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Grade B (Standard)</h4>
          <ul class="list-disc pl-5 space-y-1 text-gray-600 dark:text-gray-400">
            <li>Mostly clean materials with some contamination (5-15%)</li>
            <li>May have small amounts of mixed materials</li>
            <li>Requires minimal processing before recycling</li>
            <li>Fetches standard market prices</li>
          </ul>
        </div>
        
        <div>
          <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Grade C (Basic)</h4>
          <ul class="list-disc pl-5 space-y-1 text-gray-600 dark:text-gray-400">
            <li>Materials with significant contamination (more than 15%)</li>
            <li>Mixed materials requiring substantial sorting</li>
            <li>Requires extensive processing before recycling</li>
            <li>Fetches lower market prices</li>
          </ul>
        </div>
      </div>
      
      <template #footer>
        <div class="flex justify-end">
          <UButton color="teal" @click="showGradeInfo = false">Got it</UButton>
        </div>
      </template>
    </UCard>
  </UModal>
  
  <!-- Verification Modal -->
  <UModal v-model="showVerificationModal" :ui="{ width: 'sm:max-w-xl' }">
    <UCard>
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200">
            Verify Materials from {{ selectedJunkshop?.name }}
          </h3>
          <UButton color="gray" variant="ghost" icon="i-heroicons-x-mark" @click="showVerificationModal = false" />
        </div>
      </template>
      
      <div v-if="selectedJunkshop">
        <div class="mb-4">
          <p class="text-gray-600 dark:text-gray-400">
            Select materials to verify and request quality photos for high-value transactions.
          </p>
        </div>
        
        <div class="space-y-4 max-h-96 overflow-y-auto">
          <div 
            v-for="item in selectedJunkshop.items" 
            :key="item.id"
            class="border dark:border-gray-700 rounded-lg p-4"
          >
            <div class="flex items-center justify-between">
              <div>
                <h4 class="font-medium text-gray-800 dark:text-gray-200">{{ item.name }}</h4>
                
                <div class="flex items-center gap-4 mt-2">
                  <div class="flex items-center gap-2">
                    <UIcon name="i-heroicons-tag" class="text-gray-500" />
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                      Grade: {{ item.grade || 'Not specified' }}
                    </span>
                  </div>
                  
                  <div class="flex items-center gap-2">
                    <UIcon name="i-heroicons-currency-dollar" class="text-gray-500" />
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                      ₱{{ item.price?.toFixed(2) || '0.00' }} / kg
                    </span>
                  </div>
                </div>
              </div>
              
              <div>
                <UButton
                  color="teal"
                  variant="soft"
                  size="sm"
                  icon="i-heroicons-camera"
                  @click="requestVerification(item)"
                >
                  Request Photos
                </UButton>
              </div>
            </div>
            
            <!-- Grade selection -->
            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Verify Material Grade
              </label>
              <URadioGroup 
                v-model="item.verifiedGrade" 
                orientation="horizontal"
                class="gap-4"
                size="sm"
              >
                <URadio 
                  v-for="grade in gradeOptions" 
                  :key="grade.value" 
                  :label="grade.label" 
                  :value="grade.value" 
                  :color="grade.color"
                />
              </URadioGroup>
            </div>
          </div>
        </div>
      </div>
      
      <template #footer>
        <div class="flex justify-end gap-2">
          <UButton color="gray" variant="ghost" @click="showVerificationModal = false">Cancel</UButton>
          <UButton color="teal" @click="saveVerifications">Save Verifications</UButton>
        </div>
      </template>
    </UCard>
  </UModal>
  
  <!-- Photo Verification Modal -->
  <UModal v-model="showPhotoModal">
    <UCard>
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200">
            Material Photos
          </h3>
          <UButton color="gray" variant="ghost" icon="i-heroicons-x-mark" @click="showPhotoModal = false" />
        </div>
      </template>
      
      <div class="space-y-4">
        <div v-if="selectedRequest && selectedRequest.photos && selectedRequest.photos.length > 0">
          <div class="grid grid-cols-1 gap-4">
            <div 
              v-for="(photo, index) in selectedRequest.photos" 
              :key="index" 
              class="border dark:border-gray-700 rounded-lg overflow-hidden"
            >
              <img 
                :src="photo" 
                :alt="`Material photo ${index + 1}`" 
                class="w-full h-auto object-cover"
              />
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8">
          <UIcon name="i-heroicons-photo" class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600 mb-3" />
          <p class="text-gray-600 dark:text-gray-400">No photos available for this verification request.</p>
        </div>
      </div>
      
      <template #footer>
        <div class="flex justify-end">
          <UButton color="teal" @click="showPhotoModal = false">Close</UButton>
        </div>
      </template>
    </UCard>
  </UModal>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const toast = useToast();
const loading = ref(true);
const connections = ref([]);
const verificationRequests = ref([]);
const showGradeInfo = ref(false);
const showVerificationModal = ref(false);
const showPhotoModal = ref(false);
const selectedJunkshop = ref(null);
const selectedRequest = ref(null);

// Grade options for material verification
const gradeOptions = [
  { label: 'Grade A', value: 'A', color: 'green' },
  { label: 'Grade B', value: 'B', color: 'yellow' },
  { label: 'Grade C', value: 'C', color: 'orange' }
];

// Column definitions for the junkshop connections table
const connectionColumns = [
  { key: 'junkshop', label: 'Junkshop' },
  { key: 'address', label: 'Address' },
  { key: 'materials', label: 'Materials' },
  { key: 'actions', label: 'Actions' }
];

// Computed property for pending verification requests
const pendingVerifications = computed(() => {
  return verificationRequests.value.filter(req => req.status === 'pending');
});

// Function to get status color based on verification status
const getStatusColor = (status) => {
  switch (status) {
    case 'approved':
      return 'green';
    case 'rejected':
      return 'red';
    case 'pending':
    default:
      return 'amber';
  }
};

// Fetch connected junkshops
const fetchConnections = async () => {
  try {
    loading.value = true;
    const response = await $fetch('/merchant/connected-junkshops');
    connections.value = response || [];
  } catch (error) {
    console.error('Error fetching junkshop connections:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to load junkshop connections.',
      color: 'red'
    });
  } finally {
    loading.value = false;
  }
};

// Fetch verification requests
const fetchVerificationRequests = async () => {
  try {
    loading.value = true;
    
    // This would be replaced with an actual API call in a production environment
    // For demo purposes, we're creating mock data
    setTimeout(() => {
      verificationRequests.value = [
        {
          id: 1,
          junkshopId: 'junkshop1',
          junkshopName: 'Green Recyclers',
          itemId: 101,
          itemName: 'Copper Wire (High Grade)',
          quantity: 250,
          price: 120.00,
          grade: 'A',
          status: 'pending',
          createdAt: new Date(),
          photos: [
            'https://placehold.co/600x400/png?text=Material+Photo+1',
            'https://placehold.co/600x400/png?text=Material+Photo+2'
          ]
        },
        {
          id: 2,
          junkshopId: 'junkshop2',
          junkshopName: 'Metro Scrap Buyers',
          itemId: 102,
          itemName: 'Aluminum Cans',
          quantity: 500,
          price: 45.50,
          grade: 'B',
          status: 'approved',
          createdAt: new Date(Date.now() - 86400000), // 1 day ago
          photos: [
            'https://placehold.co/600x400/png?text=Material+Photo+3'
          ]
        }
      ];
      loading.value = false;
    }, 1000);
  } catch (error) {
    console.error('Error fetching verification requests:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to load verification requests.',
      color: 'red'
    });
    loading.value = false;
  }
};

// Open the verification modal for a specific junkshop
const openVerificationModal = (junkshop) => {
  selectedJunkshop.value = junkshop;
  
  // Add a verification grade property to each item for UI binding
  if (selectedJunkshop.value && selectedJunkshop.value.items) {
    selectedJunkshop.value.items.forEach(item => {
      item.verifiedGrade = item.grade || '';
    });
  }
  
  showVerificationModal.value = true;
};

// Request verification photos for a specific item
const requestVerification = (item) => {
  toast.add({
    title: 'Verification Requested',
    description: `Photo verification requested for ${item.name}`,
    color: 'blue'
  });
  
  // This would trigger a notification to the junkshop in a real implementation
};

// Save all verifications for the selected junkshop
const saveVerifications = async () => {
  try {
    // In a real implementation, this would save to the backend
    // For demo purposes, we'll just show a success message
    
    toast.add({
      title: 'Verifications Saved',
      description: 'Material grades have been verified successfully.',
      color: 'green'
    });
    
    showVerificationModal.value = false;
  } catch (error) {
    console.error('Error saving verifications:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to save verifications.',
      color: 'red'
    });
  }
};

// View verification photos for a specific request
const viewVerificationPhotos = (request) => {
  selectedRequest.value = request;
  showPhotoModal.value = true;
};

// Approve or reject a verification request
const verifyRequest = async (requestId, status) => {
  try {
    // In a real implementation, this would update the status in the backend
    
    // Update local state for demo purposes
    const request = verificationRequests.value.find(req => req.id === requestId);
    if (request) {
      request.status = status;
    }
    
    toast.add({
      title: status === 'approved' ? 'Request Approved' : 'Request Rejected',
      description: `The verification request has been ${status}.`,
      color: status === 'approved' ? 'green' : 'red'
    });
  } catch (error) {
    console.error(`Error ${status} verification request:`, error);
    toast.add({
      title: 'Error',
      description: `Failed to ${status} the verification request.`,
      color: 'red'
    });
  }
};

// Initialize component
onMounted(() => {
  fetchConnections();
  fetchVerificationRequests();
});

// Define the parent layout
definePageMeta({
  layout: 'dashboard'
});
</script>
