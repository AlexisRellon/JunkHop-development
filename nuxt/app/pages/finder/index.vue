<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
import { useAutoAnimate } from "@formkit/auto-animate/vue";

/**
 * Interface representing a Junkshop.
 */
interface Junkshop {
  id: number;
  name: string;
  address: string;
  description?: string;
  contact: string;
  ulid?: string;
}

/**
 * Interface representing a Junkshop Item.
 */
interface JunkshopItem {
  id: number;
  name: string;
  junkshop_id: string;
  item_id: number;
}

const junkshops = ref<Junkshop[]>([]);
const isLoading = ref(true);
const searchQuery = ref("");
const selectedFilterCategory = ref<string[]>([]);
const filterCategories = ref<string[]>([]);
const junkshopItems = ref<JunkshopItem[]>([]);
const isLoadingItems = ref(false);
const allJunkshopItems = ref<{[key: string]: JunkshopItem[]}>({});

/**
 * Fetch junkshops from the database on component mount.
 */
onMounted(async () => {
  try {
    await new Promise(resolve => setTimeout(resolve, 1000)); // Simulate network delay
    const response = await $fetch<Junkshop[]>('/junkshop');
    junkshops.value = response;
    
    // After fetching junkshops, fetch all their items in parallel
    await fetchAllJunkshopItems();
  } catch (error) {
    console.error('Failed to fetch junkshops:', error);
  } finally {
    isLoading.value = false;
  }
});

/**
 * Fetch items for all junkshops to build a complete database of items
 */
const fetchAllJunkshopItems = async () => {
  try {
    const junkshopsWithUlid = junkshops.value.filter(shop => shop.ulid);
    
    // Fetch items for each junkshop
    await Promise.all(junkshopsWithUlid.map(async (shop) => {
      if (shop.ulid) {
        try {
          const items = await $fetch<JunkshopItem[]>(`/junkshop/${shop.ulid}/items`);
          allJunkshopItems.value[shop.ulid] = items;
        } catch (error) {
          console.error(`Failed to fetch items for junkshop ${shop.name}:`, error);
        }
      }
    }));
    
    // Extract unique item names for filter categories
    const uniqueCategories = new Set<string>();
    Object.values(allJunkshopItems.value).forEach(items => {
      items.forEach(item => {
        if (item.name) {
          uniqueCategories.add(item.name);
        }
      });
    });
    
    filterCategories.value = Array.from(uniqueCategories).sort();
  } catch (error) {
    console.error('Failed to fetch all junkshop items:', error);
  }
};

/**
 * A computed property that filters the junkshops based on search query and selected category.
 */
const filteredJunkshops = computed(() => {
  // First, filter by search query
  let filtered = junkshops.value.filter((shop) =>
    shop.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    shop.address.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    (shop.description && shop.description.toLowerCase().includes(searchQuery.value.toLowerCase()))
  );
  
  // Then, filter by selected categories if any are selected
  if (selectedFilterCategory.value.length > 0) {
    filtered = filtered.filter(shop => {
      // If the shop has no ulid or no items, filter it out when categories are selected
      if (!shop.ulid || !allJunkshopItems.value[shop.ulid]) {
        return false;
      }
      
      // Check if any of the shop's items match any of the selected categories
      const shopItems = allJunkshopItems.value[shop.ulid];
      return shopItems.some(item => 
        selectedFilterCategory.value.includes(item.name)
      );
    });
  }
  
  return filtered;
});

const [parent] = useAutoAnimate();
const selectedJunkshop = ref<Junkshop | null>(null);
const open = ref(false);

/**
 * Method to open the modal with the selected junkshop details.
 */
const openModal = async (shop: Junkshop) => {
  selectedJunkshop.value = shop;
  open.value = true;
  
  // If the shop has a ulid, fetch its items
  if (shop.ulid) {
    await fetchJunkshopItems(shop.ulid);
  }
};

/**
 * Fetch items for a specific junkshop.
 */
const fetchJunkshopItems = async (junkshopUlid: string) => {
  try {
    isLoadingItems.value = true;
    const items = await $fetch<JunkshopItem[]>(`/junkshop/${junkshopUlid}/items`);
    junkshopItems.value = items;
  } catch (error) {
    console.error('Failed to fetch junkshop items:', error);
    junkshopItems.value = [];
  } finally {
    isLoadingItems.value = false;
  }
};

/**
 * Method to close the modal.
 */
