<template>
  <div 
    class="bg-white dark:bg-gray-800 shadow-sm border-r border-gray-200 dark:border-gray-700 transition-all duration-300"
    :class="modelValue ? 'w-16' : 'w-64'"
  >
    <!-- Logo and Collapse Button -->
    <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center space-x-3" :class="{ 'justify-center': modelValue }">
        <img
          src="/Logo.svg"
          alt="JunkHop Logo"
          class="h-8 w-8"
        />
        <span v-if="!modelValue" class="font-semibold text-gray-800 dark:text-white">
          JunkHop
        </span>
      </div>
      <UButton
        color="gray"
        variant="ghost"
        icon="i-heroicons-bars-3"
        size="sm"
        square
        @click="$emit('update:modelValue', !modelValue)"
      />
    </div>
    
    <!-- Navigation Items -->
    <div class="p-2 space-y-1">
      <UButton
        block
        to="/dashboard/junkshop"
        variant="ghost"
        color="gray"
        class="justify-start mb-2"
        :class="{ 'justify-center': modelValue }"
      >
        <template #leading>
          <UIcon name="i-heroicons-home" />
        </template>
        <span v-if="!modelValue">Dashboard</span>
      </UButton>
      
      <UButton
        block
        to="/dashboard/junkshop/inventory"
        variant="ghost"
        color="gray"
        class="justify-start mb-2"
        :class="{ 'justify-center': modelValue }"
      >
        <template #leading>
          <UIcon name="i-heroicons-cube" />
        </template>
        <span v-if="!modelValue">Inventory</span>
      </UButton>
      
      <UButton
        block
        to="/dashboard/junkshop/bids"
        variant="ghost"
        color="gray"
        class="justify-start mb-2"
        :class="{ 'justify-center': modelValue }"
      >
        <template #leading>
          <UIcon name="i-heroicons-currency-dollar" />
        </template>
        <span v-if="!modelValue">Bids</span>
      </UButton>
      
      <UButton
        block
        to="/dashboard/junkshop/merchants"
        variant="ghost"
        color="gray"
        class="justify-start mb-2"
        :class="{ 'justify-center': modelValue }"
      >
        <template #leading>
          <UIcon name="i-heroicons-users" />
        </template>
        <span v-if="!modelValue">Merchants</span>
      </UButton>
      
      <UButton
        block
        to="/dashboard/junkshop/profile"
        variant="ghost"
        color="gray"
        class="justify-start mb-2"
        :class="{ 'justify-center': modelValue }"
      >
        <template #leading>
          <UIcon name="i-heroicons-user-circle" />
        </template>
        <span v-if="!modelValue">Profile</span>
      </UButton>
      
      <UDivider v-if="!modelValue" class="my-3" />
      
      <UButton
        block
        to="/finder"
        variant="ghost"
        color="gray"
        class="justify-start mb-2"
        :class="{ 'justify-center': modelValue }"
      >
        <template #leading>
          <UIcon name="i-heroicons-map" />
        </template>
        <span v-if="!modelValue">Junkshop Finder</span>
      </UButton>
      
      <UButton
        block
        to="/marketplace"
        variant="ghost"
        color="gray"
        class="justify-start mb-2"
        :class="{ 'justify-center': modelValue }"
      >
        <template #leading>
          <UIcon name="i-heroicons-shopping-bag" />
        </template>
        <span v-if="!modelValue">Marketplace</span>
      </UButton>
    </div>
  </div>
</template>

<script setup>
// Props to handle the collapse state
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  }
});

// Emit the update to the parent component
defineEmits(['update:modelValue']);
</script>
