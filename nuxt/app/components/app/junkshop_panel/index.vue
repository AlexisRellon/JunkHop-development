<template>
  <div class="flex bg-gray-50 dark:bg-gray-900">
    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar">
      <!-- Header with Avatar and Welcome -->
      <header class="flex items-center justify-between px-6 py-4 bg-white dark:bg-gray-800 shadow-sm">
        <h1 class="text-xl font-semibold text-gray-800 dark:text-white">Junkshop Dashboard</h1>
        <div class="flex items-center gap-4">
          <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Welcome, {{ auth.user.name }}</span>
          <UAvatar
            size="sm"
            :src="$storage ? $storage(auth.user.avatar) : ''"
            :alt="auth.user.name"
            :ui="{ rounded: 'rounded-full', ring: 'ring-2 ring-emerald-500' }"
          />
        </div>
      </header>

      <div class="p-6">
        <!-- Dashboard Summary Section -->
        <div class="mb-6">
          <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-4">Junkshop Overview</h2>
          <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <!-- Card 1: Junkshop Status -->
            <UCard class="relative overflow-hidden rounded-lg shadow-sm border-0">
              <div class="p-5 bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/30 dark:to-emerald-800/30">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-sm font-medium text-emerald-600 dark:text-emerald-300">Junkshop Status</p>
                    <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-1">
                      {{ junkshop.ulid ? 'Active' : 'Not Created' }}
                    </h3>
                  </div>
                  <div class="p-3 bg-emerald-500 rounded-full bg-opacity-10 w-[48px] h-[48px] flex justify-center items-center dark:bg-opacity-20">
                    <UIcon
                      name="mdi-home"
                      class="text-emerald-600 dark:text-emerald-300"
                      size="lg"
                    />
                  </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-emerald-500 dark:text-emerald-300">
                  <UIcon name="i-heroicons-information-circle" class="mr-1" />
                  <span>{{ junkshop.ulid ? 'Your junkshop is visible to users' : 'Set up your junkshop' }}</span>
                </div>
              </div>
            </UCard>
            
            <!-- Card 2: Items Offered -->
            <UCard class="relative overflow-hidden rounded-lg shadow-sm border-0">
              <div class="p-5 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-sm font-medium text-blue-600 dark:text-blue-300">Items Offered</p>
                    <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ items.length }}</h3>
                  </div>
                  <div class="p-3 bg-blue-500 rounded-full bg-opacity-10 w-[48px] h-[48px] flex justify-center items-center dark:bg-opacity-20">
                    <UIcon
                      name="mdi-cube"
                      class="text-blue-600 dark:text-blue-300"
                      size="lg"
                    />
                  </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-blue-500 dark:text-blue-300">
                  <UIcon name="i-heroicons-cube" class="mr-1" />
                  <span>Items available for collection</span>
                </div>
              </div>
            </UCard>
            
            <!-- Card 3: Last Updated -->
            <UCard class="relative overflow-hidden rounded-lg shadow-sm border-0">
              <div class="p-5 bg-gradient-to-br from-violet-50 to-violet-100 dark:from-violet-900/30 dark:to-violet-800/30">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-sm font-medium text-violet-600 dark:text-violet-300">Last Updated</p>
                    <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ getLastUpdated }}</h3>
                  </div>
                  <div class="p-3 bg-violet-500 rounded-full bg-opacity-10 w-[48px] h-[48px] flex justify-center items-center dark:bg-opacity-20">
                    <UIcon
                      name="mdi-calendar-clock"
                      class="text-violet-600 dark:text-violet-300"
                      size="lg"
                    />
                  </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-violet-500 dark:text-violet-300">
                  <UIcon name="i-heroicons-clock" class="mr-1" />
                  <span>{{ junkshop.updated_at ? 'Last information update' : 'No updates yet' }}</span>
                </div>
              </div>
            </UCard>
          </div>
        </div>

        <!-- Main Content Grid -->
        <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-2">
          <!-- Junkshop Information Section -->
          <div>
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200">Junkshop Information</h2>
              <UButton 
                size="sm" 
                color="emerald" 
                variant="soft" 
                icon="i-heroicons-arrow-path"
                :loading="isLoading"
                @click="refreshData"
                :tooltip="{ text: 'Refresh Data' }"
                square
              />
            </div>
            
            <UCard class="p-5">
              <UForm :state="junkshop" @submit.prevent="updateJunkshop" class="space-y-4">
                <UFormGroup label="Name" name="name" required>
                  <UInput 
                    v-model="junkshop.name" 
                    type="text" 
                    icon="i-heroicons-building-storefront" 
                    placeholder="Junkshop name" 
                    required
                    ref="nameInput"
                  />
                </UFormGroup>
                
                <UFormGroup label="Contact" name="contact" required>
                  <UInput 
                    v-model="junkshop.contact" 
                    type="tel" 
                    icon="i-heroicons-phone" 
                    placeholder="Contact number" 
                    required
                  />
                </UFormGroup>
                
                <UFormGroup label="Address" name="address" required>
                  <UInput 
                    v-model="junkshop.address" 
                    type="text" 
                    icon="i-heroicons-map-pin" 
                    placeholder="Your junkshop's location address" 
                    required
                  />
                </UFormGroup>
                
                <UFormGroup label="Description" name="description">
                  <UTextarea 
                    v-model="junkshop.description" 
                    placeholder="Brief description about your Junkshop"
                    :rows="3"
                  />
                </UFormGroup>
                
                <div class="pt-2">
                  <UButton 
                    type="submit" 
                    color="emerald" 
                    block
                    :loading="isLoading"
                    :disabled="isLoading"
                    class="transition-all duration-200"
                    icon="i-heroicons-save"
                    :label="'Submit'"
                  />
                </div>
              </UForm>
            </UCard>
          </div>
          
          <!-- Items Offered Section -->
          <div>
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200">Items Offered</h2>
              <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ items.length }} items
              </span>
            </div>
            
            <UCard class="p-5">
              <!-- Add Item Input -->
              <div class="flex items-center gap-2 mb-4">
                <UInput 
                  v-model="newItem" 
                  type="text" 
                  placeholder="Add new item" 
                  icon="i-heroicons-plus-circle"
                  class="flex-1"
                  :disabled="!junkshop.ulid"
                  @keyup.enter="addItem"
                  ref="newItemInput"
                />
                <UButton 
                  @click="addItem" 
                  color="emerald" 
                  variant="solid" 
                  :disabled="!newItem.trim() || !junkshop.ulid"
                  icon="i-heroicons-plus"
                  :tooltip="{ text: 'Add Item' }"
                  square
                />
              </div>
              
              <UDivider />
              
              <!-- Empty State when no junkshop exists -->
              <div v-if="!junkshop.ulid" class="flex flex-col items-center justify-center p-8 text-center">
                <div class="p-4 mb-4 rounded-full bg-amber-100 dark:bg-amber-900/30">
                  <UIcon name="i-heroicons-exclamation-triangle" class="text-2xl text-amber-600 dark:text-amber-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-1">Create your junkshop first</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Complete the form on the left to set up your junkshop</p>
                <!-- Replaced button with text link -->
                <span class="text-sm text-emerald-600 dark:text-emerald-400 cursor-pointer hover:underline flex items-center gap-1" @click="focusNameInput">
                  <UIcon name="i-heroicons-arrow-left" class="h-4 w-4" />
                  <span>Start by entering your junkshop name</span>
                </span>
              </div>
              
              <!-- Empty State when no items -->
              <div v-else-if="items.length === 0" class="flex flex-col items-center justify-center p-8 text-center">
                <div class="p-4 mb-4 rounded-full bg-blue-100 dark:bg-blue-900/30">
                  <UIcon name="i-heroicons-cube" class="text-2xl text-blue-600 dark:text-blue-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-1">No items yet</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Add the items your junkshop accepts</p>
                <!-- Removed standalone plus button to avoid duplication -->
                <span class="text-sm text-emerald-600 dark:text-emerald-400 cursor-pointer hover:underline" @click="focusNewItemInput">
                  Click here to add your first item
                </span>
              </div>
              
              <!-- Items List -->
              <div v-else class="max-h-[400px] overflow-y-auto custom-scrollbar pr-1">
                <TransitionGroup tag="ul" name="items-list" class="space-y-2">
                  <li 
                    v-for="item in items" 
                    :key="item.id"
                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200"
                  >
                    <div class="flex items-center justify-between p-3 gap-2">
                      <div v-if="editingItemId === item.id" class="flex-1">
                        <UInput 
                          v-model="editingItemName" 
                          type="text" 
                          placeholder="Item name"
                          class="w-full"
                          @keyup.enter="saveItem(item.id)"
                          ref="editItemInput"
                        />
                      </div>
                      <div v-else class="flex items-center gap-3 flex-1">
                        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                          <UIcon name="i-heroicons-cube" class="text-blue-600 dark:text-blue-400" />
                        </div>
                        <span class="font-medium text-gray-700 dark:text-gray-200">{{ item.name }}</span>
                      </div>
                      
                      <div class="flex space-x-2">
                        <template v-if="editingItemId === item.id">
                          <UButton 
                            @click="saveItem(item.id)" 
                            color="emerald" 
                            variant="soft"
                            icon="i-heroicons-check" 
                            size="sm"
                            square
                            :tooltip="{ text: 'Save' }"
                          />
                          <UButton 
                            @click="cancelEdit" 
                            color="gray" 
                            variant="soft" 
                            icon="i-heroicons-x-mark"
                            size="sm"
                            square
                            :tooltip="{ text: 'Cancel' }"
                          />
                        </template>
                        <template v-else>
                          <UButton 
                            @click="editItem(item)" 
                            color="blue" 
                            variant="ghost" 
                            icon="i-heroicons-pencil-square"
                            size="xs"
                            square
                            :tooltip="{ text: 'Edit' }"
                          />
                          <UButton 
                            @click="deleteItem(item.id)" 
                            color="red" 
                            variant="ghost" 
                            icon="i-heroicons-trash"
                            size="xs"
                            square
                            :tooltip="{ text: 'Delete' }"
                          />
                        </template>
                      </div>
                    </div>
                  </li>
                </TransitionGroup>
              </div>
            </UCard>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed, nextTick } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";

