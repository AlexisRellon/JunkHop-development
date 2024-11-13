<script lang="ts" setup></script>
<template>
  <footer
    class="py-10 flex flex-col gap-5 bg-teal-900 text-white"
  >
    <UContainer class="flex justify-between py-4 text-sm">
      <span>
        <AppLogoAlt />
      </span>
      <span>
        &copy; {{ new Date().getFullYear() }} CleanSnap. All rights reserved.
      </span>
    </UContainer>
    <UContainer class="flex justify-between py-4 text-sm">
      <div class="flex flex-col md:flex-row justify-between w-full">
        <div class="flex flex-col gap-5 mb-4 md:mb-0">
          <h3 class="font-semibold">Quick Links</h3>
          <ul class="list-none flex flex-col gap-3">
            <li>
              <NuxtLink to="/" class="hover:underline">Home</NuxtLink>
            </li>
            <li>
              <NuxtLink to="/finder" class="hover:underline">Finder</NuxtLink>
            </li>
            <li>
              <NuxtLink to="/resources" class="hover:underline"
                >Resources</NuxtLink
              >
            </li>
            <li>
              <NuxtLink to="/support" class="hover:underline">Support</NuxtLink>
            </li>
          </ul>
        </div>
        <div class="flex flex-col gap-5 mb-4 md:mb-0">
          <h3 class="font-semibold">Contact Information</h3>
          <ul class="list-none flex flex-col gap-3">
            <li>
              Email:
              <a href="mailto:support@cleansnap.com" class="hover:underline"
                >support@cleansnap.com</a
              >
            </li>
            <li>Address: 123 CleanSnap St, Clean City, CS 12345</li>
            <li>
              <NuxtLink to="/support" class="hover:underline"
                >Contact Form</NuxtLink
              >
            </li>
          </ul>
        </div>
        <div class="flex flex-col gap-5 sm:text-left text-right">
          <h3 class="font-semibold">Legal Links</h3>
          <ul class="list-none flex flex-col gap-3">
            <li>
              <NuxtLink to="/privacy-policy" class="hover:underline"
                >Privacy Policy</NuxtLink
              >
            </li>
            <li>
              <NuxtLink to="/terms-of-service" class="hover:underline"
                >Terms of Service</NuxtLink
              >
            </li>
          </ul>
        </div>
      </div>
    </UContainer>
  </footer>
</template>
