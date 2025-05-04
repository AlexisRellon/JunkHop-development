<template>
  <div class="flex bg-gray-50 dark:bg-gray-900 h-[calc(100vh-4rem)]">
    <!-- Sidebar -->
    <AppMerchantPanelMerchantSidebar v-model="isCollapsed" />
    
    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar transition-all duration-300">
      <!-- Header with Avatar and Welcome -->
      <header class="flex items-center justify-between px-6 py-4 bg-white dark:bg-gray-800 shadow-sm">
        <h1 class="text-xl font-semibold text-gray-800 dark:text-white">Merchant Dashboard</h1>
        <div class="flex items-center gap-4">
          <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Welcome, {{ auth.user?.name }}</span>
          <UAvatar
            size="sm"
            :src="$storage(auth.user?.avatar)"
            :alt="auth.user?.name"
            :ui="{ rounded: 'rounded-full', ring: 'ring-2 ring-emerald-500' }"
          />
        </div>
      </header>

      <div class="p-8">
        <!-- Router view for child routes -->
        <NuxtPage v-if="$route.path !== '/dashboard/merchant'" />
        
        <!-- Dashboard content (only shown on main merchant dashboard) -->
        <div v-if="$route.path === '/dashboard'" >
          <!-- Dashboard Summary Section -->
          <div class="mb-6">
            <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-4">Business Profile</h2>
          
          <!-- Business Profile Section -->
          <UCard v-if="!isLoading" class="dark:bg-gray-800 dark:border-gray-700 shadow-sm">
            <div v-if="merchant.ulid" class="space-y-6">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-xl font-semibold text-gray-800 dark:text-white">{{ merchant.business_name }}</h3>
                  <p class="text-gray-600 dark:text-gray-400 mt-1">{{ merchant.description || 'No description available' }}</p>
                </div>
                <UButton
                  color="blue"
                  variant="ghost"
                  icon="i-heroicons-pencil-square"
                  @click="isEditingProfile = true"
                >
                  Edit Profile
                </UButton>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Contact Information</h4>
                  <div class="space-y-2 text-gray-600 dark:text-gray-400">
                    <div class="flex items-center gap-2">
                      <UIcon name="i-heroicons-map-pin" class="text-teal-500" />
                      <span>{{ merchant.address }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                      <UIcon name="i-heroicons-phone" class="text-teal-500" />
                      <span>{{ merchant.contact }}</span>
                    </div>
                  </div>
                </div>
                
                <div>
                  <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Business Statistics</h4>
                  <div class="grid grid-cols-1 gap-4">
                    <UCard class="bg-amber-50 dark:bg-amber-900/30 border-0">
                      <div class="text-center">
                        <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ interestedItems.length }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Materials of Interest</div>
                      </div>
                    </UCard>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Create Profile Form -->
            <div v-else-if="!isLoading" class="py-4">
              <div class="text-center mb-6">
                <UIcon name="i-heroicons-building-storefront" class="text-teal-500 mx-auto mb-2 w-12 h-12" />
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-1">Create Your Merchant Profile</h3>
                <p class="text-gray-600 dark:text-gray-400">Set up your business profile to start listing material requests</p>
              </div>
              
              <UButton 
                block
                color="teal"
                @click="isCreatingProfile = true"
              >
                Create Profile
              </UButton>
            </div>
            
            <ULoadingOverlay v-if="isLoading" />
          </UCard>
        </div>
        
        <!-- Materials of Interest -->
        <div class="mb-8" v-if="merchant.ulid">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200">Materials of Interest</h2>
            <UButton
              to="/finder"
              color="amber"
              variant="soft"
              size="sm"
              icon="i-heroicons-plus"
            >
              Add Materials
            </UButton>
          </div>
          
          <UCard class="dark:bg-gray-800 dark:border-gray-700 shadow-sm">
            <div v-if="isLoadingItems" class="py-4 flex justify-center">
              <UIcon name="i-heroicons-arrow-path" class="animate-spin text-teal-500 w-6 h-6" />
            </div>
            
            <div v-else-if="interestedItems.length === 0" class="py-8 text-center">
              <UIcon name="i-heroicons-cube-transparent" class="text-gray-400 mx-auto mb-2 w-12 h-12" />
              <h3 class="text-gray-600 dark:text-gray-400 mb-1">No Materials Selected</h3>
              <p class="text-gray-500 dark:text-gray-500 mb-4">You haven't selected any materials of interest yet</p>
              <UButton
                to="/finder"
                color="teal"
                variant="soft"
                size="sm"
                icon="i-heroicons-cube"
              >
                Browse Materials
              </UButton>
            </div>
            
            <div v-else class="flex flex-wrap gap-2 p-4">
              <UBadge 
                v-for="item in interestedItems" 
                :key="item.id" 
                color="amber"
                variant="subtle"
                size="lg"
                class="px-3 py-1"
              >
                {{ item.name }}
                <UButton 
                  class="ml-1" 
                  color="amber" 
                  variant="ghost" 
                  icon="i-heroicons-x-mark" 
                  size="xs"
                  square
                  :ui="{
                    rounded: 'rounded-full',
                    padding: 'p-0.5'
                  }"
                  @click="removeItemInterest(item.id)"
                />
              </UBadge>
            </div>
          </UCard>
        </div>
      </div>
    </div>
    </div>
  </div>
  
  <!-- Create/Edit Profile Modal -->
  <UModal v-model="isCreatingProfile" :ui="{ width: 'sm:max-w-lg' }">
    <UCard class="dark:bg-gray-800">
      <template #header>
        <div class="flex justify-between items-center">
          <h3 class="text-xl font-semibold dark:text-white">
            {{ merchant.ulid ? 'Edit Merchant Profile' : 'Create Merchant Profile' }}
          </h3>
          <UButton
            color="gray" 
            variant="ghost"
            icon="i-heroicons-x-mark"
            @click="isCreatingProfile = false"
            size="sm"
            square
          />
        </div>
      </template>
      
      <UForm :state="formState" class="space-y-4" @submit="saveProfile">
        <UFormGroup label="Business Name" name="business_name">
          <UInput v-model="formState.business_name" placeholder="Your business name" />
        </UFormGroup>
        
        <UFormGroup label="Address" name="address">
          <UInput v-model="formState.address" placeholder="Business address" />
        </UFormGroup>
        
        <UFormGroup label="Contact Number" name="contact">
          <UInput v-model="formState.contact" placeholder="Contact number" />
        </UFormGroup>
        
        <UFormGroup label="Description" name="description">
          <UTextarea 
            v-model="formState.description" 
            placeholder="Describe your business and the materials you're interested in" 
            :rows="3"
          />
        </UFormGroup>
        
        <div class="flex justify-end gap-2 mt-6">
          <UButton
            color="gray"
            variant="ghost"
            @click="isCreatingProfile = false"
          >
            Cancel
          </UButton>
          
          <UButton
            type="submit"
            color="teal"
            :loading="isSaving"
          >
            {{ merchant.ulid ? 'Update Profile' : 'Create Profile' }}
          </UButton>
        </div>
      </UForm>
    </UCard>
  </UModal>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick, watch } from 'vue';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const toast = useToast();
