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
  title: "JunkHop | Connecting Communities to Recycling Solutions",
  description: "Find nearby junk shops, manage recycling activities, and track your environmental impact with JunkHop",
  ogImage: {
    url: '/social-preview.jpg'
  }
});

/* Text Animation */
import { ref, onMounted, computed } from "vue";
import { useAuthStore } from "@/stores/auth";
// import { useSwiper } from 'swiper/vue';
import { useWindowSize } from '@vueuse/core';

const { width } = useWindowSize();
const isMobile = computed(() => width.value < 768);

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

// Add impact stats for immediate visual engagement
const impactStats = ref([
  { value: '500+', label: 'Tons Recycled', icon: 'i-heroicons-scale' },
  { value: '45%', label: 'Recycling Rate', icon: 'i-heroicons-arrow-trending-up' },
  { value: '1000+', label: 'Community Members', icon: 'i-heroicons-users' },
]);

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

// Hero section particle effect state
const heroParticlesEnabled = ref(true);

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

// Testimonial carousel
const testimonials = ref([
  {
    quote: "JunkHop has transformed our city's waste management. The community is more engaged than ever!",
    author: "City Official",
    role: "Environmental Department",
    avatar: "/avatars/city-official.jpg"
  },
  {
    quote: "Thanks to JunkHop, I've been able to track my recycling efforts and make a real impact.",
    author: "Happy User",
    role: "Community Member",
    avatar: "/avatars/happy-user.jpg"
  },
  {
    quote: "As a junk shop owner, this platform has connected me with so many more customers. Business is booming!",
    author: "Shop Owner",
    role: "Green Recyclers Inc.",
    avatar: "/avatars/shop-owner.jpg"
  }
]);

// Animated recycling items for hero background
const recyclingItems = ref([
  { icon: 'i-heroicons-document-text', color: 'text-blue-500', size: 'text-4xl', style: '' },
  { icon: 'i-heroicons-light-bulb', color: 'text-amber-500', size: 'text-2xl', style: '' },
  { icon: 'i-heroicons-cube', color: 'text-emerald-500', size: 'text-3xl', style: '' },
  { icon: 'i-heroicons-beaker', color: 'text-purple-500', size: 'text-4xl', style: '' },
  { icon: 'i-heroicons-computer-desktop', color: 'text-gray-500', size: 'text-3xl', style: '' },
  { icon: 'i-heroicons-device-phone-mobile', color: 'text-slate-600', size: 'text-2xl', style: '' },
  { icon: 'i-heroicons-shopping-bag', color: 'text-brown-500', size: 'text-3xl', style: '' },
  { icon: 'i-heroicons-bolt', color: 'text-yellow-500', size: 'text-2xl', style: '' }
]);

