<script setup lang="ts">
/**
 * Router instance for navigation.
 */
const router = useRouter();

/**
 * Authentication store instance.
 */
const auth = useAuthStore();

/**
 * Scrolls the page smoothly to the details section.
 */
function scrollToDetails() {
  const detailsSection = document.getElementById("details-section");
  if (detailsSection) {
    detailsSection.scrollIntoView({ behavior: "smooth" });
  }
}

/**
 * Sets the SEO metadata for the page.
 */
useSeoMeta({
  title: "Home",
});

/* Text Animation */
import { ref, onMounted, computed } from "vue";
import { useAuthStore } from "@/stores/auth";

/**
 * Reactive reference for waste collected value.
 */
const wasteCollected = ref(0);

/**
 * Reactive reference for recycling rate value.
 */
const recyclingRate = ref(0);

/**
 * Reactive reference for community participation value.
 */
const communityParticipation = ref(0);

/**
 * DOM element reference for waste collected.
 */
const wasteCollectedElement = ref(null);

/**
 * DOM element reference for recycling rate.
 */
const recyclingRateElement = ref(null);

/**
 * DOM element reference for community participation.
 */
const communityParticipationElement = ref(null);

/**
 * Object to track if animations are done for each metric.
 */
interface AnimationState {
  wasteCollected: boolean;
  recyclingRate: boolean;
  communityParticipation: boolean;
}

let animationDone: AnimationState = {
  wasteCollected: false,
  recyclingRate: false,
  communityParticipation: false,
};

/**
 * Animates a value from start to end over a specified duration.
 * @param {Ref} refValue - The reactive reference to update.
 * @param {number} start - The starting value.
 * @param {number} end - The ending value.
 * @param {number} duration - The duration of the animation in milliseconds.
 */
function animateValue(refValue, start, end, duration) {
  let startTimestamp = null;
  const step = (timestamp) => {
    if (!startTimestamp) startTimestamp = timestamp;
    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
    refValue.value = Math.floor(progress * (end - start) + start);
    if (progress < 1) {
      window.requestAnimationFrame(step);
    }
  };
  window.requestAnimationFrame(step);
}

/**
 * Handles intersection events for observed elements.
 * @param {IntersectionObserverEntry[]} entries - The intersection entries.
 */
function handleIntersection(entries) {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      if (
        entry.target === wasteCollectedElement.value &&
        !animationDone.wasteCollected
      ) {
        animateValue(wasteCollected, 0, 500, 2000); // More conservative estimate
        animationDone.wasteCollected = true;
      } else if (
        entry.target === recyclingRateElement.value &&
        !animationDone.recyclingRate
      ) {
        animateValue(recyclingRate, 0, 45, 2000); // Initial phase estimate
        animationDone.recyclingRate = true;
      } else if (
        entry.target === communityParticipationElement.value &&
        !animationDone.communityParticipation
      ) {
        animateValue(communityParticipation, 0, 1000, 2000); // Current user base
        animationDone.communityParticipation = true;
      }
    }
  });
}

/**
 * Sets up the intersection observer on mounted.
 */
onMounted(() => {
  const observer = new IntersectionObserver(handleIntersection, {
    threshold: 1,
  });

  if (wasteCollectedElement.value) {
    observer.observe(wasteCollectedElement.value);
  }
  if (recyclingRateElement.value) {
    observer.observe(recyclingRateElement.value);
  }
  if (communityParticipationElement.value) {
    observer.observe(communityParticipationElement.value);
  }
});
</script>

