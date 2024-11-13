<script lang="ts" setup></script>

<template>
  <AppHeader />

  <UContainer
    as="main"
    class="flex-grow flex flex-col mx-0 px-0 sm:px-0 lg:px-0 max-w-screen-2xl"
  >
    <NuxtPage />
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