onMounted(() => {
  // Set up counting animations observer
  const observer = new IntersectionObserver(handleIntersection, {
    threshold: 0.5,
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
  
  // Animate recycling items in hero background
  animateRecyclingItems();
});

// Function to animate recycling items for a dynamic background effect
function animateRecyclingItems() {
  recyclingItems.value.forEach((item, index) => {
    // Set random initial positions
    const startX = Math.random() * 100;
    const startY = Math.random() * 50 + 20;
    const duration = 15000 + (Math.random() * 10000);
    const delay = Math.random() * 5000;
    
    item.style = `
      position: absolute; 
      left: ${startX}%; 
      top: ${startY}%; 
      animation: float ${duration}ms infinite ease-in-out ${delay}ms;
      opacity: 0.6;
    `;
  });
}
</script>

<template>
  <!-- Hero Section with Interactive Elements -->
  <section
    class="relative min-h-[calc(100vh-4rem)] flex items-center justify-center overflow-hidden bg-cover bg-center bg-no-repeat bg-gradient-to-b from-teal-900 via-teal-800 to-teal-900 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 hero-section"
    style="background-image: url('/path-to-your-background-image.jpg');"
  >
    <!-- Animated background elements -->
    <div v-if="heroParticlesEnabled" class="absolute inset-0 overflow-hidden pointer-events-none">
      <UIcon 
        v-for="(item, index) in recyclingItems" 
        :key="index"
        :name="item.icon"
        :class="[item.color, item.size]"
        :style="item.style"
      />
    </div>
    
    <!-- Semi-transparent overlay -->
    <div class="absolute inset-0 bg-gradient-to-r from-black/50 to-transparent"></div>
    
    <!-- Main Hero Content -->
    <div class="container relative z-10 px-4 mx-auto grid md:grid-cols-2 gap-12 items-center py-12">
      <!-- Left Column: Main CTA -->
      <div class="text-left" data-aos="fade-right" data-aos-duration="1000">
        <UBadge color="white" variant="solid" class="mb-4 dark:bg-white dark:text-teal-800" size="lg">
          <UIcon name="i-heroicons-sparkles" class="mr-1" />
          <span>Sustainable Future</span>
        </UBadge>
        
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white mb-6 leading-tight">
          Make <span class="text-teal-300 inline-block relative">recycling<span class="absolute -bottom-1 left-0 w-full h-1 bg-teal-300/50"></span></span> 
          <span class="block">part of your daily life</span>
        </h1>
        
        <p class="text-lg md:text-xl text-gray-200 mb-8 max-w-lg">
          Connect with local junk shops, track your environmental impact, and join a community committed to sustainability.
        </p>
        
        <div class="flex flex-wrap gap-4 mb-12">
          <UButton
            label="Get Started"
            @click="router.push('/auth/register')"
            color="white"
            variant="solid"
            size="xl"
            class="font-medium"
            icon="i-heroicons-arrow-right"
            trailing
          />
          <UButton
            label="How It Works"
            @click="scrollToDetails"
            color="white"
            variant="outline"
            size="xl"
            class="font-medium border-white/30 hover:border-white"
          />
        </div>
        
        <!-- Quick Stats -->
        <div class="hidden md:grid grid-cols-3 gap-4 mt-4">
          <div v-for="(stat, index) in impactStats" :key="index" class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/10">
            <UIcon :name="stat.icon" class="text-teal-300 mb-1" size="24" />
            <p class="text-2xl font-bold text-white">{{ stat.value }}</p>
            <p class="text-sm text-gray-300">{{ stat.label }}</p>
          </div>
        </div>
      </div>
      
      <!-- Right Column: Image/Visual -->
      <div class="relative hidden md:block" data-aos="fade-left" data-aos-duration="1000">
        <div class="relative">
          <img 
            src="/JunkHop-icon.png" 
            alt="Person recycling materials at a junk shop" 
            class="object-cover w-[35rem] relative left-16"
          />
          
          <!-- Floating UI Elements -->
          <div class="absolute -bottom-6 -left-6 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-xl flex items-center gap-3 animate-pulse">
            <UIcon name="i-heroicons-check-circle" class="text-teal-500" size="24" />
            <span class="font-medium">Verified Shops</span>
          </div>
          
          <div class="absolute -top-6 -right-6 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-xl flex items-center gap-3 animate-pulse delay-300">
            <UIcon name="i-heroicons-map-pin" size="24" />
            <span class="font-medium">Nearby Locations</span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Scroll indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
      <UButton
        icon="i-heroicons-chevron-down"
        color="white" 
        variant="ghost"
        @click="scrollToDetails"
        aria-label="Scroll down"
      />
    </div>
  </section>

  <!-- About Section with Cards -->
  <section id="details-section" class="py-24 bg-gray-50 dark:bg-gray-900 relative overflow-hidden">
    <!-- Background decoration -->
    <div class="absolute top-0 left-0 w-64 h-64 bg-teal-100 dark:bg-teal-900/30 rounded-full -translate-y-1/2 -translate-x-1/3 blur-3xl opacity-70"></div>
    
    <div class="container px-4 mx-auto relative">
      <div class="max-w-3xl mx-auto text-center mb-16" data-aos="fade-up">
        <UBadge color="teal" class="mb-4">About JunkHop</UBadge>
        <h2 class="text-3xl md:text-4xl font-bold mb-6">Bridging Communities with Sustainable Solutions</h2>
        <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed">
          JunkHop connects residents with local junk shops, making recycling more accessible and organized while tracking your environmental impact.
        </p>
      </div>

      <!-- Feature Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <UCard 
          data-aos="fade-up" 
          data-aos-delay="100"
          class="hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-100 dark:border-gray-800"
        >
          <template #header>
            <div class="flex justify-center p-6 bg-teal-50 dark:bg-teal-900/30">
              <UIcon name="i-heroicons-map-pin" class="text-teal-500" size="48" />
            </div>
          </template>
          <div class="text-center p-6">
            <h3 class="text-xl font-semibold mb-3">Find Junk Shops</h3>
            <p class="text-gray-600 dark:text-gray-300">Discover verified junk shops in your vicinity with detailed information about accepted materials</p>
          </div>
          <template #footer>
            <UButton 
              color="teal" 
              variant="ghost" 
              block
              label="Explore Map"
              icon="i-heroicons-arrow-right"
              trailing
              to="/finder"
            />
          </template>
        </UCard>

        <UCard 
          data-aos="fade-up" 
          data-aos-delay="300"
          class="hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-100 dark:border-gray-800"
        >
          <template #header>
            <div class="flex justify-center p-6 bg-teal-50 dark:bg-teal-900/30">
              <UIcon name="i-heroicons-user-circle" class="text-teal-500" size="48" />
            </div>
          </template>
          <div class="text-center p-6">
            <h3 class="text-xl font-semibold mb-3">User Profiles</h3>
            <p class="text-gray-600 dark:text-gray-300">Manage your account, track your recycling history, and monitor your environmental impact</p>
          </div>
          <template #footer>
            <UButton 
              color="teal" 
              variant="ghost" 
              block
              label="Get Started"
              icon="i-heroicons-arrow-right"
              trailing
              @click="auth.logged ? router.push('/account') : router.push('/auth/register')"
            />
          </template>
        </UCard>

        <UCard 
          data-aos="fade-up" 
          data-aos-delay="500"
          class="hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-gray-100 dark:border-gray-800"
        >
          <template #header>
            <div class="flex justify-center p-6 bg-teal-50 dark:bg-teal-900/30">
              <UIcon name="i-heroicons-building-storefront" class="text-teal-500" size="48" />
            </div>
          </template>
          <div class="text-center p-6">
            <h3 class="text-xl font-semibold mb-3">Junk Shop Management</h3>
            <p class="text-gray-600 dark:text-gray-300">For shop owners to manage their business profile, accepted materials, and connect with customers</p>
          </div>
          <template #footer>
            <UButton 
              color="teal" 
              variant="ghost" 
              block
              label="Register Shop"
              icon="i-heroicons-arrow-right"
              trailing
              to="/dashboard"
            />
          </template>
        </UCard>
      </div>
      
      <!-- Mission Statement -->
      <div class="mt-20 max-w-3xl mx-auto text-center" data-aos="fade-up" data-aos-delay="300">
        <UIcon name="i-heroicons-globe-americas" class="mx-auto mb-6 text-teal-500" size="48" />
        <h3 class="text-2xl font-semibold mb-4">Our Mission</h3>
        <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed">
          At JunkHop, we're committed to making recycling accessible, convenient, and rewarding. 
          Our platform bridges the gap between community members and local junk shops, promoting sustainable waste management practices and environmental responsibility.
        </p>
      </div>
    </div>
  </section>

  <!-- Key Features Section with Interactive Cards -->
  <section id="key-features" class="py-24 bg-gradient-to-br from-white to-teal-50 dark:from-gray-900 dark:to-gray-800 relative overflow-hidden">
    <!-- Background decoration -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-teal-100 dark:bg-teal-900/30 rounded-full -translate-y-1/2 translate-x-1/3 blur-3xl opacity-70"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-teal-100 dark:bg-teal-900/30 rounded-full translate-y-1/3 -translate-x-1/3 blur-3xl opacity-70"></div>
    
    <div class="container px-4 mx-auto relative">
      <div class="max-w-3xl mx-auto text-center mb-16" data-aos="fade-up">
        <UBadge color="teal" class="mb-4">Powerful Features</UBadge>
        <h2 class="text-3xl md:text-4xl font-bold mb-6">Everything You Need for Effective Recycling</h2>
        <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed">
          Our platform provides all the tools you need to make recycling easier, more accessible, and more rewarding.
        </p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
        <!-- Main feature -->
        <UCard 
          data-aos="zoom-in"
          class="md:col-span-6 border border-teal-100 dark:border-teal-900/50 hover:shadow-xl transition-all duration-500 hover:-translate-y-2 group"
        >
          <div class="p-6 flex flex-col h-full">
            <div class="rounded-full bg-teal-100 dark:bg-teal-900/50 w-16 h-16 flex items-center justify-center mb-6 group-hover:bg-teal-200 dark:group-hover:bg-teal-800 transition-colors duration-300">
              <UIcon name="i-heroicons-map" class="text-teal-600 dark:text-teal-300" size="28" />
            </div>
            <h3 class="text-2xl font-semibold mb-3">Interactive Junk Shop Finder</h3>
            <p class="text-gray-600 dark:text-gray-300 mb-4 flex-grow">
              Find verified junk shops near you with our map-based interface. Filter by materials accepted, operating hours, and user ratings.
            </p>
            <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
              <span class="text-sm text-gray-500">Most used feature</span>
              <UButton color="teal" variant="ghost" icon="i-heroicons-arrow-right" aria-label="Learn more" />
            </div>
          </div>
        </UCard>
        
        <!-- Secondary features -->
        <div class="md:col-span-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
          <!-- Profile Management -->
          <UCard 
            data-aos="zoom-in" 
            data-aos-delay="100"
            class="border border-gray-100 dark:border-gray-800 hover:shadow-lg transition-all duration-300 hover:-translate-y-1 group"
          >
            <div class="p-6">
              <div class="rounded-full bg-blue-100 dark:bg-blue-900/30 w-12 h-12 flex items-center justify-center mb-4">
                <UIcon name="i-heroicons-user-circle" class="text-blue-600 dark:text-blue-400" size="24" />
              </div>
              <h3 class="text-xl font-semibold mb-2">Profile Management</h3>
              <p class="text-gray-600 dark:text-gray-300">
                Track your recycling history and environmental impact through your personalized dashboard.
              </p>
            </div>
          </UCard>
          
          <!-- Educational Resources -->
          <UCard 
            data-aos="zoom-in" 
            data-aos-delay="200"
            class="border border-gray-100 dark:border-gray-800 hover:shadow-lg transition-all duration-300 hover:-translate-y-1 group"
          >
            <div class="p-6">
              <div class="rounded-full bg-amber-100 dark:bg-amber-900/30 w-12 h-12 flex items-center justify-center mb-4">
                <UIcon name="i-heroicons-book-open" class="text-amber-600 dark:text-amber-400" size="24" />
              </div>
              <h3 class="text-xl font-semibold mb-2">Educational Content</h3>
              <p class="text-gray-600 dark:text-gray-300">
                Learn about recycling best practices and how to prepare different materials.
              </p>
            </div>
          </UCard>
          
          <!-- Notifications -->
          <UCard 
            data-aos="zoom-in" 
            data-aos-delay="300"
            class="border border-gray-100 dark:border-gray-800 hover:shadow-lg transition-all duration-300 hover:-translate-y-1 group"
          >
            <div class="p-6">
              <div class="rounded-full bg-purple-100 dark:bg-purple-900/30 w-12 h-12 flex items-center justify-center mb-4">
                <UIcon name="i-heroicons-bell-alert" class="text-purple-600 dark:text-purple-400" size="24" />
              </div>
              <h3 class="text-xl font-semibold mb-2">Smart Notifications</h3>
              <p class="text-gray-600 dark:text-gray-300">
                Get timely reminders and updates about recycling opportunities in your area.
              </p>
            </div>
          </UCard>
          
          <!-- Community Features -->
          <UCard 
            data-aos="zoom-in" 
            data-aos-delay="400"
            class="border border-gray-100 dark:border-gray-800 hover:shadow-lg transition-all duration-300 hover:-translate-y-1 group"
          >
            <div class="p-6">
              <div class="rounded-full bg-green-100 dark:bg-green-900/30 w-12 h-12 flex items-center justify-center mb-4">
                <UIcon name="i-heroicons-users" class="text-green-600 dark:text-green-400" size="24" />
              </div>
              <h3 class="text-xl font-semibold mb-2">Community Features</h3>
              <p class="text-gray-600 dark:text-gray-300">
                Participate in challenges, share achievements, and connect with like-minded people.
              </p>
            </div>
          </UCard>
        </div>
      </div>
    </div>
  </section>

  <!-- How It Works Section with Visual Process Flow -->
  <section id="how-it-works" class="py-24 bg-gray-50 dark:bg-gray-900 relative">
    <div class="container px-4 mx-auto">
      <div class="max-w-3xl mx-auto text-center mb-16" data-aos="fade-up">
        <UBadge color="teal" class="mb-4">Simple Process</UBadge>
        <h2 class="text-3xl md:text-4xl font-bold mb-6">How JunkHop Works</h2>
        <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed">
          Our platform is designed to make recycling simple and accessible for everyone
        </p>
      </div>
      
      <!-- Process Steps -->
      <div class="relative">
        <!-- Connection Line (visible on desktop) -->
        <div class="hidden md:block absolute top-[37%] left-0 right-0 h-1 bg-teal-200 dark:bg-teal-800 transform -translate-y-1/2 z-0"></div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-16 md:gap-8 relative z-10">
          <!-- Step 1 -->
          <div class="flex flex-col items-center text-center" data-aos="fade-right" data-aos-delay="100">
            <div class="w-20 h-20 rounded-full flex items-center justify-center bg-white dark:bg-gray-800 border-4 border-teal-400 dark:border-teal-600 mb-6 shadow-lg relative">
              <span class="absolute -top-2 -right-2 w-8 h-8 rounded-full bg-teal-500 text-white flex items-center justify-center text-sm font-bold">1</span>
              <UIcon name="i-heroicons-user-plus" class="text-teal-500 dark:text-teal-400" size="36" />
            </div>
            <h3 class="text-2xl font-semibold mb-4">Create Your Account</h3>
            <p class="text-gray-600 dark:text-gray-400">
              Sign up as a user or register your junk shop to get started with JunkHop
            </p>
            
            <UButton
              color="teal"
              variant="ghost"
              label="Register Now"
              class="mt-6"
              size="sm"
              to="/auth/register"
              icon="i-heroicons-arrow-right"
              trailing
            />
          </div>
          
          <!-- Step 2 -->
          <div class="flex flex-col items-center text-center" data-aos="fade-up" data-aos-delay="300">
            <div class="w-20 h-20 rounded-full flex items-center justify-center bg-white dark:bg-gray-800 border-4 border-teal-400 dark:border-teal-600 mb-6 shadow-lg relative">
              <span class="absolute -top-2 -right-2 w-8 h-8 rounded-full bg-teal-500 text-white flex items-center justify-center text-sm font-bold">2</span>
              <UIcon name="i-heroicons-map-pin" class="text-teal-500 dark:text-teal-400" size="36" />
            </div>
            <h3 class="text-2xl font-semibold mb-4">Find & Connect</h3>
            <p class="text-gray-600 dark:text-gray-400">
              Discover junk shops near you or list your shop for customers to find
            </p>
            
            <UButton
              color="teal"
              variant="ghost"
              label="Explore Map"
              class="mt-6"
              size="sm"
              icon="i-heroicons-arrow-right"
              trailing
            />
          </div>
          
          <!-- Step 3 -->
          <div class="flex flex-col items-center text-center" data-aos="fade-left" data-aos-delay="500">
            <div class="w-20 h-20 rounded-full flex items-center justify-center bg-white dark:bg-gray-800 border-4 border-teal-400 dark:border-teal-600 mb-6 shadow-lg relative">
              <span class="absolute -top-2 -right-2 w-8 h-8 rounded-full bg-teal-500 text-white flex items-center justify-center text-sm font-bold">3</span>
              <UIcon name="i-heroicons-chart-bar" class="text-teal-500 dark:text-teal-400" size="36" />
            </div>
            <h3 class="text-2xl font-semibold mb-4">Track Your Impact</h3>
            <p class="text-gray-600 dark:text-gray-400">
              Monitor your recycling activities and see your positive environmental impact
            </p>
            
            <UButton
              color="teal"
              variant="ghost"
              label="View Dashboard"
              class="mt-6"
              size="sm"
              icon="i-heroicons-arrow-right"
              trailing
            />
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Community Impact Section -->
  <section id="community-impact" class="py-24 bg-white dark:bg-gray-800 overflow-hidden relative">
    <!-- Background pattern -->
    <div class="absolute inset-0 opacity-10">
      <div class="absolute top-0 left-0 w-full h-full bg-repeat" style="background-image: url('data:image/svg+xml,%3Csvg width=\'20\' height=\'20\' viewBox=\'0 0 20 20\' fill=\'none\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Ccircle cx=\'10\' cy=\'10\' r=\'1\' fill=\'%2310B981\'/%3E%3C/svg%3E%0A');"></div>
    </div>
    
    <div class="container px-4 mx-auto relative">
      <div class="max-w-3xl mx-auto text-center mb-16" data-aos="fade-up">
        <UBadge color="teal" class="mb-4">Our Achievements</UBadge>
        <h2 class="text-3xl md:text-4xl font-bold mb-6">Community Impact</h2>
        <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed">
          Together, we're making a significant difference in waste management and environmental conservation
        </p>
      </div>
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
        <!-- Waste Collected -->
        <div class="bg-gradient-to-br from-teal-50 to-teal-100 dark:from-gray-700 dark:to-teal-900/30 p-8 rounded-2xl shadow-sm text-center transform transition-all duration-500 hover:scale-105" data-aos="fade-up" data-aos-delay="100">
          <UIcon name="i-heroicons-scale" class="mx-auto mb-6 text-teal-500" size="48" />
          <h3 class="text-2xl font-semibold mb-3">Waste Collected</h3>
          <div class="text-5xl font-extrabold text-teal-600 dark:text-teal-400 mb-2 flex items-center justify-center gap-2" ref="wasteCollectedElement">
            <span>{{ wasteCollected }}</span>
            <span class="text-2xl font-medium">Tons</span>
          </div>
          <p class="text-gray-600 dark:text-gray-300">And counting...</p>
        </div>
        
        <!-- Recycling Rate -->
        <div class="bg-gradient-to-br from-teal-50 to-teal-100 dark:from-gray-700 dark:to-teal-900/30 p-8 rounded-2xl shadow-sm text-center transform transition-all duration-500 hover:scale-105" data-aos="fade-up" data-aos-delay="300">
          <UIcon name="i-heroicons-arrow-trending-up" class="mx-auto mb-6 text-teal-500" size="48" />
          <h3 class="text-2xl font-semibold mb-3">Recycling Rate</h3>
          <div class="text-5xl font-extrabold text-teal-600 dark:text-teal-400 mb-2 flex items-center justify-center" ref="recyclingRateElement">
            <span>{{ recyclingRate }}</span>
            <span>%</span>
          </div>
          <p class="text-gray-600 dark:text-gray-300">Improvement since launch</p>
        </div>
        
        <!-- Community Participation -->
        <div class="bg-gradient-to-br from-teal-50 to-teal-100 dark:from-gray-700 dark:to-teal-900/30 p-8 rounded-2xl shadow-sm text-center transform transition-all duration-500 hover:scale-105" data-aos="fade-up" data-aos-delay="500">
          <UIcon name="i-heroicons-users" class="mx-auto mb-6 text-teal-500" size="48" />
          <h3 class="text-2xl font-semibold mb-3">Community Members</h3>
          <div class="text-5xl font-extrabold text-teal-600 dark:text-teal-400 mb-2 flex items-center justify-center gap-2" ref="communityParticipationElement">
            <span>{{ communityParticipation }}</span>
            <span class="text-2xl font-medium">Users</span>
          </div>
          <p class="text-gray-600 dark:text-gray-300">Active JunkHop users</p>
        </div>
      </div>
      
      <!-- Testimonials -->
      <div class="mt-20">
        <h3 class="text-2xl font-semibold text-center mb-10" data-aos="fade-up">What People Are Saying</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="(testimonial, index) in testimonials" :key="index" 
              class="bg-white dark:bg-gray-700 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-600 relative"
              :data-aos="index % 2 === 0 ? 'fade-right' : 'fade-left'"
              :data-aos-delay="index * 200">
              
            <div class="text-teal-500 mb-4">
              <UIcon name="i-heroicons-star" size="20" class="inline-block" v-for="i in 5" :key="i" />
            </div>
            
            <p class="italic text-gray-600 dark:text-gray-300 mb-6">
              "{{ testimonial.quote }}"
            </p>
            
            <div class="flex items-center">
              <UAvatar :src="testimonial.avatar || ''" :alt="testimonial.author" class="mr-4" />
              <div>
                <p class="font-semibold">{{ testimonial.author }}</p>
                <p class="text-sm text-gray-500">{{ testimonial.role }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Call to Action -->
  <section id="join-movement" class="py-20 bg-gradient-to-br from-teal-600 to-teal-700 dark:from-teal-800 dark:to-teal-900 text-white relative overflow-hidden">
    <!-- Background elements -->
    <div class="absolute top-0 right-0 -translate-y-1/3 translate-x-1/3">
      <UIcon name="i-heroicons-light-bulb" class="text-teal-400/20" size="320" />
    </div>
    <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3">
      <UIcon name="i-heroicons-document-text" class="text-teal-400/20" size="320" />
    </div>
    
    <div class="container px-4 mx-auto relative">
      <div class="max-w-4xl mx-auto text-center" data-aos="fade-up">
        <h2 class="text-4xl md:text-5xl font-bold mb-6">Join the Movement Today</h2>
        <p class="text-xl mb-10 text-teal-100">
          Be part of the solution. Together, we can make recycling easier, more effective, and create a cleaner planet for future generations.
        </p>
        
        <div class="flex flex-wrap justify-center gap-6 mb-12">
          <UButton
            label="Sign Up Now"
            to="/auth/register"
            color="white"
            variant="solid"
            size="xl"
            class="text-teal-700 font-medium"
            icon="i-heroicons-arrow-right"
            trailing
            data-aos="zoom-in"
            data-aos-delay="200"
          />
          <UButton
            label="Learn More"
            to="/about"
            color="white"
            variant="outline"
            size="xl"
            class="font-medium"
            data-aos="zoom-in"
            data-aos-delay="400"
          />
        </div>
        
        <!-- Social Media -->
        <div class="mt-12" data-aos="fade-up" data-aos-delay="600">
          <h3 class="text-2xl font-semibold mb-6">Follow Our Journey</h3>
          <div class="flex justify-center gap-6">
            <UButton
              icon="i-simple-icons-facebook"
              color="white"
              variant="ghost"
              class="rounded-full hover:bg-white/10"
              aria-label="Facebook"
            />
            <UButton
              icon="i-simple-icons-twitter"
              color="white"
              variant="ghost"
              class="rounded-full hover:bg-white/10"
              aria-label="Twitter"
            />
            <UButton
              icon="i-simple-icons-instagram"
              color="white"
              variant="ghost"
              class="rounded-full hover:bg-white/10"
              aria-label="Instagram"
            />
            <UButton
              icon="i-simple-icons-linkedin"
              color="white"
              variant="ghost"
              class="rounded-full hover:bg-white/10"
              aria-label="LinkedIn"
            />
          </div>
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

/* Floating animation for recycling icons */
@keyframes float {
  0% {
    transform: translateY(0px) rotate(0deg);
    opacity: 0.3;
  }
  50% {
    transform: translateY(-20px) rotate(10deg);
    opacity: 0.6;
  }
  100% {
    transform: translateY(0px) rotate(0deg);
    opacity: 0.3;
  }
}

/* Styles for cards on hover */
.feature-card {
  transition: all 0.3s ease;
}

.feature-card:hover {
  transform: translateY(-8px);
}
</style>
