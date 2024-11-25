<template>
  <!-- Users Table Card -->
  <UCard
    class="col-span-1 p-8 bg-white rounded-lg shadow-lg md:col-span-2 lg:col-span-3 dark:bg-gray-800"
  >
    <h2 class="mb-4 text-2xl font-bold dark:text-gray-100">Users</h2>
    <UTable :rows="users" :columns="userColumns" size="lg" :loading="loading">
      <template #actions-data="{ row }">
        <div class="flex justify-end gap-3">
          <UButton
            @click="editUser(row)"
            color="teal"
            variant="solid"
            class="px-3 py-1 rounded-md"
            >Edit</UButton
          >
          <UButton
            @click="deleteUser(row.ulid)"
            color="red"
            variant="solid"
            class="px-3 py-1 rounded-md"
            >Delete</UButton
          >
        </div>
      </template>
    </UTable>
  </UCard>
  <!-- User Edit Slideover -->
  <USlideover v-model="drawerOpen" side="bottom" :ui="{ height: 'h-fit' }">
    <UContainer class="flex flex-col flex-1 gap-3 py-4 sm:py-6">
      <h2 class="font-bold">Edit User Details</h2>
      <UCard :ui="{ body: { base: 'grid grid-cols-12 gap-6 md:gap-8' } }">
        <div class="col-span-12 lg:col-span-4">
          <div class="mb-2 text-lg font-semibold">User Information</div>
          <div class="text-sm opacity-80">
            Update the user's profile information and details.
          </div>
        </div>
        <div class="col-span-12 lg:col-span-8">
          <UForm
            :validate="validate"
            :state="editingUser"
            @submit="onSubmit"
            @error="onError"
            class="space-y-6"
          >
            <div>
              <label
                for="name"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >Name</label
              >
              <UInput
                v-model="editingUser.name"
                size="lg"
                type="text"
                id="name"
                ref="nameInput"
                class="block w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                placeholder="User name"
                autofocus
                required
              />
            </div>
            <div>
              <label
                for="email"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >Email</label
              >
              <UInput
                v-model="editingUser.email"
                size="lg"
                type="email"
                id="email"
                class="block w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                placeholder="User email"
                required
              />
            </div>
            <div>
              <label
                for="role"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >Role</label
              >
              <USelect
                v-model="editingUser.role"
                value-key="id"
                label-key="label"
                :options="['admin', 'user', 'junkshop_owner', 'baranggay_admin']"
                placeholder="Choose Role for the user"
                class="block w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
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
              @click="cancelEdit"
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

const users = ref([]);
const loading = ref(false);
const editingUser = reactive({
  ulid: "",
  name: "",
  email: "",
  role: "",
});
const drawerOpen = ref(false);

const userColumns = [
  { key: "name", label: "Name" },
  { key: "email", label: "Email" },
  { key: "role", label: "Role" },
  { key: "actions", label: "Actions" },
];

const editUser = async (user) => {
  editingUser.ulid = user.ulid;
  editingUser.name = user.name;
  editingUser.email = user.email;
  editingUser.role = user.role;
  drawerOpen.value = true;
  await nextTick();
};

const cancelEdit = () => {
  Object.assign(editingUser, { ulid: "", name: "", email: "", role: "" });
  drawerOpen.value = false;
};

const validate = (state) => {
  const errors = [];
  if (!state.name) errors.push({ path: "name", message: "Required" });
  if (!state.email) errors.push({ path: "email", message: "Required" });
  if (!state.role) errors.push({ path: "role", message: "Required" });
  return errors;
};

const onSubmit = async () => {
  console.log("Editing User:", editingUser); // Debugging line
  if (!editingUser.ulid) {
    console.error("User ULID is undefined");
    return;
  }
  try {
    await $fetch(`/users/${editingUser.ulid}`, {
      method: "PUT",
      body: JSON.stringify({
        name: editingUser.name,
        email: editingUser.email,
        role: editingUser.role,
      }),
      headers: {
        "Content-Type": "application/json",
      },
    });
    // Update the user in the local list
    const index = users.value.findIndex((u) => u.ulid === editingUser.ulid);
    if (index !== -1) {
      users.value[index] = { ...editingUser };
    }
    cancelEdit();
  } catch (error) {
    if (error.response && error.response.status === 422) {
      console.error("Validation error:", error.response.data.errors);
      alert("Validation error: " + JSON.stringify(error.response.data.errors));
    } else {
      console.error("Error updating user:", error);
      console.error(
        "Error details:",
        error.response ? error.response.data : error.message
      );
    }
  }
};

const onError = (event) => {
  const element = document.getElementById(event.errors[0].id);
  element?.focus();
  element?.scrollIntoView({ behavior: "smooth", block: "center" });
};

const deleteUser = async (userUlid) => {
  await $fetch(`/users/${userUlid}`, {
    method: "DELETE",
  });
  users.value = users.value.filter((user) => user.ulid !== userUlid);
};

const fetchUsers = async () => {
  try {
    loading.value = true;
    const data = await $fetch("/users", {
      method: "GET",
    });
    users.value = data;
    console.log("Users:", data);
  } catch (error) {
    console.error("Error fetching users:", error);
    console.error("Error details:", error.response ? error.response.data : error.message);
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  try {
    await fetchUsers();
  } catch (error) {
    console.error("Error during mounted hook:", error);
  }
});
</script>