import { useNuxtApp } from "#app";

const router = useRouter();
const toast = useToast();
const { $storage } = useNuxtApp();

// Store instances
const auth = useAuthStore();
const user = computed(() => auth.user);

// Determine if the role is User
const isUserRole = computed(() => {
  return user.value?.roles?.includes("user");
});

if (isUserRole.value === true) {
  router.push("/account/general");
}

// Loading states
const isLoading = ref(false);

// Reactive state for Junkshop details
const junkshop = reactive({
  name: "",
  contact: "",
  description: "",
  address: "",
  ulid: "",
  owner_ulid: "",
  updated_at: null as string | null,
});

// Computed property for last updated
const getLastUpdated = computed(() => {
  if (junkshop.updated_at) {
    // Format date to show just the date (e.g. "May 15, 2023")
    return new Date(junkshop.updated_at).toLocaleDateString('en-US', { 
      month: 'short', 
      day: 'numeric',
      year: 'numeric'
    });
  }
  return 'Never';
});

// Reactive state for items
const items = ref<any[]>([]);
const newItem = ref("");
const newItemInput = ref(null);
const nameInput = ref(null);
const editItemInput = ref(null);

// State for editing items
const editingItemId = ref<number | null>(null);
const editingItemName = ref("");

// Define proper interface for the junkshop response
interface JunkshopResponse {
  ulid: string;
  name: string;
  contact: string;
  description: string;
  address: string;
  user_id: string;
  created_at?: string;
  updated_at?: string;
}

