<script lang="ts" setup>
/**
 * Reactive reference to the form data.
 * @type {Object}
 * @property {string} name - The name of the user.
 * @property {string} email - The email of the user.
 * @property {string} message - The message from the user.
 */
const form = ref({
  name: "",
  email: "",
  message: "",
});

/**
 * Handles the form submission.
 * Logs the form data to the console.
 * Add form submission logic within this function.
 */
const submitForm = () => {
  console.log("Form submitted:", form.value);
  // Add form submission logic here
};
</script>

<template>
  <!--
    This Vue component represents a support page where users can send a message if they have any questions or concerns.

    Components:
      - UDivider: A divider component.
      - UForm: A form component with a submit event handler and form state binding.
      - UFormGroup: A form group component with a label and name.
      - UInput: An input component with v-model binding for name and email.
      - UTextarea: A textarea component with v-model binding for the message.
      - UButton: A button component with type, color, label, and block properties.

    Methods:
      - submitForm: Method to handle form submission.
  -->
  <div
    class="relative w-screen p-6 mx-auto my-auto space-y-4 bg-white shadow-md support-container max-w-7xl dark:bg-gray-700 rounded-xl md:my-3 sm:my-5 dark:text-teal-500"
  >
    <h1 class="text-2xl font-bold">Support</h1>
    <p class="text-gray-600 dark:text-white">
      If you have any questions or concerns, feel free to send us a message.
    </p>
    <UDivider />
    <UForm @submit.prevent="submitForm" :state="form" class="space-y-4">
      <UFormGroup label="Name" name="name">
        <UInput
          id="name"
          v-model="form.name"
          class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500"
        />
      </UFormGroup>
      <UFormGroup label="Email" name="email">
        <UInput
          id="email"
          type="email"
          v-model="form.email"
          class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500"
        />
      </UFormGroup>
      <UFormGroup label="Message" name="message">
        <UTextarea
          id="message"
          v-model="form.message"
          class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500"
        />
      </UFormGroup>
      <UButton
        type="submit"
        class="w-full px-4 py-2 font-semibold text-center text-white bg-teal-600 rounded-md shadow-md hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 dark:text-white dark:bg-teal-600"
        color="teal"
        label="Submit"
        block
      />
    </UForm>
  </div>
</template>
