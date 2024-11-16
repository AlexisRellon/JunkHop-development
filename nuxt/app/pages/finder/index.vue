<script setup lang="ts">
import { ref, computed } from "vue";
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
  location: string;
  plusCode?: string;
  contact: string;
}

/**
 * A list of junkshops with their details.
 *
 * @type {Ref<Junkshop[]>}
 * @property {number} id - The unique identifier for the junkshop.
 * @property {string} name - The name of the junkshop.
 * @property {string} location - The physical address of the junkshop.
 * @property {string} plusCode - The Google Plus Code for the junkshop's location.
 * @property {string} contact - The contact number for the junkshop.
 */
const junkshops = ref<Junkshop[]>([
  {
    id: 1,
    name: "Sly Junk Shop",
    location: "lot 1 phase 1, Block 2 Paliparan Rd",
    plusCode: "7XRQ+4J",
    contact: "0921 824 7276",
  },
  {
    id: 2,
    name: "Mustasi Junkshop",
    location: "Sitio Pook Paliparan 3",
    plusCode: "7XQH+43Q",
    contact: "0918 766 3609",
  },
  {
    id: 3,
    name: "Vestidas Junk Shop",
    location: "9W4P+RXV saulog compoun, saulog compund",
    plusCode: "9W4P+RXV",
    contact: "0936 920 7969",
  },
  {
    id: 4,
    name: "Junk Shop",
    location: "8XMP+Q94, Paliparan Rd",
    plusCode: "8XMP+Q94",
    contact: "0921 327 8159",
  },
  {
    id: 5,
    name: "De Castro Junkshop",
    location: "7XRQ+3HH",
    plusCode: "7XRQ+3HH",
    contact: "",
  },
  {
    id: 6,
    name: "Gorio junk shop",
    location: "Black b1 lot 9 luzviminda 1 DASMADASMARIÑIAS DASMADASMARIÑIAS",
    plusCode: "7XQH+43Q",
    contact: "0908 767 3071",
  },
  {
    id: 7,
    name: "Niluma ng kahapon",
    location: "Blk e 12 lot 4",
    plusCode: "7XQH+43Q",
    contact: "0910 711 2561",
  },
  {
    id: 8,
    name: "Junk Shop",
    location: "8XP2+VJ8, Congressional Road",
    plusCode: "8XP2+VJ8",
    contact: "0929 488 8858",
  },
  {
    id: 9,
    name: "Sean's Junk Shop",
    location: "Zone ll, Don Placido Campos Avenue",
    plusCode: "7XQH+43Q",
    contact: "0949 791 0688",
  },
  {
    id: 10,
    name: "Angela Mae Junkshop",
    location: "8X8F+M53",
    plusCode: "8X8F+M53",
    contact: "0920 658 0239",
  },
  {
    id: 11,
    name: "JVL Junkshop",
    location: "# 452 Malinta Sampaloc 2",
    plusCode: "7XQH+43Q",
    contact: "0908 494 6223",
  },
  {
    id: 12,
    name: "Lucky 19 Junk Shop",
    location: "205 L-1A PH, B, 2 Congressional Rd",
    plusCode: "7XQH+43Q",
    contact: "(046) 539 3734",
  },
  {
    id: 13,
    name: "Vestidas Junk Shop",
    location: "8W6X+MH3, General Emilio Aguinaldo Highway",
    plusCode: "8W6X+MH3",
    contact: "(046) 416 3273",
  },
  {
    id: 14,
    name: "Double B Junk Shop",
    location: "B14 L12, Saint Anthony Village",
    plusCode: "7XQH+43Q",
    contact: "(046) 540 1123",
  },
  {
    id: 15,
    name: "QRS Junkshop",
    location: "32 Jose Abad Santos",
    plusCode: "7XQH+43Q",
    contact: "0919 666 4044",
  },
  {
    id: 16,
    name: "Malayan Junkshop",
    location: "Pala-pala Rd",
    plusCode: "7XQH+43Q",
    contact: "0956 885 4672",
  },
  {
    id: 17,
    name: "501 Junk Shop",
    location: "8WXV+JX4, Congressional Ave",
    plusCode: "8WXV+JX4",
    contact: "0927 688 4509",
  },
  {
    id: 18,
    name: "Richard Dimaunahan Junk Shop",
    location: "Lot 10, Block 1, Barangay San Dionisio",
    plusCode: "7XQH+43Q",
    contact: "0915 749 8538",
  },
  {
    id: 19,
    name: "LAK JUNK SHOP",
    location: "7XX2+XG2, Governor's Dr",
    plusCode: "7XX2+XG2",
    contact: "No contact info",
  },
  {
    id: 20,
    name: "Taba Junk Shop",
    location: "BLK. 206 Lot 16 PH 5 Brgy. Paliparan III",
    plusCode: "7XQH+43Q",
    contact: "0920 218 3367",
  },
  {
    id: 21,
    name: "Polan Junk Shop",
    location: "Lot 10, Block 87, Phase 2, Barangay Paliparan III",
    plusCode: "7XQH+43Q",
    contact: "0920 211 7623",
  },
  {
    id: 22,
    name: "CJM Junk Shop",
    location: "8WXV+PWX, Congressional Ave",
    plusCode: "8WXV+PWX",
    contact: "No contact info",
  },
  {
    id: 23,
    name: "L.A. Junk Shop",
    location: "391 National Road",
    plusCode: "7XQH+43Q",
    contact: "0919 376 3765",
  },
  {
    id: 24,
    name: "Ashley Shawn Leila Junk Shop",
    location: "8XQP+C5P, Paliparan Rd",
    plusCode: "8XQP+C5P",
    contact: "0907 157 3932",
  },
  {
    id: 25,
    name: "Danlene Junkshop",
    location: "119 Sitio Pook, Danlene Junkshop, Paliparan lll, Paliparan Rd",
    plusCode: "7XQH+43Q",
    contact: "0939 904 2803",
  },
]);

