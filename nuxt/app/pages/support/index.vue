<script lang="ts" setup>
import { ref } from "vue";

const form = ref({
  name: "",
  email: "",
  message: "",
});

const submitForm = () => {
  console.log("Form submitted:", form.value);
  // Add form submission logic here
};
</script>

<template>
  <div
    class="support-container p-6 my-auto w-screen max-w-7xl mx-auto bg-white rounded-xl shadow-md space-y-4 relative"
  >
    <h1 class="text-2xl font-bold">Support</h1>
    <p class="text-gray-600">
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
        class="w-full py-2 px-4 bg-teal-600 text-white text-center font-semibold rounded-md shadow-md hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2"
        color="teal"
        label="Submit"
        block
        />
    </UForm>
  </div>
</template>
