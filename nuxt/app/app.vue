<!--

  This Vue component serves as the main layout for the application. It includes the following elements:

  - <AppHeader />: The header component of the application.
  - <UContainer>: A container component that wraps the main content of the page.
    - Attributes:
      - as: Specifies the HTML element to render the container as (default is "main").
      - class: CSS classes for styling the container.
  - <NuxtPage />: A placeholder for the current page content rendered by Nuxt.js.
  - <AppFooter />: The footer component of the application.
  - <NuxtLoadingIndicator />: A loading indicator component for Nuxt.js.
    - Attributes:
      - class: CSS classes for styling the loading indicator.
      - :throttle: The throttle time in milliseconds before showing the loading indicator (default is 0).
  - <UModals />: A component for managing modals in the application.
  - <UNotifications>: A component for displaying notifications.
    - Slots:
      - #title: Slot for customizing the title of the notification.
        - Props:
          - title: The title of the notification.
      - #description: Slot for customizing the description of the notification.
        - Props:
          - description: The description of the notification.
-->
<script lang="ts" setup></script>

<template>
  <AppHeader />

  <UContainer
    as="main"
    class="flex flex-col flex-grow max-w-full px-0 mx-auto sm:px-0 lg:px-0"
  >
    <NuxtPage />
    <AppToTopBtn />
  </UContainer>

  <AppFooter />

  <NuxtLoadingIndicator class="!opacity-100" :throttle="0" />

  <UModals />

  <UNotifications>
    <template #title="{ title }">
      <span v-html="title" />
    </template>
    <template #description="{ description }">
      <span v-html="description" />
    </template>
  </UNotifications>
</template>