const closeModal = () => {
  selectedJunkshop.value = null;
  open.value = false;
  // Clear items when closing modal
  junkshopItems.value = [];
};

/**
 * Method to redirect to Google Maps with the junkshop location.
 */
const redirectToGoogleMaps = (location: string) => {
  const url = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(location)}`;
  window.open(url, "_blank");
};

/**
 * Reset filters
 */
const resetFilters = () => {
  searchQuery.value = "";
  selectedFilterCategory.value = [];
};
</script>

<template>
  <!-- Hero Section with Parallax Effect -->
  <div
    class="relative h-[40vh] flex items-center justify-center overflow-hidden"
  >
    <div
      class="absolute inset-0 bg-cover bg-center transform transition-transform duration-[5000ms] animate-slow-zoom"
      style="
        background-image: url('/images/junkshop-finder-bg.jpg');
        background-size: cover;
      "
    ></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/40 to-black/70 dark:from-black/80 dark:via-black/60 dark:to-black/80"></div>
    
    <div class="relative z-10 text-center px-4 max-w-xl mx-auto">
      <UBadge
        color="white"
        variant="solid"
        class="mb-4"
      >
        <UIcon name="i-heroicons-map-pin" class="mr-1" />
        <span>Region IV-A</span>
      </UBadge>
      
      <h1 class="text-4xl md:text-6xl font-extrabold text-white mb-4 drop-shadow-lg">
        Junkshop 
        <span class="bg-clip-text text-transparent bg-gradient-to-r from-teal-400 to-emerald-500">Finder</span>
      </h1>
      
      <p class="text-lg text-gray-200 mb-8 max-w-lg mx-auto leading-relaxed">
        Discover local recycling centers near you and contribute to a cleaner environment
      </p>
    </div>
  </div>

  <!-- Search and Filter Section -->
  <div class="bg-white dark:bg-gray-900 py-6 border-b border-gray-200 dark:border-gray-800 sticky top-[64px] z-20 shadow-sm dark:shadow-gray-800/20">
    <div class="container mx-auto px-4 max-w-6xl">
      <div class="flex flex-col md:flex-row gap-4 items-center">
        <div class="w-full relative">
          <UInput
            v-model="searchQuery"
            color="gray"
            size="lg"
            placeholder="Search by name, address, or description..."
            class="dark:bg-gray-800"
          >
            <template #leading>
              <UIcon name="i-heroicons-magnifying-glass-20-solid" />
            </template>
          </UInput>
        </div>
        
        <div class="hidden md:block border-l border-gray-200 dark:border-gray-700 h-11 self-stretch"></div>
            
        <div>
          <USelectMenu
            v-model="selectedFilterCategory"
            :options="filterCategories"
            multiple
            searchable
            searchable-placeholder="Search materials..."
            color="gray"
            size="lg"
            class="min-w-[200px] relative"
          >
            <template #label>
              <div class="flex items-center gap-2">
                <UIcon name="i-heroicons-funnel" />
                <span class="text-gray-600">Filter by materials</span>
                <UBadge v-if="selectedFilterCategory.length" color="teal" size="xs">
                  {{ selectedFilterCategory.length }}
                </UBadge>
              </div>
            </template>
          </USelectMenu>
        </div>
      </div>
    </div>
  </div>

  <!-- Results Section -->
  <div class="bg-gray-50 dark:bg-gray-800 min-h-[60vh] py-8">
    <div class="container mx-auto px-4 max-w-6xl">
      <!-- Results Count -->
      <div class="flex items-center justify-between mb-6">
        <p class="text-gray-700 dark:text-gray-300">
          <span class="font-medium">{{ filteredJunkshops.length }}</span> junkshops found
          <span v-if="selectedFilterCategory.length" class="ml-1">
            accepting <span class="font-medium text-teal-600 dark:text-teal-400">
              {{ selectedFilterCategory.length > 1 
                ? `${selectedFilterCategory.length} materials` 
                : selectedFilterCategory[0] }}
            </span>
          </span>
        </p>
        
        <UButton
          v-if="selectedFilterCategory.length || searchQuery"
          color="gray"
          variant="ghost"
          size="sm"
          @click="resetFilters"
          class="hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          Clear filters
          <template #trailing>
            <UIcon name="i-heroicons-x-mark" />
          </template>
        </UButton>
      </div>

      <!-- Cards Grid -->
      <UCard class="border-0 shadow-sm dark:bg-gray-900 dark:shadow-gray-950/10">
        <!-- Loading State -->
        <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
          <UCard v-for="n in 21" :key="n" class="border shadow-sm animate-pulse dark:bg-gray-800 dark:border-gray-700">
            <div class="space-y-4">
              <USkeleton class="h-6 w-3/4" />
              <UDivider class="dark:border-gray-700" />
              <div class="space-y-3">
                <div class="flex gap-3">
                  <USkeleton class="h-5 w-5 rounded-full" />
                  <USkeleton class="h-5 flex-1" />
                </div>
                <div class="flex gap-3">
                  <USkeleton class="h-5 w-5 rounded-full" />
                  <USkeleton class="h-5 flex-1" />
                </div>
                <div class="flex gap-3">
                  <USkeleton class="h-5 w-5 rounded-full" />
                  <USkeleton class="h-5 flex-1" />
                </div>
              </div>
              <UDivider class="dark:border-gray-700" />
              <USkeleton class="h-10 w-1/3" />
            </div>
          </UCard>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredJunkshops.length === 0" class="py-16 text-center">
          <UIcon name="i-heroicons-face-frown" class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500 mb-4" />
          <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">No junkshops found</h3>
          <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto mb-6">
            We couldn't find any junkshops matching your search criteria. Try adjusting your filters or search term.
          </p>
          <UButton 
            color="teal" 
            @click="resetFilters"
            class="hover:shadow-md transition-shadow duration-300"
          >
            Reset filters
          </UButton>
        </div>

        <!-- Results -->
        <ul 
          v-else 
          ref="parent" 
          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4"
        >
          <li
            v-for="shop in filteredJunkshops"
            :key="shop.id"
            class="group"
          >
            <UCard 
              class="h-full border transition-all duration-300 hover:border-teal-500 hover:shadow-md dark:hover:border-teal-400 dark:bg-gray-800 dark:border-gray-700"
            >
              <template #header>
                <div class="flex justify-between items-center">
                  <h3 class="text-lg font-bold group-hover:text-teal-600 dark:text-gray-100 dark:group-hover:text-teal-400 transition-colors duration-300">
                    {{ shop.name }}
                  </h3>
                  <UBadge color="teal" variant="subtle" class="text-xs dark:bg-teal-900/50">Verified</UBadge>
                </div>
              </template>

              <div class="space-y-4 py-2">
                <p v-if="shop.description" class="text-gray-600 dark:text-gray-300 text-sm italic">
                  "{{ shop.description }}"
                </p>
                
                <div class="space-y-3">
                  <div class="flex items-start gap-3">
                    <UIcon
                      name="i-heroicons-map-pin"
                      class="h-5 w-5 text-teal-500 dark:text-teal-400 mt-0.5 flex-shrink-0"
                    />
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ shop.address }}</p>
                  </div>

                  <div class="flex items-start gap-3">
                    <UIcon
                      name="i-heroicons-phone"
                      class="h-5 w-5 text-teal-500 dark:text-teal-400 mt-0.5 flex-shrink-0"
                    />
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ shop.contact }}</p>
                  </div>

                  <div class="flex items-start gap-3">
                    <UIcon
                      name="i-heroicons-clock"
                      class="h-5 w-5 text-teal-500 dark:text-teal-400 mt-0.5 flex-shrink-0"
                    />
                    <p class="text-sm text-gray-700 dark:text-gray-300">Open 8:00 AM - 5:00 PM</p>
                  </div>
                </div>
              </div>

              <template #footer>
                <div class="flex justify-between items-center">
                  <UButton
                    color="teal"
                    variant="ghost"
                    @click="openModal(shop)"
                    icon="i-heroicons-information-circle"
                    class="text-sm hover:bg-teal-50 dark:hover:bg-teal-900/20"
                  >
                    Details
                  </UButton>

                  <UButton
                    color="teal"
                    variant="solid"
                    @click="redirectToGoogleMaps(shop.address)"
                    class="text-sm hover:shadow-md"
                  >
                    <template #leading>
                      <UIcon name="i-heroicons-map" />
                    </template>
                    View on Map
                  </UButton>
                </div>
              </template>
            </UCard>
          </li>
        </ul>
      </UCard>
    </div>
  </div>

  <!-- Modal for Junkshop Details -->
  <UModal v-model="open" :ui="{ width: 'sm:max-w-lg' }">
    <UCard v-if="selectedJunkshop" class="dark:bg-gray-800 dark:border-teal-600 border">
      <template #header>
        <div class="flex justify-between items-center">
          <h3 class="text-xl font-bold dark:text-white">{{ selectedJunkshop.name }}</h3>
          <UBadge color="teal" variant="subtle" class="dark:bg-teal-900/50">Verified</UBadge>
        </div>
      </template>
      
      <div class="space-y-6">
        <p v-if="selectedJunkshop.description" class="text-gray-600 dark:text-gray-300 italic border-l-4 border-teal-500 pl-4 py-2 bg-teal-50/50 dark:bg-teal-900/10 rounded-r">
          "{{ selectedJunkshop.description }}"
        </p>
        
        <div class="grid md:grid-cols-2 gap-6">
          <div class="space-y-4">
            <h4 class="font-semibold text-lg border-b pb-2 dark:border-gray-700 dark:text-gray-100">Contact Information</h4>
            
            <div class="space-y-3">
              <div class="flex items-start gap-3">
                <UIcon name="i-heroicons-map-pin" class="h-5 w-5 text-teal-500 dark:text-teal-400 mt-0.5 flex-shrink-0" />
                <p class="text-sm dark:text-gray-300">{{ selectedJunkshop.address }}</p>
              </div>
              
              <div class="flex items-start gap-3">
                <UIcon name="i-heroicons-phone" class="h-5 w-5 text-teal-500 dark:text-teal-400 mt-0.5 flex-shrink-0" />
                <p class="text-sm dark:text-gray-300">{{ selectedJunkshop.contact }}</p>
              </div>
              
              <div class="flex items-start gap-3">
                <UIcon name="i-heroicons-clock" class="h-5 w-5 text-teal-500 dark:text-teal-400 mt-0.5 flex-shrink-0" />
                <p class="text-sm dark:text-gray-300">Open 8:00 AM - 5:00 PM</p>
              </div>
              
              <div class="flex items-start gap-3">
                <UIcon name="i-heroicons-globe-alt" class="h-5 w-5 text-teal-500 dark:text-teal-400 mt-0.5 flex-shrink-0" />
                <p class="text-sm dark:text-gray-300">www.junkhop.com/{{ selectedJunkshop.name.toLowerCase().replace(/\s+/g, '-') }}</p>
              </div>
            </div>
          </div>
          
          <div class="space-y-4">
            <h4 class="font-semibold text-lg border-b pb-2 dark:border-gray-700 dark:text-gray-100">Accepted Materials</h4>
            
            <div v-if="isLoadingItems" class="py-2">
              <USkeleton class="h-8 w-full mb-2" />
              <USkeleton class="h-8 w-full" />
            </div>
            
            <div v-else-if="junkshopItems.length === 0" class="text-sm text-gray-500 dark:text-gray-400 py-2">
              No materials information available
            </div>
            
            <div v-else class="flex flex-wrap gap-2">
              <UBadge 
                v-for="item in junkshopItems" 
                :key="item.id" 
                color="gray" 
                class="dark:bg-gray-700"
              >
                {{ item.name }}
              </UBadge>
            </div>
            
            <h4 class="font-semibold text-lg border-b pb-2 dark:border-gray-700 mt-6 dark:text-gray-100">Ratings</h4>
            
            <div class="flex items-center">
              <div class="flex text-amber-400">
                <UIcon name="i-heroicons-star-solid" class="h-5 w-5" v-for="i in 4" :key="i" />
                <UIcon name="i-heroicons-star-half-solid" class="h-5 w-5" />
              </div>
              <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">4.5 (120 reviews)</span>
            </div>
          </div>
        </div>
      </div>
      
      <template #footer>
        <div class="flex justify-between items-center gap-4">
          <UButton color="gray" variant="ghost" @click="closeModal" class="hover:bg-gray-100 dark:hover:bg-gray-700">
            Close
          </UButton>
          
          <UButton color="teal"  @click="redirectToGoogleMaps(selectedJunkshop.address)" class="hover:shadow-md">
            <template #leading>
              <UIcon name="i-heroicons-map" />
            </template>
            View on Google Maps
          </UButton>
        </div>
      </template>
    </UCard>
  </UModal>
</template>

<style scoped>
.animate-slow-zoom {
  animation: slow-zoom 30s ease-in-out infinite alternate;
}

@keyframes slow-zoom {
  from {
    transform: scale(1);
  }
  to {
    transform: scale(1.1);
  }
}

/* Improve card hover effects */
.group:hover UCard {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Dark mode adjustments */
.dark .group:hover UCard {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5), 0 4px 6px -2px rgba(0, 0, 0, 0.3);
}
</style>
