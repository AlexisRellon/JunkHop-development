<script lang="ts" setup>
definePageMeta({
  middleware: ['guest'],
});

useHead({
  htmlAttrs: {
    lang: 'en'
  }
});

useSeoMeta({
  title: 'Log In | JunkHop',
  description: 'Access your JunkHop account to manage recycling activities and connect with local junk shops',
  ogTitle: 'Log In to JunkHop',
  ogDescription: 'Access your account to track your environmental impact and recycling activities',
  twitterCard: 'summary',
  twitterTitle: 'Log In to JunkHop',
  twitterDescription: 'Access your sustainable waste management account'
})
</script>
<template>
  <UCard class="w-full max-w-md mx-auto my-20">
    <h1 class="text-3xl font-black mb-6 leading-tight tracking-tight">Log In</h1>
    <AuthLogin />
  </UCard>
</template>
