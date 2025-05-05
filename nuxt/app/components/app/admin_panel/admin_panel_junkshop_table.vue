<template>
  <!-- Junkshop Table Card -->
  <UCard
    class="col-span-1 p-8 bg-white rounded-lg shadow-lg md:col-span-2 lg:col-span-3 dark:bg-gray-900/30 border-0"
  >
    <span class="flex justify-between items-center">
      <h2 class="mb-4 text-2xl font-bold dark:text-gray-100">Junkshops</h2>
      <UButton
        @click="openAddJunkshopDrawer"
        color="teal"
        variant="solid"
        class="mb-4"
      >Add Junkshop</UButton>
    </span>
    <UTable
      :rows="paginatedJunkshops"
      :columns="junkshopColumns"
      size="lg"
      :loading="loadingJunkshops"
    >
      <template #actions-data="{ row }">
        <div class="flex justify-end gap-3">
          <UButton
            @click="editJunkshop(row)"
            color="teal"
            variant="soft"
            class="px-3 py-1 rounded-md"
          >Edit</UButton>
          <UButton
            @click="deleteJunkshop(row.ulid)"
            color="red"
            variant="soft"
            class="px-3 py-1 rounded-md"
          >Delete</UButton>
        </div>
      </template>
      <template #user_id-data="{ row }">
        <span>{{ getUserName(row.user_id) }}</span>
      </template>
    </UTable>
    
    <!-- Pagination -->
    <div class="flex justify-between items-center mt-6">
      <p class="text-sm text-gray-500 dark:text-gray-400">
        Showing <span class="font-medium">{{ startIndex + 1 }}</span> to <span class="font-medium">{{ endIndex }}</span> of <span class="font-medium">{{ junkshops.length }}</span> junkshops
      </p>
      <UPagination
        v-if="pageCount > 1"
        v-model="page"
        :page-count="pageCount"
        :total="junkshops.length"
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
  <USlideover v-model="drawerOpenJunkshop" side="bottom" :ui="{ height: 'h-fit' }">
    <UContainer class="flex flex-col flex-1 gap-3 py-4 sm:py-6">
      <h2 class="font-bold">Edit Junkshop Details</h2>
      <UCard :ui="{ body: { base: 'grid grid-cols-12 gap-6 md:gap-8' } }">
        <div class="col-span-12 lg:col-span-4">
          <div class="mb-2 text-lg font-semibold">Junkshop Information</div>
          <div class="text-sm opacity-80">
            Update the junkshop's profile information and details.
          </div>
        </div>
        <div class="col-span-12 lg:col-span-8">
          <UForm
            :validate="validateJunkshop"
            :state="editingJunkshop"
            @submit="onSubmitJunkshop"
            @error="onErrorJunkshop"
            class="space-y-6"
          >
            <UFormGroup label="Name" name="name" required>
              <UInput
                v-model="editingJunkshop.name"
                size="lg"
                type="text"
                id="name"
                ref="nameInput"
                class="relative w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                placeholder="Junkshop name"
                autofocus
              >
                <template #leading>
                  <UIcon name="mdi-factory" class="text-gray-400"></UIcon>
                </template>
              </UInput>
            </UFormGroup>

            <UFormGroup label="Address" name="address" required>
              <UInput
                v-model="editingJunkshop.address"
                size="lg"
                type="text"
                id="address"
                class="relative w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                placeholder="Junkshop address"
              >
                <template #leading>
                  <UIcon name="mdi-map-marker" class="text-gray-400"></UIcon>
                </template>
              </UInput>
            </UFormGroup>

            <UFormGroup label="Contact" name="contact" required>
              <UInput
                v-model="editingJunkshop.contact"
                size="lg"
                type="text"
                id="contact"
                class="relative w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                placeholder="Junkshop contact"
              >
                <template #leading>
                  <UIcon name="mdi-phone" class="text-gray-400"></UIcon>
                </template>
              </UInput>
            </UFormGroup>

            <UFormGroup label="Owner" name="owner" required>
              <UInputMenu
                v-model="editingJunkshop.owner.ulid"
                :options="users.map(user => ({ value: user.ulid, label: user.name }))"
                placeholder="Select Owner"
                class="relative w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                size="lg">
                <template #leading>
                  <UIcon name="mdi-account" class="text-gray-400"></UIcon>
                </template>
              </UInputMenu>
            </UFormGroup>

            <UButton
              type="submit"
              color="teal"
              variant="solid"
              class="flex justify-center w-full py-2 rounded-md"
            >{{ isEditingJunkshop ? "Update" : "Add" }}</UButton>
            <UButton
              @click="cancelEditJunkshop"
              color="gray"
              variant="ghost"
              class="flex justify-center w-full py-2 rounded-md"
            >Cancel</UButton>
          </UForm>
        </div>
      </UCard>
    </UContainer>
  </USlideover>
