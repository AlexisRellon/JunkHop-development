<template>
  <div class="p-8 bg-gray-50 dark:bg-gray-900">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Material Preferences</h1>
      <p class="text-gray-600 dark:text-gray-400">Set your preferred materials and price ranges to get tailored marketplace listings</p>
    </div>

    <!-- Material Preferences Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
      <div class="space-y-6">
        <div v-for="item in items" :key="item.id" class="border-b border-gray-200 dark:border-gray-700 pb-6 last:border-0 last:pb-0">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
              <UCheckbox 
                v-model="selectedItems" 
                :value="item.id"
                :name="`item-${item.id}`"
              />
              <h3 class="text-lg font-medium text-gray-800 dark:text-white">{{ item.name }}</h3>
            </div>
          </div>

          <div v-if="isItemSelected(item.id)" class="ml-7">
            <UFormGroup label="Preferred Price Range (₱ per kg)">
              <div class="flex items-center space-x-3">
                <UInput
                  v-model="itemPreferences[item.id].minPrice"
                  type="number"
                  placeholder="Min Price"
                  min="0"
                  class="w-32"
                />
                <span class="text-gray-500">to</span>
                <UInput
                  v-model="itemPreferences[item.id].maxPrice"
                  type="number"
                  placeholder="Max Price"
                  min="0"
                  class="w-32"
                />
              </div>
            </UFormGroup>
          </div>
        </div>
      </div>

      <div class="mt-6 flex justify-end">
        <UButton
          color="teal"
          :loading="isSaving"
          @click="savePreferences"
          icon="i-heroicons-check"
        >
          Save Preferences
        </UButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'

const items = ref([])
const selectedItems = ref([])
const itemPreferences = ref({})
const isSaving = ref(false)

// Check if an item is selected
const isItemSelected = (itemId) => selectedItems.value.includes(itemId)

// Initialize preferences when items are loaded
const initializePreferences = () => {
  items.value.forEach(item => {
    if (!itemPreferences.value[item.id]) {
      itemPreferences.value[item.id] = {
        minPrice: '',
        maxPrice: ''
      }
    }
  })
}

// Fetch all available items and merchant preferences
const fetchData = async () => {
  try {
    const [itemsResponse, preferencesResponse] = await Promise.all([
      $fetch('/items'),
      $fetch('/merchant/preferences')
    ])

    items.value = itemsResponse
    
    // Set selected items and preferences from saved data
    if (preferencesResponse) {
      selectedItems.value = preferencesResponse.map(pref => pref.item_id)
      preferencesResponse.forEach(pref => {
        itemPreferences.value[pref.item_id] = {
          minPrice: pref.min_price,
          maxPrice: pref.max_price
        }
      })
    }

    initializePreferences()
  } catch (error) {
    console.error('Error fetching data:', error)
    useToast().add({
      title: 'Error',
      description: 'Failed to load preferences',
      color: 'red'
    })
  }
}

// Save preferences
const savePreferences = async () => {
  isSaving.value = true
  try {
    const preferences = selectedItems.value.map(itemId => ({
      item_id: itemId,
      min_price: parseFloat(itemPreferences.value[itemId].minPrice) || 0,
      max_price: parseFloat(itemPreferences.value[itemId].maxPrice) || 0
    }))

    await $fetch('/merchant/preferences', {
      method: 'POST',
      body: { preferences }
    })

    useToast().add({
      title: 'Success',
      description: 'Your preferences have been saved',
      color: 'green'
    })
  } catch (error) {
    console.error('Error saving preferences:', error)
    useToast().add({
      title: 'Error',
      description: 'Failed to save preferences',
      color: 'red'
    })
  } finally {
    isSaving.value = false
  }
}

// Watch for changes in selected items
watch(selectedItems, () => {
  initializePreferences()
}, { deep: true })

onMounted(() => {
  fetchData()
})
</script>