const router = useRouter();
const isCollapsed = ref(false);

// Define role checks
const isUserRole = computed(() => {
  return auth.user?.roles?.includes("user");
});

// User is a merchant
const isMerchant = computed(() => {
  return auth.user?.roles?.includes("merchant");
});

const isAdmin = computed(() => {
  return auth.user?.roles?.includes("admin");
});

// Check if user has any of the allowed roles
const hasAccess = computed(() => {
  return isMerchant.value || isAdmin.value;
});

// Check user role and redirect if necessary
onMounted(() => {
  if (!auth.logged || !hasAccess.value) {
    router.push('/');
    toast.add({
      title: 'Access Denied',
      description: 'You need merchant or admin permissions to access this area.',
      color: 'red'
    });
  } else {
    fetchMerchantProfile();
  }
});

// State variables
const merchant = ref({ 
  ulid: null,
  business_name: '',
  address: '',
  contact: '',
  description: ''
});
const isLoading = ref(true);
const isLoadingItems = ref(false);
const isSaving = ref(false);
const isCreatingProfile = ref(false);
const isEditingProfile = ref(false);
const interestedItems = ref([]);

// Form state
const formState = reactive({
  business_name: '',
  address: '',
  contact: '',
  description: ''
});

// Fetch the merchant's profile
const fetchMerchantProfile = async () => {
  try {
    isLoading.value = true;
    
    // Get merchant profile
    const response = await $fetch('/merchant/profile');
    merchant.value = response;
    
    // If profile exists, populate the form
    if (merchant.value?.ulid) {
      formState.business_name = merchant.value.business_name;
      formState.address = merchant.value.address;
      formState.contact = merchant.value.contact;
      formState.description = merchant.value.description || '';
      
      // Fetch item interests
      fetchInterests();
    }
  } catch (error) {
    console.error('Failed to fetch merchant profile:', error);
    if (error.response?.status !== 404) {
      toast.add({
        title: 'Error',
        description: 'Failed to load merchant profile.',
        color: 'red'
      });
    }
  } finally {
    isLoading.value = false;
  }
};

