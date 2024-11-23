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
            placeholder="Junkshop name" />
        </div>
        <div>
          <label for="contact" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact</label>
          <div class="flex mt-1">
            <UInput v-model="junkshop.contact" size="lg" type="tel" id="contact"
              class="block w-full text-gray-900 bg-white border-gray-300 shadow-sm rounded-r-md dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
              placeholder="Basic Contact information"
              pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" />
          </div>
        </div>
        <div>
          <label for="description"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
          <UInput v-model="junkshop.description" size="lg" type="textarea" id="description"
            class="block w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" placeholder="Brief description about your Junkshop" />
        </div>
        <div>
          <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
          <UInput v-model="junkshop.address" size="lg" type="text" id="address"
            class="block w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" placeholder="Your address of your Junkshop so the people will know where you are located" />
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
    <div class="col-span-12 lg:col-span-8">
      <div class="flex items-center mb-6 space-x-4">
        <UInput v-model="newItem" size="lg" type="text" placeholder="New item"
          class="flex-1 block w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
        <UButton @click="addItem" color="teal" variant="solid" class="py-2 rounded-md">Add</UButton>
      </div>
      <ul class="space-y-4">
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

// Store instances
const auth = useAuthStore();

// Reactive state for Junkshop details
const junkshop = reactive({
  name: "",
  contact: "",
  description: "",
  address: "",
});

// Reactive state for items
const items = ref<any[]>([]);
const newItem = ref("");

// Reactive state for selected country code
const selectedCountryCode = ref("");

// List of country codes with flags
const countryCodes = ref([
  { value: "+1", label: "+1", flag: "🇺🇸" },
  { value: "+44", label: "+44", flag: "🇬🇧" },
  { value: "+61", label: "+61", flag: "🇦🇺" },
  { value: "+81", label: "+81", flag: "🇯🇵" },
  { value: "+86", label: "+86", flag: "🇨🇳" },
  { value: "+91", label: "+91", flag: "🇮🇳" },
  { value: "+49", label: "+49", flag: "🇩🇪" },
  { value: "+33", label: "+33", flag: "🇫🇷" },
  { value: "+39", label: "+39", flag: "🇮🇹" },
  { value: "+7", label: "+7", flag: "🇷🇺" },
  { value: "+34", label: "+34", flag: "🇪🇸" },
  { value: "+55", label: "+55", flag: "🇧🇷" },
  { value: "+27", label: "+27", flag: "🇿🇦" },
  { value: "+52", label: "+52", flag: "🇲🇽" },
  { value: "+63", label: "+63", flag: "🇵🇭" },
]);

// Computed property to format country code options with flags
const formattedCountryCodes = computed(() => {
  return countryCodes.value.map(code => ({
    value: code.value,
    label: `${code.flag} ${code.label}`
  }));
});

// State for editing items
const editingItemId = ref<number | null>(null);
const editingItemName = ref("");

// Fetch Junkshop details and items on component mount
onMounted(async () => {
  const junkshopData = await $fetch(`/api/junkshop/${auth.user.ulid}`);
  Object.assign(junkshop, junkshopData);

  const itemsData = await $fetch(`/api/junkshop/${auth.user.ulid}/items`);
  items.value = Array.isArray(itemsData) ? itemsData : [];
});

// Function to update Junkshop details
const updateJunkshop = async () => {
  await $fetch(`/api/junkshop/${auth.user.ulid}`, {
    method: "PUT",
    body: junkshop,
  });
};

// Function to add a new item
const addItem = async () => {
  if (newItem.value.trim() === "") return;

  const addedItem = await $fetch(`/api/junkshop/${auth.user.ulid}/items`, {
    method: "POST",
    body: { name: newItem.value },
  });

  items.value.push(addedItem);
  newItem.value = "";
};

// Function to delete an item
const deleteItem = async (itemId: number) => {
  await $fetch(`/api/junkshop/${auth.user.ulid}/items/${itemId}`, {
    method: "DELETE",
  });

  items.value = items.value.filter((item) => item.id !== itemId);
};

// Function to edit an item
const editItem = (item: any) => {
  editingItemId.value = item.id;
  editingItemName.value = item.name;
};

// Function to save an edited item
const saveItem = async (itemId: number) => {
  const updatedItem = await $fetch(`/api/junkshop/${auth.user.ulid}/items/${itemId}`, {
    method: "PUT",
    body: { name: editingItemName.value },
  });

  const index = items.value.findIndex((item) => item.id === itemId);
  if (index !== -1) {
    items.value[index] = updatedItem;
  }

  editingItemId.value = null;
  editingItemName.value = "";
};

// Function to cancel editing an item
const cancelEdit = () => {
  editingItemId.value = null;
  editingItemName.value = "";
};

// Placeholder Items
items.value = [
  { id: 1, name: "Scrap Metal" },
  { id: 2, name: "Used Tires" },
  { id: 3, name: "Old Electronics" },
];

</script>

<style scoped>
.container {
  max-width: 800px;
}
</style>
