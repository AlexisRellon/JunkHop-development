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
  title: 'Sign Up | JunkHop',
  description: 'Join JunkHop to find nearby junk shops, manage recycling activities, and track your environmental impact',
  ogTitle: 'Sign Up for JunkHop',
  ogDescription: 'Create your account to start your recycling journey with JunkHop',
  twitterCard: 'summary',
  twitterTitle: 'Join JunkHop Today',
  twitterDescription: 'Create an account to find recycling centers and manage waste sustainably',
  keywords: 'recycling account, sustainability platform signup, junk shop finder registration'
})
</script>
<template>
  <UCard class="w-full max-w-md mx-auto my-20">
    <h1 class="text-3xl font-black mb-6 leading-tight tracking-tight">Sign Up</h1>
    <AuthRegister />
  </UCard>
</template>
