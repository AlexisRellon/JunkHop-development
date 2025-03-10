<template>
  <div
    class="flex flex-col justify-between h-screen py-6 bg-white shadow-lg dark:bg-gray-800 z-10 transition-all duration-300 ease-in-out"
    :class="[modelValue ? 'w-20' : 'w-64']"
  >
    <div>
      
      <div class="flex items-center justify-between px-4">
        <!-- Only show title when sidebar is expanded -->
        <h1 v-if="!modelValue" class="text-2xl font-bold text-gray-900 dark:text-gray-100 truncate">
          Admin <span class="text-primary-600">Panel</span>
        </h1>
        
        <!-- Toggle Button - always visible -->
        <UButton
          @click="toggle"
          color="gray"
          variant="ghost"
          :icon="modelValue ? 'i-heroicons-bars-3' : 'i-heroicons-x-mark'"
          class="rounded-full"
          :class="modelValue ? 'mx-auto' : ''"
          aria-label="Toggle sidebar"
        />
      </div>

      <div v-if="!modelValue" class="mt-2 px-4">
        <p 
        class="text-sm text-gray-600 truncate 
        dark:text-gray-400"
        >
          {{ description || "Manage your application from here." }}
        </p>
      </div>

      <UDivider 
      class="my-4"
      />

      <h3
        v-if="!modelValue"
        class="px-6 py-2 text-lg font-semibold text-gray-700 dark:text-gray-300"
      >
        Navigation
      </h3>

      <div class="mt-4">
        <ul class="space-y-2">
          <li v-for="link in links" :key="link.to" class="px-2">
            <NuxtLink
              :to="link.to"
              class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors"
              :class="[
                $route.path === link.to
                  ? 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
              ]"
            >
              <UIcon :name="link.icon" class="flex-shrink-0" :class="modelValue ? 'text-2xl mx-auto' : ''" />
              <span v-if="!modelValue" class="truncate">{{ link.label }}</span>
            </NuxtLink>
          </li>
        </ul>
      </div>
    </div>

    <slot name="footer">
      <UButton
        class="mx-auto truncate"
        :icon="modelValue ? 'i-heroicons-arrow-left-on-rectangle' : ''"
        to="/"
        :label="modelValue ? '' : 'Go Back'"
        :trailing="false"
        color="teal"
        variant="ghost"
        size="lg"
        :class="[!modelValue ? 'w-full px-6 rounded-none hover:bg-gray-200 dark:hover:bg-gray-700' : 'rounded-full']"
      />
    </slot>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, defineProps, defineEmits } from "vue";

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  links: {
    type: Array,
    default: () => [
      { label: "Dashboard", to: "/dashboard", icon: "mdi-view-dashboard" },
      { label: "Users", to: "/dashboard/users", icon: "mdi-account-group" },
      { label: "Junkshops", to: "/dashboard/junkshop", icon: "mdi-home" },
      { label: "API Documentation", to: "/dashboard/api", icon: "mdi-api" },
    ],
  },
  description: {
    type: String,
    default: "",
  },
  persistKey: {
    type: String,
    default: "adminSidebarCollapsed",
  },
});

const emit = defineEmits(["update:modelValue"]);

const toggle = () => {
  emit("update:modelValue", !props.modelValue);

  // Store preference in localStorage if persistKey is provided
  if (process.client && props.persistKey) {
    localStorage.setItem(props.persistKey, !props.modelValue);
  }
};

// Initialize from localStorage if available
onMounted(() => {
  if (process.client && props.persistKey) {
    const savedState = localStorage.getItem(props.persistKey);
    if (savedState !== null) {
      emit("update:modelValue", savedState === "true");
    } else {
      // Default to expanded on desktop, collapsed on mobile
      emit("update:modelValue", window.innerWidth < 768);
    }
  }
});
</script>