</template>

<script setup>
import { ref, reactive, nextTick, onMounted, computed, watch } from "vue";
import { useRouter } from 'vue-router';

const junkshops = ref([]);
const loadingJunkshops = ref(false);
const users = ref([]); // Add users list
const editingJunkshop = reactive({
  ulid: "",
  name: "",
  address: "",
  contact: "",
  description: "", // Add description field
  owner: { ulid: "", name: "" }, // Change owner to an object with ulid and name
});
const drawerOpenJunkshop = ref(false);
const isEditingJunkshop = ref(false);

const junkshopColumns = [
  { key: "name", label: "Name" },
  { key: "address", label: "Address" },
  { key: "contact", label: "Contact" },
  { key: "user_id", label: "Owner" }, // Use user_id for owner column
  { key: "actions", label: "Actions" },
];

// Pagination
const page = ref(1);
const itemsPerPage = ref(5);

// Computed for pagination
const pageCount = itemsPerPage.value;

const startIndex = computed(() => (page.value - 1) * itemsPerPage.value);
const endIndex = computed(() => Math.min(startIndex.value + itemsPerPage.value, junkshops.value.length));
const paginatedJunkshops = computed(() => {
  return junkshops.value.slice(startIndex.value, endIndex.value);
});

const router = useRouter();
const toast = useToast();

const getUserName = (ulid) => {
  const user = users.value.find(user => user.ulid === ulid);
  return user ? user.name : 'Unknown';
};

const openAddJunkshopDrawer = () => {
  Object.assign(editingJunkshop, { ulid: "", name: "", address: "", contact: "", owner: { ulid: "", name: "" } });
  isEditingJunkshop.value = false;
  drawerOpenJunkshop.value = true;
};

const editJunkshop = async (junkshop) => {
  isEditingJunkshop.value = true;
  drawerOpenJunkshop.value = true;

  await nextTick();

  const ownerName = getUserName(junkshop.user_id);

  // Create a new object with the junkshop data
  const junkshopData = {
    ulid: junkshop.ulid,
    name: junkshop.name,
    address: junkshop.address,
    contact: junkshop.contact,
    owner: {
      ulid: junkshop.user_id,
      name: ownerName
    }
  };

  // Set the values after the drawer is opened
  Object.keys(junkshopData).forEach(key => {
    editingJunkshop[key] = junkshopData[key];
  });
};

const cancelEditJunkshop = () => {
  Object.assign(editingJunkshop, { ulid: "", name: "", address: "", contact: "", owner: { ulid: "", name: "" } });
  drawerOpenJunkshop.value = false;
};

const validateJunkshop = (state) => {
  const errors = [];
  if (!state.name?.trim()) errors.push({ path: "name", message: "Name is required" });
  if (!state.address?.trim()) errors.push({ path: "address", message: "Address is required" });
  if (!state.contact?.trim()) errors.push({ path: "contact", message: "Contact is required" });
  if (!state.owner?.ulid) errors.push({ path: "owner", message: "Owner is required" });
  return errors;
};

const validateJunkshopData = (data) => {
  const errors = [];
  if (!data.name?.trim()) errors.push("Name is required");
  if (!data.address?.trim()) errors.push("Address is required");
  if (!data.contact?.trim()) errors.push("Contact is required");
  if (!data.owner_ulid) errors.push("Owner is required");
  if (data.contact?.length > 20) errors.push("Contact must not exceed 20 characters");
  if (data.description?.length > 1000) errors.push("Description must not exceed 1000 characters");
  return errors;
};