// Function to fetch all junkshop data
const fetchAllData = async () => {
  try {
    isLoading.value = true;
    console.log("Fetching junkshop data...");

    const junkshopsData = await $fetch<any[]>('/junkshop', {
      headers: {
        Authorization: `Bearer ${auth.token}`
      }
    });

    console.log("Junkshops data received:", junkshopsData);

    // Match using user_id instead of owner_ulid (field name mismatch)
    const userJunkshop = Array.isArray(junkshopsData)
      ? junkshopsData.find(shop => shop.user_id === auth.user.ulid)
      : null;

    console.log("Current user ULID:", auth.user.ulid);
    console.log("Found user junkshop:", userJunkshop);

    if (userJunkshop) {
      Object.assign(junkshop, userJunkshop);
      junkshop.owner_ulid = auth.user.ulid; // Set this for later use

      await fetchItems(userJunkshop.ulid);
    } else {
      console.log("No junkshop found for this user, initializing new one");
      // Initialize new junkshop with owner_ulid
      junkshop.owner_ulid = auth.user.ulid;
    }
  } catch (error) {
    console.error('Error fetching junkshop data:', error);
    handleApiError(error, 'Failed to load junkshop data. Please try again.');
  } finally {
    isLoading.value = false;
  }
};

// Function to fetch junkshop items
const fetchItems = async (junkshopUlid: string) => {
  try {
    console.log("Fetching items for junkshop:", junkshopUlid);

    // Make sure we're using the correct API path
    const itemsData = await $fetch<any[]>(`/junkshop/${junkshopUlid}/items`, {
      headers: {
        Authorization: `Bearer ${auth.token}`
      }
    });

    console.log("Raw items data received:", itemsData);

    // Enhanced handling of item data - ensuring each item has a name property
    items.value = Array.isArray(itemsData) ? itemsData.map(item => {
      // Check for the name property directly
      if (typeof item.name === 'string' && item.name.trim() !== '') {
        return {
          id: item.id,
          name: item.name,
          item_id: item.item_id,
          junkshop_id: item.junkshop_id
        };
      }

      // Fallback if no name is found
      console.warn('Item missing name property:', item);
      return {
        id: item.id,
        name: `Item ${item.item_id}`, // Fallback name
        item_id: item.item_id,
        junkshop_id: item.junkshop_id
      };
    }) : [];

    console.log("Processed items:", items.value);
  } catch (error) {
    console.error('Error fetching junkshop items:', error);
    handleApiError(error, 'Failed to load junkshop items.');
  }
};

