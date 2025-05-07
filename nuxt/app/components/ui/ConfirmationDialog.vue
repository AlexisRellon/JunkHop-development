<script lang="ts" setup>
/**
 * A reusable confirmation dialog component for actions that need user confirmation.
 * 
 * Usage:
 * <UiConfirmationDialog
 *   v-model:show="showConfirmation" 
 *   title="Delete Item" 
 *   :message="'Are you sure you want to delete this item?'" 
 *   @confirm="deleteItemAction" 
 *   confirm-label="Yes, Delete"
 *   confirm-color="red"
 * />
 */

import type { PropType } from 'vue';

// Define valid button colors
type ButtonColorType = 'primary' | 'gray' | 'green' | 'red' | 'blue' | 'yellow' | 'purple' | 'teal' | 'orange';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Confirm Action',
  },
  message: {
    type: String,
    default: 'Are you sure you want to proceed with this action?',
  },
  confirmLabel: {
    type: String,
    default: 'Confirm',
  },
  cancelLabel: {
    type: String,
    default: 'Cancel',
  },  confirmColor: {
    type: String as PropType<ButtonColorType>,
    default: 'primary',
  },
  confirmIcon: {
    type: String,
    default: '',
  },
  destructive: {
    type: Boolean,
    default: false,
  }
});

const emit = defineEmits(['update:show', 'confirm', 'cancel']);

const isOpen = computed({
  get: () => props.show,
  set: (value) => emit('update:show', value)
});

function onConfirm() {
  emit('confirm');
  isOpen.value = false;
}

function onCancel() {
  emit('cancel');
  isOpen.value = false;
}
</script>

<template>
  <UModal v-model="isOpen" :ui="{ width: 'sm:max-w-md' }">
    <UCard :ui="{ 
      ring: '', 
      divide: 'divide-y divide-gray-100 dark:divide-gray-800'
    }">
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">
            {{ title }}
          </h3>
          <UButton
            color="gray"
            variant="ghost"
            icon="i-heroicons-x-mark"
            @click="onCancel"
            size="xs"
            :padded="false"
            aria-label="Close"
            class="-my-1"
          />
        </div>
      </template>

      <div class="py-3">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          {{ message }}
        </p>
      </div>

      <template #footer>
        <div class="flex justify-end gap-3">
          <UButton
            color="gray"
            variant="ghost"
            @click="onCancel"
          >
            {{ cancelLabel }}
          </UButton>
          <UButton
            :color="confirmColor"
            :variant="destructive ? 'solid' : 'soft'"
            :icon="confirmIcon"
            @click="onConfirm"
          >
            {{ confirmLabel }}
          </UButton>
        </div>
      </template>
    </UCard>
  </UModal>
</template>
