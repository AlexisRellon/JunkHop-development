<template>
  <UCard :ui="{ body: { base: 'grid grid-cols-12 gap-6 md:gap-8' } }">
    <div class="col-span-12 lg:col-span-4">
      <div class="mb-2 text-lg font-semibold">Junkshop Information</div>
      <div class="text-sm opacity-80">
        Update your junkshop's profile information and details.
      </div>
    </div>
    <div class="col-span-12 lg:col-span-8">
      <UForm :state="junkshop" @submit.prevent="updateJunkshop" class="space-y-6">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
          <UInput v-model="junkshop.name" size="lg" type="text" id="name"
            class="block w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
            placeholder="Junkshop name" required />
        </div>
        <div>
          <label for="contact" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact</label>
          <div class="flex mt-1">
            <UInput v-model="junkshop.contact" size="lg" type="tel" id="contact"
              class="block w-full text-gray-900 bg-white border-gray-300 shadow-sm rounded-r-md dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
              placeholder="Basic Contact information"
              pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" required />
          </div>
        </div>
        <div>
          <label for="description"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
          <UInput v-model="junkshop.description" size="lg" type="textarea" id="description"
            class="block w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" placeholder="Brief description about your Junkshop" required />
        </div>
        <div>
          <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
          <UInput v-model="junkshop.address" size="lg" type="text" id="address"
            class="block w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" placeholder="Your address of your Junkshop so the people will know where you are located" required />
        </div>
        <UButton type="submit" color="teal" variant="solid" class="flex justify-center w-full py-2 rounded-md">Update
        </UButton>
      </UForm>
    </div>
    <UDivider class="col-span-12" />
    <div class="col-span-12 lg:col-span-4">
      <div class="mb-2 text-lg font-semibold">Items Offered</div>
      <div class="text-sm opacity-80">
        Manage the items your junkshop offers.
      </div>
    </div>
    <div class="col-span-12 lg:col-span-8" v-auto-animate>
      <div class="flex items-center mb-6 space-x-4">
        <UInput v-model="newItem" size="lg" type="text" placeholder="New item"
          class="flex-1 block w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
        <UButton @click="addItem" color="teal" variant="solid" class="py-2 rounded-md">Add</UButton>
      </div>
      <ul class="space-y-4" v-auto-animate>
        <li v-for="item in items" :key="item.id"
          class="flex items-center justify-between p-4 border border-gray-300 rounded-md bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
          <div v-if="editingItemId === item.id">
            <UInput v-model="editingItemName" type="text"
              class="block w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
          </div>
          <div v-else>
            <span class="text-gray-900 dark:text-gray-200">{{ item.name }}</span>
          </div>
          <div class="flex space-x-2">
            <UButton v-if="editingItemId === item.id" @click="saveItem(item.id)" color="teal" variant="solid"
              class="px-3 py-1 rounded-md">Save</UButton>
            <UButton v-if="editingItemId === item.id" @click="cancelEdit" color="teal" variant="outline"
              class="px-3 py-1 rounded-md">Cancel</UButton>
            <UButton v-else @click="editItem(item)" color="teal" variant="solid" class="px-3 py-1 rounded-md">Edit
            </UButton>
            <UButton @click="deleteItem(item.id)" color="red" variant="solid" class="px-3 py-1 rounded-md">Delete
            </UButton>
          </div>
        </li>
      </ul>
    </div>
  </UCard>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";

const router = useRouter();
const toast = useToast();

// Store instances
const auth = useAuthStore();

// Store instances
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
});

// Reactive state for items
const items = ref<any[]>([]);
const newItem = ref("");

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

// Fetch Junkshop details and items on component mount
onMounted(async () => {
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

      console.log("Fetching items for junkshop:", userJunkshop.ulid);

      try {
        // Make sure we're using the correct API path
        const itemsData = await $fetch<any[]>(`/junkshop/${userJunkshop.ulid}/items`, {
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
      } catch (itemError) {
        console.error('Error fetching junkshop items:', itemError);
        // More detailed error logging
        if ((itemError as any).response) {
          console.error('Response status:', (itemError as any).response.status);
          console.error('Response data:', await (itemError as any).response.json().catch(() => ({})));
        }
      }
    } else {
      console.log("No junkshop found for this user, initializing new one");
      // Initialize new junkshop with owner_ulid
      junkshop.owner_ulid = auth.user.ulid;
    }
  } catch (error) {
    console.error('Error fetching junkshop data:', error);
    // More detailed error logging
    if ((error as any).response) {
      console.error('Response status:', (error as any).response.status);
      console.error('Response data:', await (error as any).response.json().catch(() => ({})));
    }

    toast.add({
      title: 'Error',
      description: 'Failed to load junkshop data. Please try again.',
      color: 'red'
    });
  } finally {
    isLoading.value = false;
  }
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
      }

      toast.add({
        title: 'Success',
        description: 'Junkshop created successfully',
        color: 'green'
      });
    } else {
      // Update existing junkshop
      await $fetch(`/junkshop/${junkshop.ulid}`, {
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

      toast.add({
        title: 'Success',
        description: 'Junkshop updated successfully',
        color: 'green'
      });
    }
  } catch (error) {
    console.error('Error updating junkshop:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to update junkshop. Please try again.',
      color: 'red'
    });
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
  } catch (error) {
    console.error('Error adding item:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to add item. Please try again.',
      color: 'red'
    });
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
  } catch (error) {
    console.error('Error deleting item:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to delete item. Please try again.',
      color: 'red'
    });
  }
};

// Function to edit an item
const editItem = (item: any) => {
  editingItemId.value = item.id;
  editingItemName.value = item.name;
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
  } catch (error) {
    console.error('Error updating item:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to update item. Please try again.',
      color: 'red'
    });
  }
};

// Function to cancel editing an item
const cancelEdit = () => {
  editingItemId.value = null;
  editingItemName.value = "";
};

</script>

<style scoped>
.container {
  max-width: 800px;
}
</style>
