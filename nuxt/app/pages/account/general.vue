<script lang="ts" setup>
useHead({
  htmlAttrs: {
    lang: 'en'
  }
});

useSeoMeta({
  title: 'Account Settings | JunkHop',
  description: 'Manage your JunkHop account profile, update personal information and change your password',
  ogTitle: 'JunkHop Account Settings',
  ogDescription: 'Access and modify your account settings and profile information',
  twitterCard: 'summary',
  twitterTitle: 'JunkHop Account Settings',
  twitterDescription: 'Manage your profile on the JunkHop platform',
  robots: 'noindex, nofollow' // For privacy protection of user account pages
})
</script>
<template>
  <UCard :ui="{ body: { base: 'grid grid-cols-12 gap-6 md:gap-8' } }">
    <div class="col-span-12 lg:col-span-4">
      <div class="text-lg font-semibold mb-2">Profile information</div>
      <div class="text-sm opacity-80">
        Update your account's profile information and email address.
      </div>
    </div>
    <div class="col-span-12 lg:col-span-8">
      <AccountUpdateProfile />
    </div>
    <UDivider class="col-span-12" />
    <div class="col-span-12 lg:col-span-4">
      <div class="text-lg font-semibold mb-2">Update Password</div>
      <div class="text-sm opacity-80">
        Ensure your account is using a long, random password to stay secure.
      </div>
    </div>
    <div class="col-span-12 lg:col-span-8">
      <AccountUpdatePassword />
    </div>
  </UCard>
</template>