/**
 * Opens a new tab with the Google Maps location of the specified junkshop.
 *
 * @param {Junkshop} shop - The junkshop object containing the name and location.
 */
const showOnMap = (shop: Junkshop) => {
  const googleMapsUrl = `https://www.google.com/maps?q=${shop.name}, ${shop.location}`;
  window.open(googleMapsUrl, "_blank");
};


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
    <p class="text-xl mt-2">Dasmariñas Cavite</p>
  </div>
  <div class="py-5 mx-auto flex flex-col items-center gap-4">
    <!-- Search Bar -->
    <div class="w-full flex gap-4">
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
      <UCard class="p-5 w-full" as="div">
        <ul class="grid grid-cols-1 md:grid-cols-2 gap-5" ref="parent">
          <!-- Show if there is no result -->
          <div v-if="filteredJunkshops.length === 0">
            <p class="text-center">No results found.</p>
          </div>
          <li
            v-for="shop in filteredJunkshops"
            :key="shop.id"
            class="p-4 border rounded-lg shadow-sm hover:shadow-md transition-shadow cursor-pointer"
          >
            <!-- Card for Junk Shop List -->
            <UCard :shop="shop" class="cursor-auto">
              <template #header>
                <h3 class="font-bold text-lg">{{ shop.name }}</h3>
              </template>
              <div class="flex flex-col gap-3">
                <div class="flex gap-2">
                  <UIcon
                    name="i-heroicons-map-pin-20-solid"
                    class="text-teal-500"
                  />
                  <p class="text-sm">{{ shop.location }}</p>
                </div>
                <div class="flex gap-2">
                  <UIcon
                    name="i-heroicons-phone-20-solid"
                    class="text-teal-500"
                  />
                  <p class="text-sm">{{ shop.contact }}</p>
                </div>
                <div class="flex gap-2">
                  <UIcon name="i-heroicons-plus" class="text-teal-500" />
                  <p class="text-sm">{{ shop.plusCode }}</p>
                </div>
              </div>
              <template #footer>
                <UButton @click.stop="() => showOnMap(shop)" class="mt-2"
                  >Show on Map</UButton
                >
              </template>
            </UCard>
          </li>
        </ul>
      </UCard>
    </div>
  </div>
</template>
