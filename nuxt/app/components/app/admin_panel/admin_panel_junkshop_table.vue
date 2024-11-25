<template>
  <!-- Junkshop Table Card -->
  <UCard
    class="col-span-1 p-8 bg-white rounded-lg shadow-lg md:col-span-2 lg:col-span-3 dark:bg-gray-800"
  >
    <h2 class="mb-4 text-2xl font-bold dark:text-gray-100">Junkshops</h2>
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
            >Edit</UButton
          >
          <UButton
            @click="deleteJunkshop(row.ulid)"
            color="red"
            variant="solid"
            class="px-3 py-1 rounded-md"
            >Delete</UButton
          >
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
            <div>
              <label
                for="name"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >Name</label
              >
              <UInput
                v-model="editingJunkshop.name"
                size="lg"
                type="text"
                id="name"
                ref="nameInput"
                class="block w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                placeholder="Junkshop name"
                autofocus
                required
              />
            </div>
            <div>
              <label
                for="address"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >Address</label
              >
              <UInput
                v-model="editingJunkshop.address"
                size="lg"
                type="text"
                id="address"
                class="block w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                placeholder="Junkshop address"
                required
              />
            </div>
            <div>
              <label
                for="contact"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >Contact</label
              >
              <UInput
                v-model="editingJunkshop.contact"
                size="lg"
                type="text"
                id="contact"
                class="block w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                placeholder="Junkshop contact"
                required
              />
            </div>
            <UButton
              type="submit"
              color="teal"
              variant="solid"
              class="flex justify-center w-full py-2 rounded-md"
              >Update</UButton
            >
            <UButton
              @click="cancelEditJunkshop"
              color="gray"
              variant="outline"
              class="flex justify-center w-full py-2 rounded-md"
              >Cancel</UButton
            >
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
const editingJunkshop = reactive({
  ulid: "",
  name: "",
  address: "",
  contact: "",
});
const drawerOpenJunkshop = ref(false);

const junkshopColumns = [
  { key: "name", label: "Name" },
  { key: "address", label: "Address" },
  { key: "contact", label: "Contact" },
  { key: "actions", label: "Actions" },
];

const editJunkshop = async (junkshop) => {
  editingJunkshop.ulid = junkshop.ulid;
  editingJunkshop.name = junkshop.name;
  editingJunkshop.address = junkshop.address;
  editingJunkshop.contact = junkshop.contact;
  drawerOpenJunkshop.value = true;
  await nextTick();
};

const cancelEditJunkshop = () => {
  Object.assign(editingJunkshop, { ulid: "", name: "", address: "", contact: "" });
  drawerOpenJunkshop.value = false;
};

const validateJunkshop = (state) => {
  const errors = [];
  if (!state.name) errors.push({ path: "name", message: "Required" });
  if (!state.address) errors.push({ path: "address", message: "Required" });
  if (!state.contact) errors.push({ path: "contact", message: "Required" });
  return errors;
};

const onSubmitJunkshop = async () => {
  console.log("Editing Junkshop:", editingJunkshop); // Debugging line
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

onMounted(async () => {
  try {
    await fetchJunkshops();
  } catch (error) {
    console.error("Error during mounted hook:", error);
  }
});
</script>

<style></style>
