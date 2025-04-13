<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Materials of Interest</h1>
    
    <UCard class="mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200">Manage Material Interests</h2>
        <UButton
          to="/finder"
          color="amber"
          variant="soft"
          size="sm"
          icon="i-heroicons-cube"
        >
          Browse All Materials
        </UButton>
      </div>
      
      <p class="text-gray-600 dark:text-gray-400 mb-4">
        Select the recyclable materials your business is interested in. This helps junkshops understand your needs and preferences.
      </p>
    </UCard>

    <!-- Loading state -->
    <div v-if="loading" class="flex justify-center py-8">
      <UIcon name="i-heroicons-arrow-path" class="animate-spin text-amber-500 w-8 h-8" />
    </div>
    
    <!-- No materials state -->
    <UCard v-else-if="interestedMaterials.length === 0" class="text-center py-8">
      <div class="p-4 mb-4 bg-amber-100 dark:bg-amber-900/30 rounded-full inline-flex">
        <UIcon name="i-heroicons-exclamation-triangle" class="text-amber-500 w-8 h-8" />
      </div>
      <h3 class="text-xl font-medium text-gray-700 dark:text-gray-300 mb-2">No Materials Selected</h3>
      <p class="text-gray-600 dark:text-gray-400 mb-4 max-w-md mx-auto">
        You haven't selected any materials of interest yet. Browse available materials to add them to your profile.
      </p>
      <UButton
        @click="showMaterialBrowser = true"
        color="amber"
        icon="i-heroicons-cube"
      >
        Browse Materials
      </UButton>
    </UCard>
    
    <!-- Material interests list -->
    <UCard v-else class="mb-6">
      <h3 class="font-medium text-gray-800 dark:text-white mb-4">Your Material Interests</h3>
      <div class="flex flex-wrap gap-2">
        <UBadge 
          v-for="material in interestedMaterials" 
          :key="material.id" 
          color="amber"
          variant="subtle"
          size="lg"
          class="px-3 py-1"
        >
          {{ material.name }}
          <UButton 
            class="ml-1" 
            color="amber" 
            variant="ghost" 
            icon="i-heroicons-x-mark" 
            size="xs"
            square
            :ui="{
              rounded: 'rounded-full',
              padding: 'p-0.5'
            }"
            @click="removeMaterialInterest(material.id)"
          />
        </UBadge>
      </div>
      
      <template #footer>
        <UButton
          @click="showMaterialBrowser = true"
          color="amber"
          variant="soft"
          icon="i-heroicons-plus"
        >
          Add More Materials
        </UButton>
      </template>
    </UCard>
    
    <!-- Material browser -->
    <UModal v-model="showMaterialBrowser" :ui="{ width: 'sm:max-w-3xl' }">
      <UCard class="dark:bg-gray-800">
        <template #header>
          <div class="flex justify-between items-center">
            <h3 class="text-xl font-semibold dark:text-white">
              Browse Available Materials
            </h3>
            <UButton
              color="gray" 
              variant="ghost"
              icon="i-heroicons-x-mark"
              @click="showMaterialBrowser = false"
              size="sm"
              square
            />
          </div>
        </template>
        
        <!-- Search input -->
        <div class="mb-4">
          <UInput
            v-model="searchQuery"
            placeholder="Search for materials..."
            icon="i-heroicons-magnifying-glass"
            color="amber"
            trailing
            @clear="searchQuery = ''"
          />
        </div>
        
        <!-- Category filters -->
        <div class="mb-4 overflow-x-auto">
          <div class="flex gap-2 pb-2">
            <UBadge 
              v-for="category in categories" 
              :key="category"
              :color="selectedCategory === category ? 'amber' : 'gray'"
              :variant="selectedCategory === category ? 'solid' : 'soft'"
              size="md"
              class="cursor-pointer"
              @click="toggleCategory(category)"
            >
              {{ category }}
            </UBadge>
          </div>
        </div>
        
        <!-- Loading state for materials -->
        <div v-if="loadingAllMaterials" class="flex justify-center py-8">
          <UIcon name="i-heroicons-arrow-path" class="animate-spin text-amber-500 w-8 h-8" />
        </div>
        
        <!-- Materials grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-[400px] overflow-y-auto p-1">
          <UCard
            v-for="material in filteredMaterials"
            :key="material.id"
            :ui="{ 
              body: { 
                padding: 'p-3' 
              },
              ring: 'ring-1 hover:ring-2',
              color: isInterested(material.id) ? 'amber' : 'white'
            }"
            class="transition-all cursor-pointer"
            @click="toggleMaterialInterest(material)"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <UIcon
                  :name="isInterested(material.id) ? 'i-heroicons-check-circle' : 'i-heroicons-cube'"
                  class="mr-2"
                  :class="isInterested(material.id) ? 'text-amber-500' : 'text-gray-500'"
                />
                <span class="font-medium">{{ material.name }}</span>
              </div>
              <UBadge color="gray" size="xs">{{ material.category }}</UBadge>
            </div>
          </UCard>
        </div>
        
        <template #footer>
          <div class="flex justify-between">
            <div class="text-sm text-gray-500 dark:text-gray-400">
              {{ interestedMaterials.length }} materials selected
            </div>
            <UButton
              color="amber"
              @click="showMaterialBrowser = false"
            >
              Done
            </UButton>
          </div>
        </template>
      </UCard>
    </UModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';