<template>
  <section
    class="relative flex items-center justify-center h-screen py-20 overflow-visible bg-center bg-cover hero-section"
  >
    <div
      class="absolute inset-0 hero-image -z-10"
      style="
        background: linear-gradient(
            to bottom,
            rgba(0, 0, 0, 0.3),
            rgba(0, 0, 0, 0.6)
          ),
          url('hero-image.jpg');
        background-size: cover;
        background-position: center;
      "
    ></div>
    <div class="container mx-auto text-center">
      <h1 class="mb-4 text-5xl font-bold text-white dark:text-white">
        Connect with Local Junk Shops
        <span class="text-teal-300">Easily</span>
      </h1>
      <p class="mb-8 text-xl text-white">
        Find nearby junk shops, manage your recycling activities, and track your environmental impact
      </p>
      <div class="flex justify-center gap-5">
        <UButton
          label="Get Started"
          @click="router.push('/auth/register')"
          color="teal"
          size="xl"
          class="dark:text-white"
        />
        <UButton
          label="Learn More"
          @click="scrollToDetails"
          color="teal"
          variant="ghost"
        />
      </div>
    </div>
  </section>
  <ClientOnly>
    <section id="details-section" class="py-20 bg-gray-100 overview dark:bg-gray-800">
      <div class="container px-4 mx-auto">
        <h2 class="mb-8 text-4xl font-bold text-center text-teal-500">
          About JunkHop
        </h2>
        <p class="mb-12 text-xl text-center">
          A platform that bridges the gap between residents and junk shops, making recycling more accessible and organized.
        </p>
        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
          <div class="text-center feature">
            <UIcon
              name="i-heroicons-map-pin"
              class="mx-auto mb-4 text-teal-500"
              size="48"
            />
            <h3 class="mb-2 text-2xl font-semibold">Find Junk Shops</h3>
            <p>Discover verified junk shops in your vicinity</p>
          </div>
          <div class="text-center feature">
            <UIcon
              name="i-heroicons-user-circle"
              class="mx-auto mb-4 text-teal-500"
              size="48"
            />
            <h3 class="mb-2 text-2xl font-semibold">User Profiles</h3>
            <p>Manage your account</p>
          </div>
          <div class="text-center feature">
            <UIcon
              name="i-heroicons-building-storefront"
              class="mx-auto mb-4 text-teal-500"
              size="48"
            />
            <h3 class="mb-2 text-2xl font-semibold">Junk Shop Management</h3>
            <p>For shop owners to manage their business profile</p>
          </div>
        </div>
      </div>
    </section>
  </ClientOnly>

  <section id="key-features" class="py-20 bg-white key-features dark:bg-gray-900">
    <div class="container px-4 mx-auto">
      <h2 class="mb-8 text-4xl font-bold text-center">Key Features</h2>
      <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
        <div class="text-center feature">
          <UIcon
            name="mdi-map-marker"
            class="mx-auto mb-4 text-teal-500"
            size="48"
          />
          <h3 class="mb-2 text-2xl font-semibold">Junk Shop Finder</h3>
          <p>
            Map View: Displays junk shops with accepted materials and shop
            details.
          </p>
          <p>
            Shop Profiles: Shop details, including accepted items, and
            reviews.
          </p>
        </div>
        <div class="text-center feature">
          <UIcon
            name="mdi-book-open"
            class="mx-auto mb-4 text-teal-500"
            size="48"
          />
          <h3 class="mb-2 text-2xl font-semibold">Educational Resources</h3>
          <p>Recycling Tips: Guides on sorting and preparing items.</p>
          <p>
            Accepted Items List: Lists recyclable materials and items for junk
            shops.
          </p>
          <p>
            Environmental Facts: Facts about how recycling benefits the
            environment.
          </p>
        </div>
        <div class="text-center feature">
          <UIcon name="mdi-bell" class="mx-auto mb-4 text-teal-500" size="48" />
          <h3 class="mb-2 text-2xl font-semibold">Push Notifications</h3>
          <p>Tips and Updates: Reminders and eco-friendly guidelines.</p>
        </div>
      </div>
    </div>
  </section>

  <section id="how-it-works" class="py-20 bg-gray-100 how-it-works dark:bg-gray-800">
    <div class="container px-4 mx-auto">
      <h2 class="mb-8 text-4xl font-bold text-center">How It Works</h2>
      <div class="grid grid-cols-1 gap-8 text-center md:grid-cols-3">
        <div class="step">
          <UIcon
            name="i-heroicons-user-circle"
            class="mx-auto mb-4 text-teal-500 animate-pulse"
            size="48"
          />
          <h3 class="mb-2 text-2xl font-semibold">Create Account</h3>
          <p>Register as a user or junk shop owner to access the platform.</p>
        </div>
        <div class="step">
          <UIcon
            name="i-heroicons-map-pin"
            class="mx-auto mb-4 text-teal-500 animate-bounce"
            size="48"
          />
          <h3 class="mb-2 text-2xl font-semibold">Find & Connect</h3>
          <p>
            Locate nearby junk shops or list your shop for others to find.
          </p>
        </div>
        <div class="step">
          <UIcon
            name="i-heroicons-building-storefront"
            class="mx-auto mb-4 text-teal-500 animate-pulse"
            size="48"
          />
          <h3 class="mb-2 text-2xl font-semibold">Manage Profile</h3>
          <p>Update your profile and manage your shop information.</p>
        </div>
      </div>
    </div>
  </section>

  <section id="community-impact" class="py-20 bg-white community-impact dark:bg-gray-900">
    <div class="container px-4 mx-auto">
      <h2 class="mb-8 text-4xl font-bold text-center">Community Impact</h2>
      <div class="grid grid-cols-1 gap-8 text-center md:grid-cols-3">
        <div class="stat">
          <h3 class="mb-2 text-2xl font-semibold">Waste Collected</h3>
          <p
            class="text-4xl font-bold text-teal-500"
            ref="wasteCollectedElement"
          >
            {{ wasteCollected }} Tons
          </p>
        </div>
        <div class="stat">
          <h3 class="mb-2 text-2xl font-semibold">Recycling Rate</h3>
          <p
            class="text-4xl font-bold text-teal-500"
            ref="recyclingRateElement"
          >
            {{ recyclingRate }}%
          </p>
        </div>
        <div class="stat">
          <h3 class="mb-2 text-2xl font-semibold">Community Participation</h3>
          <p
            class="text-4xl font-bold text-teal-500"
            ref="communityParticipationElement"
          >
            {{ communityParticipation }} Members
          </p>
        </div>
      </div>
      <div class="mt-12 testimonials">
        <h3 class="mb-8 text-3xl font-semibold text-center">Success Stories</h3>
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
          <div class="testimonial">
            <p class="italic">
              "JunkHop has transformed our city's waste management. The
              community is more engaged than ever!"
            </p>
            <p class="mt-4 font-bold">- City Official</p>
          </div>
          <div class="testimonial">
            <p class="italic">
              "Thanks to JunkHop, I've been able to track my recycling efforts
              and make a real impact."
            </p>
            <p class="mt-4 font-bold">- Happy User</p>
          </div>
        </div>
      </div>

    </div>
  </section>

  <section
    id="join-movement"
    class="py-20 text-white bg-teal-600 dark:bg-teal-700 join-movement"
  >
    <div class="container px-4 mx-auto text-center">
      <h2 class="mb-8 text-4xl font-bold">Join the Movement</h2>
      <p class="mb-8 text-xl">
        Become part of the JunkHop community and make a positive impact on the
        environment.
      </p>
      <UButton
        label="Get Started"
        to="/auth/register"
        color="white"
        variant="outline"
        size="xl"
      />
      <div class="mt-8 social-media">
        <h3 class="mb-4 text-2xl font-semibold">Follow Us</h3>
        <div class="flex justify-center gap-4">
          <a href="#" class="text-white"
            ><UIcon name="mdi-facebook" size="24"
          /></a>
          <a href="#" class="text-white"
            ><UIcon name="mdi-twitter" size="24"
          /></a>
          <a href="#" class="text-white"
            ><UIcon name="mdi-instagram" size="24"
          /></a>
        </div>
      </div>
    </div>
  </section>
</template>

<style>
.hero-image {
  background-size: cover !important;
  background-position: center !important;
  filter: saturate(1.2);
}
</style>
