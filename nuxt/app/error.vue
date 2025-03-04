<script setup lang="ts">
/**
 * Props definition for the error component.
 * @property {Object} error - The error object passed to the component.
 */
const props = defineProps({
  error: Object,
});

/**
 * Handles the error by clearing it and redirecting to the home page.
 */
const handleError = () => clearError({ redirect: "/" });

/**
 * Sets the SEO metadata for the error page.
 * @property {string} title - The title of the error page.
 */
useSeoMeta({
  title: 'Error',
});
</script>

<template>
  <!--
    This Vue component displays an error page with the following structure:

    - A container with padding and centered content that includes the application logo.
    - A second container that grows to fill available space and centers its content vertically and horizontally.
      - Displays the error status code in a large, bold font.
      - Displays the error message.
      - If the error status code is 500 or greater, displays the error stack trace as HTML.
      - Includes a button that triggers the `handleError` method when clicked, labeled "Go back".

    Props:
    - error: An object containing error details such as `statusCode`, `message`, and `stack`.

    Methods:
    - handleError: A method to handle the error, typically used to navigate back or perform some recovery action.
  -->
  <UContainer class="py-5 flex items-center justify-center">
    <AppLogo />
  </UContainer>
  <UContainer class="flex-grow flex flex-col items-center justify-center space-y-5">
    <h1 class="text-9xl font-bold">{{ error?.statusCode }}</h1>
    <div>{{ error?.message }}</div>
    <div v-if="error?.statusCode >= 500" v-html="error?.stack"></div>
    <div>
      <UButton
        @click="handleError"
        color="gray"
        size="xl"
        variant="ghost"
        label="Go back"
      />
    </div>
  </UContainer>
</template>
