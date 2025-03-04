<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useAutoAnimate } from "@formkit/auto-animate/vue";

/**
 * Interface representing a Junkshop.
 *
 * @interface Junkshop
 * @property {number} id - Unique identifier for the junkshop.
 * @property {string} name - Name of the junkshop.
 * @property {string} location - Physical location of the junkshop.
 * @property {string} [plusCode] - Optional Plus Code for the junkshop's location.
 * @property {string} contact - Contact information for the junkshop.
 */
interface Junkshop {
  id: number;
  name: string;
  address: string;
  description?: string;
  contact: string;
}

/**
 * A list of junkshops with their details.
 *
 * @type {Ref<Junkshop[]>}
 */
const junkshops = ref<Junkshop[]>([]);

/**
 * A reactive reference to indicate loading state.
 * @type {Ref<boolean>}
 */
const isLoading = ref(true);

/**
 * Fetch junkshops from the database on component mount.
 */
onMounted(async () => {
  const response = await $fetch<Junkshop[]>('/junkshop');
  junkshops.value = response;
  isLoading.value = false;
});

/**
 * A reactive reference to the search query input by the user.
 * @type {Ref<string>}
 */
const searchQuery = ref("");

/**
 * A computed property that filters the list of junkshops based on the search query.
 * It returns only those junkshops whose names include the search query (case-insensitive).
 * @type {ComputedRef<Array<Object>>}
 */
const filteredJunkshops = computed(() =>
  junkshops.value.filter((shop) =>
    shop.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
);

/**
 * Destructures the `parent` element from the `useAutoAnimate` hook.
 * `useAutoAnimate` is a custom hook that provides automatic animations for elements.
 * The `parent` element will be used to apply these animations to its child elements.
 */
const [parent] = useAutoAnimate();

/**
 * A reactive reference to the selected junkshop for the modal.
 * @type {Ref<Junkshop | null>}
 */
const selectedJunkshop = ref<Junkshop | null>(null);

const open = ref(false);

/**
 * Method to open the modal with the selected junkshop details.
 * @param {Junkshop} shop - The junkshop to show in the modal.
 */
const openModal = (shop: Junkshop) => {
  selectedJunkshop.value = shop;
  open.value = true;
};

/**
 * Method to close the modal.
 */
const closeModal = () => {
  selectedJunkshop.value = null;
  open.value = false;
};

/**
 * Method to redirect to Google Maps with the junkshop location.
 * @param {string} location - The location of the junkshop.
 */
const redirectToGoogleMaps = (location: string) => {
  const url = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(location)}`;
  window.open(url, "_blank");
};
</script>

<template>
  <!-- Header Title -->
  <div
    class="h-fit flex flex-col items-center justify-center text-center gap-5 py-[4rem] text-white w-full"
    style="
      background: linear-gradient(
          to bottom,
          rgba(0, 0, 0, 0.25),
          rgba(0, 0, 0, 0.5)
        ),
        url('/images/junkshop-finder-bg.jpg');
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center;
    "
  >
    <h1 class="text-5xl font-extrabold leading-tight">Junkshop Finder</h1>
    <p class="mt-2 text-xl">Dasmariñas Cavite</p>
  </div>
  <div class="flex flex-col items-center gap-4 py-5 mx-auto">
    <!-- Search Bar -->
    <div class="flex w-full gap-4">
      <div class="w-full">
        <UInput
          v-model="searchQuery"
          icon="i-heroicons-magnifying-glass-20-solid"
          color="gray"
          size="lg"
          placeholder="Search junkshops..."
        />
      </div>
    </div>
    <div class="w-[90vw]">
      <UCard class="w-full p-5" as="div">
        <ul ref="parent" class="grid grid-cols-1 gap-5 md:grid-cols-2 ">
          <!-- Show skeleton loader while loading -->
            <li v-for="n in 10" :key="n" class="flex flex-col w-full gap-5 p-4 border rounded-lg shadow-sm" v-if="isLoading">
              <USkeleton class="w-3/4 h-6 mb-2" />
              <UDivider />
              <span class="flex flex-col gap-2 mx-5">
                <span class="flex gap-4">
                  <USkeleton class="w-4 h-4 mb-2" />
                  <USkeleton class="w-full h-4 mb-2" />
                </span>
                <span class="flex gap-4">
                  <USkeleton class="w-4 h-4 mb-2" />
                  <USkeleton class="w-5/6 h-4 mb-2" />
                </span>
                <span class="flex gap-4">
                  <USkeleton class="w-4 h-4 mb-2" />
                  <USkeleton class="w-2/3 h-4 mb-2" />
                </span>
              </span>
              <UDivider />
              <USkeleton class="w-1/3 h-8 mt-2" />
            </li>
          <!-- Show if there is no result -->
          <div v-else-if="filteredJunkshops.length === 0">
            <p class="text-center">No results found.</p>
          </div>
          <!-- Show junkshops list -->
          <li
            v-else
            v-for="shop in filteredJunkshops"
            :key="shop.id"
            class="p-4 transition-shadow rounded-lg shadow-sm cursor-pointer hover:shadow-md"
          >
            <!-- Card for Junk Shop List -->
            <UCard :shop="shop" class="cursor-auto">
              <template #header>
                <h3 class="text-lg font-bold">{{ shop.name }}</h3>
              </template>
              <div class="flex flex-col gap-3">
                <div class="flex gap-2">
                  <UIcon
                    name="i-heroicons-information-circle-20-solid"
                    class="text-teal-500" />
                  <p class="text-sm">{{ shop.description }}</p>
                </div>
                <div class="flex gap-2">
                  <UIcon
                    name="i-heroicons-map-pin-20-solid"
                    class="text-teal-500"
                  />
                  <p class="text-sm">{{ shop.address }}</p>
                </div>
                <div class="flex gap-2">
                  <UIcon
                    name="i-heroicons-phone-20-solid"
                    class="text-teal-500"
                  />
                  <p class="text-sm">{{ shop.contact }}</p>
                </div>
              </div>
              <template #footer>
                <UButton @click="redirectToGoogleMaps(selectedJunkshop?.address)" class="mt-2">Show on Map</UButton>
              </template>
            </UCard>
          </li>
        </ul>
      </UCard>
    </div>
  </div>
</template>
