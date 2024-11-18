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
import { ref, onMounted } from "vue";

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
let animationDone = {
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
        animateValue(wasteCollected, 0, 1234, 2000);
        animationDone.wasteCollected = true;
      } else if (
        entry.target === recyclingRateElement.value &&
        !animationDone.recyclingRate
      ) {
        animateValue(recyclingRate, 0, 75, 2000);
        animationDone.recyclingRate = true;
      } else if (
        entry.target === communityParticipationElement.value &&
        !animationDone.communityParticipation
      ) {
        animateValue(communityParticipation, 0, 5678, 2000);
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
    threshold: 0.1,
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
    class="hero-section bg-cover bg-center py-20 h-screen flex items-center justify-center overflow-visible relative"
  >
    <div
      class="hero-image absolute inset-0 -z-10"
      style="
        background: linear-gradient(
            to bottom,
            rgba(0, 0, 0, 0.25),
            rgba(0, 0, 0, 0.5)
          ),
          url('hero-image.jpg');
        background-size: cover;
        background-position: center;
      "
    ></div>
    <div class="container mx-auto text-center">
      <h1 class="text-5xl font-bold text-white mb-4">
        Empowering Communities for a
        <span class="text-teal-300">Cleaner Tomorrow</span>
      </h1>
      <p class="text-xl text-white mb-8">
        Join us in creating sustainable cities through smart waste management
        and community engagement.
      </p>
      <div class="flex justify-center gap-5">
        <UButton
          label="Get Started"
          @click="router.push('/auth/register')"
          color="teal"
          size="xl"
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
  <ClientOnly> </ClientOnly>

  <ClientOnly>
    <section id="details-section" class="overview py-20 bg-gray-100">
      <div class="container mx-auto px-4">
        <h2 class="text-4xl font-bold text-teal-500 text-center mb-8">
          About CleanSnap
        </h2>
        <p class="text-xl text-center mb-12">
          CleanSnap helps communities report waste, find recycling resources,
          and contribute to sustainability. Our goal is to create sustainable
          cities through smart waste management and community engagement.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="feature text-center">
            <UIcon
              name="mdi-report"
              class="mx-auto mb-4 text-teal-500"
              size="48"
            />
            <h3 class="text-2xl font-semibold mb-2">Real-Time Reporting</h3>
            <p>Residents report waste issues directly to local authorities.</p>
          </div>
          <div class="feature text-center">
            <UIcon
              name="mdi-chart-bar"
              class="mx-auto mb-4 text-teal-500"
              size="48"
            />
            <h3 class="text-2xl font-semibold mb-2">Data Analytics</h3>
            <p>
              Cities optimize resources through insights on waste collection.
            </p>
          </div>
          <div class="feature text-center">
            <UIcon
              name="mdi-account-group"
              class="mx-auto mb-4 text-teal-500"
              size="48"
            />
            <h3 class="text-2xl font-semibold mb-2">Community Engagement</h3>
            <p>
              Tools for community involvement and environmental impact tracking.
            </p>
          </div>
        </div>
      </div>
    </section>
  </ClientOnly>

  <section id="key-features" class="key-features py-20 bg-white">
    <div class="container mx-auto px-4">
      <h2 class="text-4xl font-bold text-center mb-8">Key Features</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div class="feature text-center">
          <UIcon
            name="mdi-account-circle"
            class="mx-auto mb-4 text-teal-500"
            size="48"
          />
          <h3 class="text-2xl font-semibold mb-2">User Dashboard</h3>
          <p>
            Quick Access: Displays rewards, badges, and environmental stats.
          </p>
          <p>Leaderboard: User’s ranking based on recycled items.</p>
        </div>
        <div class="feature text-center">
          <UIcon
            name="mdi-map-marker"
            class="mx-auto mb-4 text-teal-500"
            size="48"
          />
          <h3 class="text-2xl font-semibold mb-2">Junk Shop Finder</h3>
          <p>
            Map View: Displays junk shops with accepted materials and shop
            details.
          </p>
          <p>
            Shop Profiles: Shop details, including accepted items, pricing, and
            reviews.
          </p>
        </div>
        <div class="feature text-center">
          <UIcon
            name="mdi-book-open"
            class="mx-auto mb-4 text-teal-500"
            size="48"
          />
          <h3 class="text-2xl font-semibold mb-2">Educational Resources</h3>
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
        <div class="feature text-center">
          <UIcon name="mdi-bell" class="mx-auto mb-4 text-teal-500" size="48" />
          <h3 class="text-2xl font-semibold mb-2">Push Notifications</h3>
          <p>Tips and Updates: Reminders and eco-friendly guidelines.</p>
        </div>
      </div>
    </div>
  </section>

  <section id="how-it-works" class="how-it-works py-20 bg-gray-100">
    <div class="container mx-auto px-4">
      <h2 class="text-4xl font-bold text-center mb-8">How It Works</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
        <div class="step">
          <UIcon
            name="mdi-account-plus"
            class="mx-auto mb-4 text-teal-500 animate-bounce"
            size="48"
          />
          <h3 class="text-2xl font-semibold mb-2">Step 1</h3>
          <p>Sign up and log in to access personalized features.</p>
        </div>
        <div class="step">
          <UIcon
            name="mdi-recycle"
            class="mx-auto mb-4 text-teal-500 animate-spin"
            size="48"
          />
          <h3 class="text-2xl font-semibold mb-2">Step 2</h3>
          <p>
            Report waste issues, track activities, and find recycling options.
          </p>
        </div>
        <div class="step">
          <UIcon
            name="mdi-trophy"
            class="mx-auto mb-4 text-teal-500 animate-pulse"
            size="48"
          />
          <h3 class="text-2xl font-semibold mb-2">Step 3</h3>
          <p>Earn rewards, badges, and monitor your environmental impact.</p>
        </div>
      </div>
    </div>
  </section>

  <section id="community-impact" class="community-impact py-20 bg-white">
    <div class="container mx-auto px-4">
      <h2 class="text-4xl font-bold text-center mb-8">Community Impact</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
        <div class="stat">
          <h3 class="text-2xl font-semibold mb-2">Waste Collected</h3>
          <p
            class="text-4xl font-bold text-teal-500"
            ref="wasteCollectedElement"
          >
            {{ wasteCollected }} Tons
          </p>
        </div>
        <div class="stat">
          <h3 class="text-2xl font-semibold mb-2">Recycling Rate</h3>
          <p
            class="text-4xl font-bold text-teal-500"
            ref="recyclingRateElement"
          >
            {{ recyclingRate }}%
          </p>
        </div>
        <div class="stat">
          <h3 class="text-2xl font-semibold mb-2">Community Participation</h3>
          <p
            class="text-4xl font-bold text-teal-500"
            ref="communityParticipationElement"
          >
            {{ communityParticipation }} Members
          </p>
        </div>
      </div>
      <div class="testimonials mt-12">
        <h3 class="text-3xl font-semibold text-center mb-8">Success Stories</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div class="testimonial">
            <p class="italic">
              "CleanSnap has transformed our city's waste management. The
              community is more engaged than ever!"
            </p>
            <p class="mt-4 font-bold">- City Official</p>
          </div>
          <div class="testimonial">
            <p class="italic">
              "Thanks to CleanSnap, I've been able to track my recycling efforts
              and make a real impact."
            </p>
            <p class="mt-4 font-bold">- Happy User</p>
          </div>
        </div>
      </div>
      <div class="leaderboard-preview mt-12">
        <h3 class="text-3xl font-semibold text-center mb-8">
          Top Contributors
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
          <div class="contributor">
            <p class="text-2xl font-bold">User123</p>
            <p class="text-teal-500">500 Points</p>
          </div>
          <div class="contributor">
            <p class="text-2xl font-bold">EcoWarrior</p>
            <p class="text-teal-500">450 Points</p>
          </div>
          <div class="contributor">
            <p class="text-2xl font-bold">GreenThumb</p>
            <p class="text-teal-500">400 Points</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section
    id="join-movement"
    class="join-movement py-20 bg-teal-500 text-white"
  >
    <div class="container mx-auto px-4 text-center">
      <h2 class="text-4xl font-bold mb-8">Join the Movement</h2>
      <p class="text-xl mb-8">
        Become part of the CleanSnap community and make a positive impact on the
        environment.
      </p>
      <UButton
        label="Get Started"
        @click="router.push('/auth/register')"
        color="white"
        variant="outline"
        size="xl"
      />
      <div class="social-media mt-8">
        <h3 class="text-2xl font-semibold mb-4">Follow Us</h3>
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