// Refresh data function
const refreshData = () => {
  fetchAllData();
};

// Focus functions for better UX
const focusNewItemInput = () => {
  nextTick(() => {
    if (newItemInput.value) {
      newItemInput.value.focus();
    }
  });
};

const focusNameInput = () => {
  nextTick(() => {
    if (nameInput.value) {
      nameInput.value.focus();
    }
  });
};

// Handle API errors consistently
const handleApiError = async (error: any, defaultMessage: string) => {
  // More detailed error logging
  if (error.response) {
    console.error('Response status:', error.response.status);
    try {
      const responseData = await error.response.json();
      console.error('Response data:', responseData);
    } catch (e) {
      console.error('Could not parse error response');
    }
  }

  toast.add({
    title: 'Error',
    description: defaultMessage,
    color: 'red'
  });
};

// Fetch Junkshop details and items on component mount
onMounted(() => {
  fetchAllData();
});

// Function to update or create Junkshop details
const updateJunkshop = async () => {
  try {
    isLoading.value = true;

    // Ensure required fields
    if (!junkshop.name || !junkshop.contact || !junkshop.address) {
      toast.add({
        title: 'Error',
        description: 'Please fill all required fields',
        color: 'red'
      });
      return;
    }

    // Create new junkshop or update existing one
    if (!junkshop.ulid) {
      // Create new junkshop
      const response = await $fetch<JunkshopResponse>('/junkshop', {
        method: "POST",
        body: {
          name: junkshop.name,
          contact: junkshop.contact,
          description: junkshop.description,
          address: junkshop.address,
          owner_ulid: auth.user.ulid,
        },
        headers: {
          Authorization: `Bearer ${auth.token}`,
          'Content-Type': 'application/json'
        },
      });

      // Update local state with new ulid
      if (response && response.ulid) {
        junkshop.ulid = response.ulid;
        junkshop.updated_at = response.updated_at || new Date().toISOString();
      }

      toast.add({
        title: 'Success',
        description: 'Junkshop created successfully',
        color: 'green'
      });
      
      // Focus on the new item input after creating the junkshop
      setTimeout(() => {
        focusNewItemInput();
      }, 500);
    } else {
      // Update existing junkshop
      const response = await $fetch<JunkshopResponse>(`/junkshop/${junkshop.ulid}`, {
        method: "PUT",
        body: {
          name: junkshop.name,
          contact: junkshop.contact,
          description: junkshop.description,
          address: junkshop.address,
          owner_ulid: auth.user.ulid,
        },
        headers: {
          Authorization: `Bearer ${auth.token}`,
          'Content-Type': 'application/json'
        },
      });
      
      if (response && response.updated_at) {
        junkshop.updated_at = response.updated_at;
      }

      toast.add({
        title: 'Success',
        description: 'Junkshop updated successfully',
        color: 'green'
      });
    }
  } catch (error) {
    console.error('Error updating junkshop:', error);
    handleApiError(error, 'Failed to update junkshop. Please try again.');
  } finally {
    isLoading.value = false;
  }
};

