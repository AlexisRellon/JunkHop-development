<template>
  <aside
    class="z-10 flex flex-col h-screen transition-width duration-300 ease-in-out bg-white border-r dark:bg-gray-800 dark:border-gray-700"
    :class="[isCollapsed ? 'w-[5rem]' : 'w-64']"
  >
    <!-- Logo and collapse button section -->
    <div class="p-4 flex items-center justify-between">
      <NuxtLink
        to="/"
        class="flex items-center flex-shrink-0 gap-2 text-xl font-semibold text-gray-800 dark:text-white transition-all duration-300"
        :class="isCollapsed ? 'justify-center mx-auto' : ''"
      >
        <img src="/Logo.svg" alt="Logo" class="w-8 h-8" />
        <span v-show="!isCollapsed" class="transition-opacity duration-300" :class="isCollapsed ? 'opacity-0' : 'opacity-100'">CleanSnap</span>
      </NuxtLink>
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
          :size="isCollapsed ? 'sm' : 'md'"
          class="rounded-full"
        />
        <div v-show="!isCollapsed" class="flex-1 min-w-0 transition-opacity duration-300" :class="isCollapsed ? 'opacity-0 w-0' : 'opacity-100'">
          <p class="text-sm font-medium text-gray-800 truncate dark:text-white">
            {{ auth.user.name }}
          </p>
          <p class="text-xs text-gray-500 truncate dark:text-gray-400">
            Admin
          </p>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed } from "vue";
import { useAuthStore } from "@/stores/auth";

const auth = useAuthStore();
const { $storage } = useNuxtApp();

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:modelValue"]);

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

// Updated navigation links with new admin sections
const links = [
  { label: "Dashboard", to: "/dashboard", icon: "mdi-view-dashboard" },
  { label: "Users", to: "/dashboard/users", icon: "mdi-account-group" },
  { label: "Junkshops", to: "/dashboard/junkshop", icon: "mdi-home" },
  { label: "Bid Management", to: "/dashboard/bids", icon: "mdi-currency-usd" },
  { label: "Activity", to: "/dashboard/activity", icon: "mdi-clock" },
  // { label: "Schedule", to: "/dashboard/schedule", icon: "mdi-calendar" },
  // { label: "Reports", to: "/dashboard/reports", icon: "mdi-chart-bar" },
  // { label: "Notifications", to: "/dashboard/notifications", icon: "mdi-bell" },
  // { label: "Settings", to: "/dashboard/settings", icon: "mdi-cog" },
  { label: "API Documentation", to: "/dashboard/api", icon: "mdi-api" },
];
</script>

<style scoped>
/* For Safari and other browsers that might not support transition-width */
.transition-width {
  transition-property: width;
}

/* Using direct class assignment instead of @apply to avoid circular dependency */
.active-link .router-link-active {
  color: #0d9488; /* text-teal-600 */
}

.dark .active-link .router-link-active {
  color: #5eead4; /* dark:text-teal-300 */
}
</style>
