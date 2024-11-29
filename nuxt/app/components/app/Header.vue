<script lang="ts" setup>
const auth = useAuthStore();
const { $storage } = useNuxtApp();

const isDarkMode = ref(true);

// Save user's dark mode preference

const userItems = computed(() => [
  [
    {
      label: "User",
      slot: "overview",
      disabled: true,
    },
  ],
  [
    {
      label: "Account",
      to: "/account/general",
      icon: "i-heroicons-user",
    },
    {
      label: "Devices",
      to: "/account/devices",
      icon: "i-heroicons-device-phone-mobile",
    },
  ],
  [
    {
      label: isDarkMode.value ? "Light Mode" : "Dark Mode",
      icon: "i-heroicons-moon-20-solid",
      type: "checkbox" as const,
      checked: isDarkMode.value,
      click: () => {
        isDarkMode.value = !isDarkMode.value;
        document.documentElement.classList.toggle("dark");
      },
    },
  ],
  [
    {
      label: "Sign out",
      click: auth.logout,
      icon: "i-heroicons-arrow-left-on-rectangle",
    },
  ],
]);

const navItems = [
  {
    label: "Home",
    to: "/",
    icon: "i-heroicons-home-20-solid",
    target: "_self",
  },
  {
    label: "Dashboard",
    to: "/dashboard",
    icon: "i-heroicons-view-grid-20-solid",
    target: "_self",
    condition: auth.logged, // Only show when user is logged in | comment this condition to show always
  },
  {
    label: "Junk Shop Finder",
    to: "/finder",
    icon: "i-heroicons-map-20-solid",
    target: "_self",
  },
  {
    label: "Educational Resources",
    to: "/resources",
    icon: "i-heroicons-book-open-20-solid",
    target: "_self",
  },
  {
    label: "Notifications",
    to: "/notifications",
    icon: "i-heroicons-bell-20-solid",
    target: "_self",
    condition: auth.logged, // Only show when user is logged in | comment this condition to show always
  },
  {
    label: "Support",
    to: "/support",
    icon: "i-heroicons-question-mark-circle-20-solid",
    target: "_self",
  },
];

const isSideOpen = ref(false);
const openSide = () => {
  isSideOpen.value = true;
};

defineShortcuts({
  escape: {
    usingInput: true,
    whenever: [isSideOpen],
    handler: () => {
      isSideOpen.value = false;
    },
  },
});

import { useRoute } from 'vue-router';
import { computed } from 'vue';

const route = useRoute();

const isAdminPanel = computed(() => route.path.startsWith('/playgroundAdmin'));
const isJunkshopPanel = computed(() => route.path.startsWith('/playground-junkshopOwner'));
</script>
<template>
  <header
    :class="{
      'fixed top-0 z-50 w-full flex items-center justify-center -mb-px bg-white shadow-sm dark:bg-gray-900 dark:text-white': !isAdminPanel && !isJunkshopPanel,
      'top-0 w-full flex items-center justify-center -mb-px bg-white shadow-sm dark:bg-gray-900 dark:text-white': isAdminPanel,
      'sticky top-0 z-50 w-full flex items-center justify-center -mb-px bg-white shadow-sm dark:bg-gray-900 dark:text-white': isJunkshopPanel,
    }"
  >
    <UContainer
      class="flex items-center justify-between w-full h-16 gap-3 py-2 mx-auto sm"
    >
      <AppLogo class="lg:flex-1" />

      <nav class="hidden lg:flex">
        <ul
          class="flex flex-col items-end lg:flex-row lg:items-center lg:gap-x-8"
        >
          <li v-for="item in navItems" class="relative">
            <NuxtLink
              v-if="
                item.condition === undefined || (item.condition && auth.logged)
              "
              class="flex items-center gap-1 font-semibold text-sm/6 hover:text-primary"
              :to="item.to"
              :target="item.target"
              >{{ item.label }}</NuxtLink
            >
          </li>
        </ul>
      </nav>

      <div class="flex items-center justify-end gap-3 lg:flex-1">
        <!-- <AppTheme /> -->

        <UDropdown
          v-if="auth.logged"
          :items="userItems"
          :ui="{ item: { disabled: 'cursor-text select-text' } }"
          :popper="{ placement: 'bottom-end' }"
        >
          <!-- Notification Bell -->
          <UIcon
            name="i-heroicons-bell-20-solid"
            class="w-[25px] h-[25px] bg-gray-600"
          />
        </UDropdown>

        <UDropdown
          v-if="auth.logged"
          :items="userItems"
          :ui="{ item: { disabled: 'cursor-text select-text' } }"
          :popper="{ placement: 'bottom-end' }"
        >
          <UAvatar
            size="sm"
            :src="$storage(auth.user.avatar)"
            :alt="auth.user.name"
            :ui="{ rounded: 'rounded-md' }"
          />

          <template #overview>
            <div class="text-left">
              <p>Signed in as</p>
              <p class="font-medium text-gray-900 truncate dark:text-white">
                {{ auth.user.email }}
              </p>
            </div>
          </template>
        </UDropdown>
        <UButton
          v-else
          label="Log In"
          to="/auth/login"
          variant="ghost"
          color="gray"
        />

        <UButton
          class="lg:hidden"
          variant="ghost"
          color="gray"
          icon="i-heroicons-bars-3"
          @click="isSideOpen = true"
        />
      </div>
    </UContainer>
  </header>

  <USlideover v-model="isSideOpen" :ui="{ width: 'max-w-xs' }" @close="">
    <UContainer
      class="flex items-center justify-between h-16 gap-3 py-2 border-b border-dashed border-gray-200/80 dark:border-gray-800/80"
    >
      <AppLogo />
      <UButton
        variant="ghost"
        color="gray"
        icon="i-heroicons-x-mark-20-solid"
        @click="isSideOpen = false"
      />
    </UContainer>
    <UContainer class="flex-1 py-4 sm:py-6">
      <UVerticalNavigation :links="navItems">
        <template #default="{ link }">
          <span class="relative group-hover:text-primary">{{
            link.label
          }}</span>
        </template>
      </UVerticalNavigation>
    </UContainer>
  </USlideover>
</template>