const toast = useToast();
const loading = ref(true);
const loadingAllMaterials = ref(true);
const interestedMaterials = ref([]);
const allMaterials = ref([]);
const showMaterialBrowser = ref(false);
const searchQuery = ref('');
const selectedCategory = ref('');

// Computed categories from all materials
const categories = computed(() => {
  const uniqueCategories = new Set();
  allMaterials.value.forEach(material => {
    if (material.category) {
      uniqueCategories.add(material.category);
    }
  });
  return Array.from(uniqueCategories).sort();
});

// Filter materials based on search and category
const filteredMaterials = computed(() => {
  return allMaterials.value.filter(material => {
    const matchesSearch = searchQuery.value === '' || 
      material.name.toLowerCase().includes(searchQuery.value.toLowerCase());
    const matchesCategory = selectedCategory.value === '' || 
      material.category === selectedCategory.value;
    return matchesSearch && matchesCategory;
  });
});

// Check if a material is in the interested list
const isInterested = (materialId) => {
  return interestedMaterials.value.some(m => m.id === materialId);
};

// Toggle category selection
const toggleCategory = (category) => {
  selectedCategory.value = selectedCategory.value === category ? '' : category;
};

// Fetch interested materials
const fetchInterestedMaterials = async () => {
  try {
    loading.value = true;
    const response = await $fetch('/api/v1/merchant/interested-items');
    interestedMaterials.value = response || [];
  } catch (error) {
    console.error('Error fetching interested materials:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to load your interested materials.',
      color: 'red'
    });
  } finally {
    loading.value = false;
  }
};

// Fetch all available materials
const fetchAllMaterials = async () => {
  try {
    loadingAllMaterials.value = true;
    // This endpoint might need to be created or adjusted based on your API
    const response = await $fetch('/api/v1/items');
    allMaterials.value = response || [];
  } catch (error) {
    console.error('Error fetching all materials:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to load available materials.',
      color: 'red'
    });
  } finally {
    loadingAllMaterials.value = false;
  }
};

// Toggle interest in a material
const toggleMaterialInterest = async (material) => {
  try {
    const response = await $fetch(`/api/v1/merchant/item-interest/${material.id}`, {
      method: 'POST'
    });
    
    if (response.status === 'added') {
      // Add to interested materials if not already there
      if (!isInterested(material.id)) {
        interestedMaterials.value.push(material);
      }
      toast.add({
        title: 'Material Added',
        description: `Added ${material.name} to your interests.`,
        color: 'green'
      });
    } else if (response.status === 'removed') {
      // Remove from interested materials
      interestedMaterials.value = interestedMaterials.value.filter(m => m.id !== material.id);
      toast.add({
        title: 'Material Removed',
        description: `Removed ${material.name} from your interests.`,
        color: 'gray'
      });
    }
  } catch (error) {
    console.error('Error toggling material interest:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to update material interest.',
      color: 'red'
    });
  }
};

// Remove material interest
const removeMaterialInterest = async (materialId) => {
  try {
    const response = await $fetch(`/api/v1/merchant/item-interest/${materialId}`, {
      method: 'POST'
    });
    
    if (response.status === 'removed') {
      // Remove from interested materials
      interestedMaterials.value = interestedMaterials.value.filter(m => m.id !== materialId);
      toast.add({
        title: 'Material Removed',
        description: 'Material removed from your interests.',
        color: 'gray'
      });
    }
  } catch (error) {
    console.error('Error removing material interest:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to remove material interest.',
      color: 'red'
    });
  }
};

// Watch for modal opening to load materials if needed
watch(showMaterialBrowser, (newValue) => {
  if (newValue && allMaterials.value.length === 0) {
    fetchAllMaterials();
  }
});

// Fetch data on component mount
onMounted(() => {
  Promise.all([
    fetchInterestedMaterials(),
    fetchAllMaterials()
  ]);
});

// Define the parent layout
definePageMeta({
  layout: 'dashboard'
});
</script>
