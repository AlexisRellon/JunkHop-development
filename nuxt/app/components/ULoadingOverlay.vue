<template>
  <div 
    v-if="show" 
    class="fixed inset-0 flex items-center justify-center bg-white/75 dark:bg-gray-900/75 z-50 backdrop-blur-sm"
  >
    <UIcon 
      name="i-heroicons-arrow-path" 
      class="animate-spin w-8 h-8 text-teal-500"
    />
    <span v-if="text" class="ml-2 font-medium text-gray-700 dark:text-gray-200">{{ text }}</span>
  </div>
</template>

<script setup>
const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  text: {
    type: String,
    default: ''
  }
});
</script>
