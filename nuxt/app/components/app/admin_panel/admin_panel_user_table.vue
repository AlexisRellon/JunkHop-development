<template>
  <!-- Users Table Card -->
  <UCard
    class="col-span-1 p-8 bg-white rounded-lg shadow-lg md:col-span-2 lg:col-span-3 dark:bg-gray-800"
  >
    <span class="flex items-center justify-between">
      <h2 class="mb-4 text-2xl font-bold dark:text-gray-100">Users</h2>
      <UButton
        @click="openAddUserDrawer"
        color="teal"
        variant="solid"
        class="mb-4"
        >Add User</UButton
      >
    </span>
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
            autocomplete="off"
          >
            <UFormGroup label="Name" name="name" required>
              <UInput
                v-model="editingUser.name"
                size="lg"
                type="text"
                id="name"
                ref="nameInput"
                class="relative w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                placeholder="User name"
                autofocus
              >
                <template #leading>
                  <UIcon name="mdi-account" class="text-gray-400"></UIcon>
                </template>
              </UInput>
            </UFormGroup>

            <UFormGroup label="Email" name="email" required>
              <UInput
                v-model="editingUser.email"
                size="lg"
                type="email"
                id="email"
                class="relative w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                placeholder="User email"
              >
                <template #leading>
                  <UIcon name="mdi-at" class="text-gray-100" />
                </template>
              </UInput>
            </UFormGroup>

            <UFormGroup label="Role" name="role" required>
              <USelect
                v-model="editingUser.role"
                :options="roleOptions"
                placeholder="Choose Role for the user"
                class="relative w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                size="lg"
              >
                <template #leading>
                  <UIcon name="mdi-account-cog" class="text-gray-400"></UIcon>
                </template>
              </USelect>
            </UFormGroup>

            <UFormGroup v-if="!isEditing" label="Password" name="password" hint="min 8 characters"
            :ui="{ hint: 'text-xs text-gray-500 dark:text-gray-400' }" required>
              <UInput
                v-model="editingUser.password"
                size="lg"
                type="password"
                id="password"
                class="relative w-full mt-1 text-gray-900 bg-white border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                placeholder="User password"
              >
                <template #leading>
                  <UIcon name="mdi-lock" class="text-gray-400"></UIcon>
                </template>
              </UInput>
            </UFormGroup>

            <UButton
              type="submit"
              color="teal"
              variant="solid"
              class="flex justify-center w-full py-2 rounded-md"
            >{{ isEditing ? "Update" : "Add" }}</UButton>
            <UButton
              @click="cancelEdit"
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
import { useRouter } from 'vue-router';

const users = ref([]);
const loading = ref(false);
const editingUser = reactive({
  ulid: "",
  name: "",
  email: "",
  role: "",
  password: "", // Add password field
});
const drawerOpen = ref(false);
const isEditing = ref(false);

const userColumns = [
  { key: "name", label: "Name" },
  { key: "email", label: "Email" },
  { key: "role", label: "Role" },
  { key: "actions", label: "Actions" },
];

const router = useRouter();
const toast = useToast();

// Update roleOptions to match exact role names from Laravel permissions
const roleOptions = [
  'admin',
  'user',
  'junkshop_owner',
  'baranggay_admin'
];

/**
 * Edit user details.
 *
 * @param {Object} user - The user object to edit.
 */
const editUser = async (user) => {
  console.log('Original user data:', user); // Debug log

  // First set the editing mode and open drawer
  isEditing.value = true;
  drawerOpen.value = true;

  await nextTick();

  // Then set the user data
  editingUser.ulid = user.ulid;
  editingUser.name = user.name;
  editingUser.email = user.email;
  editingUser.role = user.role || user.roles?.[0] || ''; // Handle both single role and array
  editingUser.password = '';

  console.log('Edited user data:', editingUser); // Debug log
};

/**
 * Open the drawer to add a new user.
 */
const openAddUserDrawer = () => {
  // Reset the form with empty values
  Object.assign(editingUser, {
    ulid: '',
    name: '',
    email: '',
    role: '',
    password: ''
  });
  isEditing.value = false;
  drawerOpen.value = true;
};

/**
 * Cancel the edit operation.
 */
const cancelEdit = () => {
  Object.assign(editingUser, { ulid: "", name: "", email: "", role: "" });
  drawerOpen.value = false;
};

