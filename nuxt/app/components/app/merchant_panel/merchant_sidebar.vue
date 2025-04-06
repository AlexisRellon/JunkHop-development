<template>
  <div
    class="flex flex-col bg-white dark:bg-gray-800 border-r dark:border-gray-700 transition-all duration-300"
    :class="{ 'w-64': !isCollapsed, 'w-16': isCollapsed }"
  >
    <!-- Logo and Toggle -->
    <div class="flex items-center justify-between h-16 px-4 border-b dark:border-gray-700">
      <div v-show="!isCollapsed" class="text-xl font-semibold text-gray-800 dark:text-white">
        Merchant Hub
      </div>
      <div v-show="isCollapsed" class="mx-auto">
        <UIcon name="i-heroicons-building-storefront" class="h-6 w-6 text-teal-500" />
      </div>
      <button
        @click="toggleSidebar"
        class="p-1 flex item-center text-gray-400 transition-colors rounded-lg hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-gray-700 focus:outline-none"
      >
        <UIcon :name="isCollapsed ? 'i-heroicons-chevron-right' : 'i-heroicons-chevron-left'" class="w-5 h-5" />
      </button>
    </div>

    <!-- Navigation links -->
    <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
      <NuxtLink
        v-for="item in links"
        :key="item.to"
        :to="item.to"
        class="flex truncate items-center py-3 text-gray-600 transition-colors rounded-lg dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white"
        :class="[
          $route.path === item.to
            ? 'active-link bg-teal-50 text-teal-700 dark:bg-teal-900 dark:bg-opacity-20 dark:text-teal-300'
            : '',
          isCollapsed ? 'px-3 justify-center' : 'px-4'
        ]"
        :title="item.label"
      >
        <UIcon :name="item.icon" class="w-5 h-5" :class="{ 'mr-3': !isCollapsed }" />
        <span v-show="!isCollapsed" class="flex-1 transition-opacity duration-300" :class="isCollapsed ? 'opacity-0 w-0' : 'opacity-100'">{{ item.label }}</span>
      </NuxtLink>
    </nav>

    <!-- User section -->
    <div class="px-2 py-4 mt-auto border-t dark:border-gray-700">
      <div 
        class="flex items-center gap-2 transition-all duration-300"
        :class="isCollapsed ? 'justify-center' : 'px-4 py-3'"
      >
        <UAvatar
          :src="$storage(auth.user.avatar)"
          :alt="auth.user.name"
          size="sm"
          :ui="{ rounded: 'rounded-full', ring: 'ring-2 ring-teal-500' }"
          :class="{ 'mx-auto': isCollapsed }"
        />
        <div v-if="!isCollapsed" class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-700 truncate dark:text-gray-200">{{ auth.user.name }}</p>
          <p class="text-xs text-gray-500 truncate dark:text-gray-400">{{ auth.user.email }}</p>
        </div>

        <UDropdown v-if="!isCollapsed" :items="userDropdownItems" :popper="{ placement: 'top-end' }">
          <UButton color="gray" variant="ghost" icon="i-heroicons-ellipsis-vertical" size="xs" />
        </UDropdown>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const emit = defineEmits(['update:modelValue']);
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
});

const isCollapsed = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value;
};

// Navigation links for the merchant dashboard
const links = [
  { label: "Dashboard", to: "/dashboard/merchant", icon: "i-heroicons-home" },
  { label: "Connections", to: "/dashboard/merchant/connections", icon: "i-heroicons-link" },
  { label: "Materials", to: "/dashboard/merchant/materials", icon: "i-heroicons-cube" },
  { label: "Find Junkshops", to: "/finder", icon: "i-heroicons-map" },
  { label: "Messages", to: "/dashboard/merchant/messages", icon: "i-heroicons-chat-bubble-left-ellipsis" },
  { label: "Settings", to: "/dashboard/merchant/settings", icon: "i-heroicons-cog-6-tooth" },
];

const userDropdownItems = [
  [
    {
      label: "Account",
      to: "/account/general",
      icon: "i-heroicons-user-circle",
    },
    {
      label: "Preferences",
      to: "/account/preferences",
      icon: "i-heroicons-adjustments-horizontal",
    },
  ],
  [
    {
      label: "Sign out",
      click: () => auth.logout(),
      icon: "i-heroicons-arrow-right-on-rectangle",
    },
  ],
];
</script>

<style scoped>
.active-link {
  position: relative;
}

.active-link::before {
  content: "";
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  height: 60%;
  width: 3px;
  background-color: rgb(20 184 166); /* teal-500 */
  border-radius: 0 4px 4px 0;
}
</style>