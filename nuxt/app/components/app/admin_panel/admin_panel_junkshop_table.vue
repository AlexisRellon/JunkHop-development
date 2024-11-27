<template>
  <!-- Junkshop Table Card -->
  <UCard
    class="col-span-1 p-8 bg-white rounded-lg shadow-lg md:col-span-2 lg:col-span-3 dark:bg-gray-800"
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
      :rows="junkshops"
      :columns="junkshopColumns"
      size="lg"
      :loading="loadingJunkshops"
    >
      <template #actions-data="{ row }">
        <div class="flex justify-end gap-3">
          <UButton
            @click="editJunkshop(row)"
            color="teal"
            variant="solid"
            class="px-3 py-1 rounded-md"
          >Edit</UButton>
          <UButton
            @click="deleteJunkshop(row.ulid)"
            color="red"
            variant="solid"
            class="px-3 py-1 rounded-md"
          >Delete</UButton>
        </div>
      </template>
    </UTable>
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
            <div class="flex flex-col gap-2">
              <label
                for="name"
                class="relative text-sm font-medium text-gray-700 dark:text-gray-300"
              >Name</label>
              <UInput
                v-model="editingJunkshop.name"
                size="lg"
                type="text"
                id="name"
                ref="nameInput"
                class="relative w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                placeholder="Junkshop name"
                autofocus
                required
              >
                <template #leading>
                  <UIcon name="mdi-factory" class="text-gray-400"></UIcon>
                </template>
              </UInput>
            </div>
            <div class="flex flex-col gap-2">
              <label
                for="address"
                class="relative text-sm font-medium text-gray-700 dark:text-gray-300"
              >Address</label>
              <UInput
                v-model="editingJunkshop.address"
                size="lg"
                type="text"
                id="address"
                class="relative w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                placeholder="Junkshop address"
                required
              >
                <template #leading>
                  <UIcon name="mdi-map-marker" class="text-gray-400"></UIcon>
                </template>
              </UInput>
            </div>
            <div class="flex flex-col gap-2">
              <label
                for="contact"
                class="relative text-sm font-medium text-gray-700 dark:text-gray-300"
              >Contact</label>
              <UInput
                v-model="editingJunkshop.contact"
                size="lg"
                type="text"
                id="contact"
                class="relative w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                placeholder="Junkshop contact"
                required
              >
                <template #leading>
                  <UIcon name="mdi-phone" class="text-gray-400"></UIcon>
                </template>
              </UInput>
            </div>
            <div class="flex flex-col gap-2">
              <label
                for="owner"
                class="relative text-sm font-medium text-gray-700 dark:text-gray-300"
              >Owner</label>
              <UInputMenu
                v-model="editingJunkshop.owner"
                :options="users.map(user => ({ value: user.ulid, label: user.name }))"
                placeholder="Select Owner"
                class="relative w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                size="lg"
                required
              >
                <template #leading>
                  <UIcon name="mdi-account" class="text-gray-400"></UIcon>
                </template>
              </UInputMenu>
            </div>
            <UButton
              type="submit"
              color="teal"
              variant="solid"
              class="flex justify-center w-full py-2 rounded-md"
            >{{ isEditingJunkshop ? "Update" : "Add" }}</UButton>
            <UButton
              @click="cancelEditJunkshop"
              color="gray"
              variant="outline"
              class="flex justify-center w-full py-2 rounded-md"
            >Cancel</UButton>
          </UForm>
        </div>
      </UCard>
    </UContainer>
  </USlideover>
</template>

<script setup>
import { ref, reactive, nextTick, onMounted } from "vue";

const junkshops = ref([]);
const loadingJunkshops = ref(false);
const users = ref([]); // Add users list
const editingJunkshop = reactive({
  ulid: "",
  name: "",
  address: "",
  contact: "",
  owner: "", // Add owner field
});
const drawerOpenJunkshop = ref(false);
const isEditingJunkshop = ref(false);

const junkshopColumns = [
  { key: "name", label: "Name" },
  { key: "address", label: "Address" },
  { key: "contact", label: "Contact" },
  { key: "owner", label: "Owner" }, // Add owner column
  { key: "actions", label: "Actions" },
];