/**
 * Validate the form state.
 *
 * @param {Object} state - The form state to validate.
 * @returns {Array} - An array of validation errors.
 */
const validate = (state) => {
  const errors = [];
  if (!state.name) errors.push({ path: "name", message: "Required" });
  if (!state.email) errors.push({ path: "email", message: "Required" });
  if (!state.role) errors.push({ path: "role", message: "Required" });
  return errors;
};

/**
 * Handle form submission.
 */
const onSubmit = async () => {
  if (isEditing.value) {
    try {
      // Ensure role is a string
      const role = typeof editingUser.role === 'object' ? editingUser.role.value : editingUser.role;

      const updateData = {
        name: editingUser.name.trim(),
        email: editingUser.email.trim(),
        role: role, // Send role as string
      };

      // Only include password if provided
      if (editingUser.password && editingUser.password.trim() !== '') {
        updateData.password = editingUser.password.trim();
      }

      console.log('Sending update request with data:', updateData);

      // Debug: Log the URL being used
      console.log(`Update URL: /users/${editingUser.ulid}`);

      // Use $fetch instead of axios for consistency
      const response = await $fetch(`/users/${editingUser.ulid}`, {
        method: "PUT",
        body: updateData,
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        }
      });

      console.log('Update response:', response); // Log the response

      if (response?.user) {
        // Update local state
        const index = users.value.findIndex((u) => u.ulid === editingUser.ulid);
        if (index !== -1) {
          users.value[index] = {
            ...users.value[index],
            ...response.user,
            // Ensure role is properly updated
            role: response.user.role
          };
        }

        await fetchUsers(); // Refresh data
        toast.add({
          icon: "i-heroicons-check-circle-20-solid",
          title: "User has been updated successfully.",
          color: "emerald",
        });
        cancelEdit();
      }
    } catch (error) {
      console.error('Error updating user:', error);
      console.error('Error details:', error.response?.data || error.message);
      toast.add({
        icon: "i-heroicons-x-circle-20-solid",
        title: "Error updating user",
        description: error.response?.data?.error || error.message || "Unknown error",
        color: "red",
      });
    }
  } else {
    // Handle add user case
    try {
      // For new users, use the users endpoint instead of register
      await $fetch(`/users`, {
        method: "POST",
        body: {
          name: editingUser.name,
          email: editingUser.email,
          password: editingUser.password,
          role: editingUser.role
        },
      });

      await fetchUsers();
      toast.add({
        icon: "i-heroicons-check-circle-20-solid",
        title: "User has been added successfully.",
        color: "emerald",
      });
      cancelEdit();
    } catch (error) {
      console.error('Error adding user:', error);
      console.error('Error details:', error.response?.data || error.message);
      toast.add({
        icon: "i-heroicons-x-circle-20-solid",
        title: "Error adding user",
        description: error.response?.data?.error || error.message || "Unknown error",
        color: "red",
      });
    }
  }
};

/**
 * Handle form errors.
 *
 * @param {Object} event - The error event.
 */
const onError = (event) => {
  const element = document.getElementById(event.errors[0].id);
  element?.focus();
  element?.scrollIntoView({ behavior: "smooth", block: "center" });
};

/**
 * Delete a user.
 *
 * @param {string} userUlid - The ULID of the user to delete.
 */
const deleteUser = async (userUlid) => {
  try {
    await $fetch(`/users/${userUlid}`, {
      method: "DELETE",
    });
    users.value = users.value.filter((user) => user.ulid !== userUlid);
    toast.add({
      icon: "i-heroicons-check-circle-20-solid",
      title: "User has been deleted successfully.",
      color: "emerald",
    });
  } catch (error) {
    handleError(error, "deleting user");
  }
};

/**
 * Fetch the list of users.
 */
const fetchUsers = async () => {
  try {
    loading.value = true;
    const data = await $fetch("/users", {
      method: "GET",
      headers: {
        'Accept': 'application/json',
      }
    });
    users.value = data;
    console.log("Users:", data);
  } catch (error) {
    console.error("Error fetching users:", error);
    console.error(
      "Error details:",
      error.response ? error.response.data : error.message
    );
  } finally {
    loading.value = false;
  }
};

/**
 * Handle errors during API calls.
 *
 * @param {Object} error - The error object.
 * @param {string} action - The action being performed.
 */

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
  } catch (error) {
    console.error("Error during mounted hook:", error);
  }
});
</script>