const onSubmitJunkshop = async () => {
  try {
    const ownerUlid = editingJunkshop.owner.ulid?.value || editingJunkshop.owner.ulid;

    const junkshopData = {
      name: editingJunkshop.name.trim(),
      address: editingJunkshop.address.trim(),
      contact: editingJunkshop.contact.trim(),
      owner_ulid: ownerUlid, // Changed from user_id to owner_ulid to match server expectation
      description: editingJunkshop.description?.trim() || "", // Handle description
    };

    // Add debug logging
    console.log('Form Data:', editingJunkshop);
    console.log('Owner ULID:', ownerUlid);
    console.log('Prepared Data:', junkshopData);

    const errors = validateJunkshopData(junkshopData);
    if (errors.length > 0) {
      throw new Error(errors.join(', '));
    }

    const requestConfig = {
      method: isEditingJunkshop.value ? 'PUT' : 'POST',
      body: junkshopData, // $fetch will handle JSON stringification
      headers: {
        'Accept': 'application/json'
      }
    };

    if (isEditingJunkshop.value) {
      junkshopData.ulid = editingJunkshop.ulid;
      await $fetch(`/junkshop/${editingJunkshop.ulid}`, requestConfig);
    } else {
      const response = await $fetch('/junkshop', requestConfig);
      console.log('Server Response:', response);
    }

    await fetchJunkshops();
    toast.add({
      icon: 'i-heroicons-check-circle-20-solid',
      title: `Junkshop ${isEditingJunkshop.value ? 'updated' : 'added'} successfully`,
      color: 'emerald'
    });
    cancelEditJunkshop();

  } catch (error) {
    console.error('Error Object:', error);
    console.error('Response Data:', error.response?._data);

    let message = 'An error occurred';
    if (error.response?.status === 422) {
      const serverErrors = error.response._data?.errors || {};
      message = Object.values(serverErrors).flat().join(', ');
    } else if (error.message) {
      message = error.message;
    }

    toast.add({
      icon: 'i-heroicons-x-circle',
      title: 'Error',
      description: message,
      color: 'red'
    });
  }
};

const onErrorJunkshop = (event) => {
  const element = document.getElementById(event.errors[0].id);
  element?.focus();
  element?.scrollIntoView({ behavior: "smooth", block: "center" });
};

const deleteJunkshop = async (junkshopUlid) => {
  try {
    await $fetch(`/junkshop/${junkshopUlid}`, {
      method: "DELETE",
    });
    junkshops.value = junkshops.value.filter((junkshop) => junkshop.ulid !== junkshopUlid);
    toast.add({
      icon: "i-heroicons-check-circle-20-solid",
      title: "Junkshop has been deleted successfully.",
      color: "emerald",
    });
  } catch (error) {
    handleError(error, "deleting junkshop");
  }
};

const fetchJunkshops = async () => {
  try {
    loadingJunkshops.value = true;
    const data = await $fetch("/junkshop", {
      method: "GET",
    });
    junkshops.value = data;
    console.log("Junkshops:", data);
    
    // Ensure page is within valid range after data loads
    const maxPage = Math.max(1, Math.ceil(junkshops.value.length / itemsPerPage.value));
    if (page.value > maxPage) {
      page.value = maxPage;
    }
  } catch (error) {
    console.error("Error fetching junkshops:", error);
    console.error("Error details:", error.response ? error.response.data : error.message);
  } finally {
    loadingJunkshops.value = false;
  }
};

const fetchUsers = async () => {
  try {
    const data = await $fetch("/users", {
      method: "GET",
    });
    users.value = data;
    console.log("Users:", data);
  } catch (error) {
    console.error("Error fetching users:", error);
    console.error(
      "Error details:",
      error.response ? error.response.data : error.message
    );
  }
};

const handleError = (error, action) => {
  if (error.response && error.response.status === 422) {
    const errors = error.response.data?.errors || {};
    console.error(`Validation error while ${action}:`, errors);
    alert(`Validation error: ${JSON.stringify(errors)}`);
  } else {
    console.error(`Error ${action}:`, error);
    console.error(
      "Error details:",
      error.response ? error.response.data : error.message
    );
  }
};

onMounted(async () => {
  try {
    await fetchUsers();
    await fetchJunkshops();
  } catch (error) {
    console.error("Error during mounted hook:", error);
  }
});
</script>

<style></style>