// Function to add a new item
const addItem = async () => {
  if (newItem.value.trim() === "" || !junkshop.ulid) return;

  try {
    const addedItem = await $fetch(`/junkshop/${junkshop.ulid}/items`, {
      method: "POST",
      body: { name: newItem.value },
      headers: {
        Authorization: `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      },
    });

    items.value.push(addedItem);
    newItem.value = "";
    
    toast.add({
      title: 'Success',
      description: 'Item added successfully',
      color: 'green'
    });
    
    // Re-focus the input for quick addition of multiple items
    focusNewItemInput();
  } catch (error) {
    console.error('Error adding item:', error);
    handleApiError(error, 'Failed to add item. Please try again.');
  }
};

// Function to delete an item
const deleteItem = async (itemId: number) => {
  if (!junkshop.ulid) return;

  try {
    await $fetch(`/junkshop/${junkshop.ulid}/items/${itemId}`, {
      method: "DELETE",
      headers: {
        Authorization: `Bearer ${auth.token}`
      }
    });

    items.value = items.value.filter((item) => item.id !== itemId);
    
    toast.add({
      title: 'Success',
      description: 'Item deleted successfully',
      color: 'green'
    });
  } catch (error) {
    console.error('Error deleting item:', error);
    handleApiError(error, 'Failed to delete item. Please try again.');
  }
};

// Function to edit an item
const editItem = (item: any) => {
  editingItemId.value = item.id;
  editingItemName.value = item.name;
  
  // Focus the edit input after rendering
  nextTick(() => {
    if (editItemInput.value) {
      editItemInput.value.focus();
    }
  });
};

// Function to save an edited item
const saveItem = async (itemId: number) => {
  if (!junkshop.ulid) return;

  try {
    const updatedItem = await $fetch(`/junkshop/${junkshop.ulid}/items/${itemId}`, {
      method: "PUT",
      body: { name: editingItemName.value },
      headers: {
        Authorization: `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      },
    });

    const index = items.value.findIndex((item) => item.id === itemId);
    if (index !== -1) {
      items.value[index] = updatedItem;
    }

    editingItemId.value = null;
    editingItemName.value = "";
    
    toast.add({
      title: 'Success',
      description: 'Item updated successfully',
      color: 'green'
    });
  } catch (error) {
    console.error('Error updating item:', error);
    handleApiError(error, 'Failed to update item. Please try again.');
  }
};

// Function to cancel editing an item
const cancelEdit = () => {
  editingItemId.value = null;
  editingItemName.value = "";
};
</script>

<style scoped>
/* Add subtle animations to cards for a more interactive feel */
.u-card {
  transition: all 0.3s ease;
}

.u-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Transition animations for the item list */
.items-list-enter-active,
.items-list-leave-active {
  transition: all 0.3s ease;
}
.items-list-enter-from,
.items-list-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

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