// Fetch interested items
const fetchInterests = async () => {
  try {
    isLoadingItems.value = true;
    
    // Fetch items this merchant is interested in
    const itemsResponse = await $fetch('/merchant/interested-items');
    interestedItems.value = itemsResponse || [];
  } catch (error) {
    console.error('Failed to fetch interests:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to load interests data.',
      color: 'red'
    });
  } finally {
    isLoadingItems.value = false;
  }
};

// Save or update merchant profile
const saveProfile = async () => {
  try {
    isSaving.value = true;
    
    const method = merchant.value?.ulid ? 'put' : 'post';
    const url = '/merchant/profile';
    
    const response = await $fetch(url, {
      method,
      body: formState
    });
    
    if (response) {
      merchant.value = response.merchant || response;
      isCreatingProfile.value = false;
      
      toast.add({
        title: merchant.value.ulid ? 'Profile Updated' : 'Profile Created',
        description: merchant.value.ulid 
          ? 'Your merchant profile has been updated successfully.' 
          : 'Your merchant profile has been created successfully.',
        color: 'green'
      });
      
      // Refresh connections data if needed
      if (merchant.value.ulid) {
        fetchInterests();
      }
    }
  } catch (error) {
    console.error('Failed to save merchant profile:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to save merchant profile. Please try again.',
      color: 'red'
    });
  } finally {
    isSaving.value = false;
  }
};

// Handle remove item interest
const removeItemInterest = async (itemId) => {
  try {
    const response = await $fetch(`/merchant/item-interest/${itemId}`, {
      method: 'POST',
    });
    
    if (response.status === 'removed') {
      // Remove the item from the interests list
      interestedItems.value = interestedItems.value.filter(item => item.id !== itemId);
      
      toast.add({
        title: 'Interest Removed',
        description: 'You have removed your interest in this material.',
        color: 'gray'
      });
    }
  } catch (error) {
    console.error('Failed to remove item interest:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to remove interest. Please try again.',
      color: 'red'
    });
  }
};

// Watch for changes to the isEditingProfile value
watch(isEditingProfile, (newVal) => {
  if (newVal) {
    isCreatingProfile.value = true;
  }
});
</script>

<style scoped>
/* Custom scrollbar styling */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.5);
  border-radius: 20px;
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(75, 85, 99, 0.5);
}
</style>