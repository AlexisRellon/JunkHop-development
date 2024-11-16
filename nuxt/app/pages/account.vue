<script lang="ts" setup>

/**
 * Page metadata configuration for the account page.
 * This sets up middleware to ensure the user is authenticated.
 */
definePageMeta({
  middleware: ["auth"],
});

/**
 * Array of navigation links for the account page.
 * Each link object contains:
 * - label: The display text for the link.
 * - icon: The icon class for the link.
 * - to: The route path the link navigates to.
 */
const links = [
  [
    {
      label: "Account",
      icon: "i-heroicons-user",
      to: "/account/general",
    },
    {
      label: "Devices",
      icon: "i-heroicons-device-phone-mobile",
      to: "/account/devices",
    },
  ],
];
</script>
<template>
  <!--
    This Vue component represents the account page layout in a Nuxt.js application.

    Components used:
    - NuxtLayout: A wrapper component for the layout.
    - UHorizontalNavigation: A custom component for horizontal navigation.
      - Props:
        - links: An array of link objects to be displayed in the navigation.
      - Class:
        - border-b: Adds a bottom border.
        - border-gray-200: Sets the border color to gray-200.
        - dark:border-gray-800: Sets the border color to gray-800 in dark mode.
        - mb-4: Adds a bottom margin of 4 units.
    - NuxtPage: A component that renders the current page.
      - Class:
        - col-span-10: Sets the column span to 10.
        - md:col-span-8: Sets the column span to 8 on medium and larger screens.
  -->
  <NuxtLayout>
    <UHorizontalNavigation
      :links="links"
      class="border-b border-gray-200 dark:border-gray-800 mb-4"
    />
    <NuxtPage class="col-span-10 md:col-span-8" />
  </NuxtLayout>
</template>