const openAddJunkshopDrawer = () => {
  Object.assign(editingJunkshop, { ulid: "", name: "", address: "", contact: "", owner: "" });
  isEditingJunkshop.value = false;
  drawerOpenJunkshop.value = true;
};

const editJunkshop = async (junkshop) => {
  editingJunkshop.ulid = junkshop.ulid;
  editingJunkshop.name = junkshop.name;
  editingJunkshop.address = junkshop.address;
  editingJunkshop.contact = junkshop.contact;
  editingJunkshop.owner = junkshop.owner;
  drawerOpenJunkshop.value = true;
  await nextTick();
  isEditingJunkshop.value = true;
};

const cancelEditJunkshop = () => {
  Object.assign(editingJunkshop, { ulid: "", name: "", address: "", contact: "", owner: "" });
  drawerOpenJunkshop.value = false;
};

const validateJunkshop = (state) => {
  const errors = [];
  if (!state.name) errors.push({ path: "name", message: "Required" });
  if (!state.address) errors.push({ path: "address", message: "Required" });
  if (!state.contact) errors.push({ path: "contact", message: "Required" });
  if (!state.owner) errors.push({ path: "owner", message: "Required" });
  return errors;
};

const onSubmitJunkshop = async () => {
  console.log(isEditingJunkshop.value ? "Editing Junkshop:" : "Adding Junkshop:", editingJunkshop); // Debugging line
  if (isEditingJunkshop.value) {
    if (!editingJunkshop.ulid) {
      console.error("Junkshop ULID is undefined");
      return;
    }
    try {
      await $fetch(`/junkshop/${editingJunkshop.ulid}`, {
        method: "PUT",
        body: JSON.stringify({
          name: editingJunkshop.name,
          address: editingJunkshop.address,
          contact: editingJunkshop.contact,
          owner_ulid: editingJunkshop.owner,
        }),
        headers: {
          "Content-Type": "application/json",
        },
      });
      // Update the junkshop in the local list
      const index = junkshops.value.findIndex((j) => j.ulid === editingJunkshop.ulid);
      if (index !== -1) {
        junkshops.value[index] = { ...editingJunkshop };
      }
      cancelEditJunkshop();
    } catch (error) {
      if (error.response && error.response.status === 422) {
        console.error("Validation error:", error.response.data.errors);
        alert("Validation error: " + JSON.stringify(error.response.data.errors));
      } else {
        console.error("Error updating junkshop:", error);
        console.error(
          "Error details:",
          error.response ? error.response.data : error.message
        );
      }
    }
  } else {
    try {
      const newJunkshop = await $fetch(`/junkshop`, {
        method: "POST",
        body: JSON.stringify({
          name: editingJunkshop.name,
          address: editingJunkshop.address,
          contact: editingJunkshop.contact,
          owner_ulid: editingJunkshop.owner,
        }),
        headers: {
          "Content-Type": "application/json",
        },
      });
      junkshops.value.push(newJunkshop);
      cancelEditJunkshop();
    } catch (error) {
      if (error.response && error.response.status === 422) {
        console.error("Validation error:", error.response.data.errors);
        alert("Validation error: " + JSON.stringify(error.response.data.errors));
      } else {
        console.error("Error adding junkshop:", error);
        console.error(
          "Error details:",
          error.response ? error.response.data : error.message
        );
      }
    }
  }
};

const onErrorJunkshop = (event) => {
  const element = document.getElementById(event.errors[0].id);
  element?.focus();
  element?.scrollIntoView({ behavior: "smooth", block: "center" });
};

const deleteJunkshop = async (junkshopUlid) => {
  await $fetch(`/junkshop/${junkshopUlid}`, {
    method: "DELETE",
  });
  junkshops.value = junkshops.value.filter((junkshop) => junkshop.ulid !== junkshopUlid);
};

const fetchJunkshops = async () => {
  try {
    loadingJunkshops.value = true;
    const data = await $fetch("/junkshop", {
      method: "GET",
    });
    junkshops.value = data;
    console.log("Junkshops:", data);
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
