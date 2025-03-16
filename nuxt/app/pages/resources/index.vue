<script lang="ts" setup>
import { ref, computed, onMounted } from 'vue';

interface VideoResource {
  id: number;
  youtubeId: string;
  title: string;
  category: string;
  duration: string;
  views: string;
  thumbnail?: string;
}

// Enhanced video data with additional fields for better UX
const videos = ref<VideoResource[]>([
  { 
    id: 1, 
    youtubeId: "cNPEH0GOhRw", 
    title: "How to Recycle Plastic Bottles", 
    category: "Plastic",
    duration: "4:35",
    views: "134K"
  },
  {
    id: 2,
    youtubeId: "6jQ7y_qQYUA",
    title: "Recycling 101: A Beginner's Guide",
    category: "Basics",
    duration: "7:21",
    views: "256K"
  },
  {
    id: 3,
    youtubeId: "s4LZwCDaoQM",
    title: "The Recycling Process: From Curbside to New Product",
    category: "Process",
    duration: "12:05",
    views: "89K"
  },
  {
    id: 4,
    youtubeId: "iBGZtNJAt-M",
    title: "Recycling Electronics: What You Need to Know",
    category: "Electronics",
    duration: "8:42",
    views: "105K"
  },
  {
    id: 5,
    youtubeId: "xpAnLXc_bIU",
    title: "How to Recycle Paper and Cardboard",
    category: "Paper",
    duration: "5:18",
    views: "72K"
  },
  {
    id: 6,
    youtubeId: "jsp7mgYv3aI",
    title: "Recycling Glass: A Step-by-Step Guide",
    category: "Glass",
    duration: "6:32",
    views: "64K"
  },
  {
    id: 7,
    youtubeId: "IYi7mKL7I5k",
    title: "The Importance of Recycling: Why It Matters",
    category: "Awareness",
    duration: "9:47",
    views: "325K"
  },
]);

// Filter options based on video categories
const categories = ref([
  { label: 'All', value: '' },
  { label: 'Basics', value: 'Basics' },
  { label: 'Plastic', value: 'Plastic' },
  { label: 'Paper', value: 'Paper' },
  { label: 'Glass', value: 'Glass' },
  { label: 'Electronics', value: 'Electronics' },
  { label: 'Process', value: 'Process' },
  { label: 'Awareness', value: 'Awareness' },
]);

const selectedCategory = ref('');
const searchQuery = ref('');
const isLoading = ref(true);
const selectedVideo = ref<VideoResource | null>(null);
const showVideoModal = ref(false);

// Calculate thumbnails from YouTube IDs
onMounted(() => {
  // Simulate loading delay
  setTimeout(() => {
    videos.value = videos.value.map(video => {
      return {
        ...video,
        thumbnail: `https://img.youtube.com/vi/${video.youtubeId}/mqdefault.jpg`
      };
    });
    isLoading.value = false;
  }, 800);
});

// Filter videos based on search and category
const filteredVideos = computed(() => {
  return videos.value.filter(video => {
    const matchesSearch = video.title.toLowerCase().includes(searchQuery.value.toLowerCase());
    const matchesCategory = selectedCategory.value ? video.category === selectedCategory.value : true;
    return matchesSearch && matchesCategory;
  });
});

// Handle video selection
const selectVideo = (video: VideoResource) => {
  selectedVideo.value = video;
  showVideoModal.value = true;
};

// Reset filters
const resetFilters = () => {
  searchQuery.value = '';
  selectedCategory.value = '';
};

// View on YouTube directly
const openYouTube = (youtubeId: string) => {
  window.open(`https://youtube.com/watch?v=${youtubeId}`, '_blank');
};

// Track resource clicks for analytics (placeholder)
const trackResourceClick = (resourceId: number) => {
  console.log(`Resource ${resourceId} clicked`);
  // Here you would normally send analytics data
};

// Use section ID for scroll navigation
const scrollToSection = (id: string) => {
  const element = document.getElementById(id);
  if (element) {
    element.scrollIntoView({ behavior: 'smooth' });
  }
};
</script>

<template>
  <!-- Hero Section with Animated Background -->
  <div
    class="relative h-[40vh] flex items-center justify-center overflow-hidden"
  >
    <!-- Background video pattern with overlay -->
    <div class="absolute inset-0 overflow-hidden">
      <div class="absolute inset-0 bg-teal-900/90 dark:bg-gray-900/95"></div>
      <!-- Animated pattern background -->
      <div class="absolute inset-0 opacity-10">
        <div 
          v-for="i in 6" 
          :key="i"
          class="absolute rounded-full mix-blend-screen animate-float"
          :style="`
            width: ${30 + (i * 15)}px; 
            height: ${30 + (i * 15)}px; 
            left: ${(i * 15) + 5}%; 
            top: ${(i * 10)}%; 
            animation-delay: ${i * 0.5}s;
            opacity: 0.${3 + i};
            background-color: ${['#14b8a6', '#10b981', '#22c55e', '#3b82f6'][i % 4]};
          `"
        ></div>
      </div>
    </div>

    <div class="relative z-10 text-center px-4 max-w-3xl mx-auto">
      <UBadge
        color="teal"
        variant="solid"
        class="mb-4"
      >
        <UIcon name="i-heroicons-academic-cap" class="mr-1" />
        <span>Learning Center</span>
      </UBadge>
      
      <h1 class="text-4xl md:text-6xl font-extrabold text-white mb-4 drop-shadow-lg">
        Educational <span class="bg-clip-text text-transparent bg-gradient-to-r from-teal-400 to-emerald-500">Resources</span>
      </h1>
      
      <p class="text-lg text-gray-200 mb-8 max-w-lg mx-auto leading-relaxed">
        Expand your knowledge with our curated collection of recycling and sustainability videos
      </p>

      <div class="flex flex-wrap justify-center gap-4">
        <UButton
          icon="i-heroicons-arrow-down"
          color="white"
          variant="solid"
          @click="scrollToSection('browse-videos')"
          class="hover:shadow-md transition-all duration-300"
        >
          Browse Videos
        </UButton>

        <UButton
          icon="i-heroicons-bookmark"
          color="gray"
          variant="ghost"
          class="text-white border-white/30 hover:bg-white/10 transition-all duration-300"
        >
          Save for Later
        </UButton>
      </div>
    </div>
  </div>

  <!-- Search and Filter Bar -->
  <div id="browse-videos" class="bg-white dark:bg-gray-900 py-6 border-b border-gray-200 dark:border-gray-800 sticky top-0 z-20 shadow-sm dark:shadow-gray-800/20">
    <div class="container mx-auto px-4 max-w-6xl">
      <div class="flex flex-col md:flex-row gap-4 items-center">
        <!-- Search Bar -->
        <div class="w-full relative">
          <UInput
            v-model="searchQuery"
            icon="i-heroicons-magnifying-glass-20-solid"
            color="gray"
            size="lg"
            placeholder="Search for recycling tutorials..."
            leading
            class="dark:bg-gray-800"
          >
            <template #trailing v-if="searchQuery">
              <UButton
                color="gray"
                variant="ghost"
                icon="i-heroicons-x-mark"
                @click="searchQuery = ''"
                class="-my-1"
              />
            </template>
          </UInput>
        </div>

        <!-- Category Filter -->
        <div class="w-full md:w-auto relative">
          <USelectMenu
            v-model="selectedCategory"
            :options="categories"
            placeholder="Filter by category"
            color="gray"
            size="lg"
            class="w-full md:w-60 dark:bg-gray-800"
            trailing
          >
            <template #trailing>
              <UButton
                v-if="selectedCategory"
                color="gray" 
                variant="ghost"
                icon="i-heroicons-x-mark"
                @click="selectedCategory = ''"
                class="-my-1"
              />
            </template>
          </USelectMenu>
        </div>
      </div>
    </div>
  </div>

  <!-- Video Gallery Section -->
  <section class="py-8 bg-gray-50 dark:bg-gray-800">
    <div class="container mx-auto px-4 max-w-6xl">
      <!-- Filters and Results Summary -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
            {{ filteredVideos.length }} 
            <span class="text-teal-600 dark:text-teal-400">
              {{ selectedCategory ? selectedCategory : 'Educational' }}
            </span> 
            Resources
          </h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Explore our collection of educational videos on recycling and sustainability
          </p>
        </div>

        <UButton 
          v-if="selectedCategory || searchQuery"
          color="gray" 
          variant="soft" 
          size="sm"
          @click="resetFilters"
          icon="i-heroicons-arrow-path"
          class="hover:bg-gray-200 dark:hover:bg-gray-700"
        >
          Reset Filters
        </UButton>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <UCard v-for="i in 6" :key="i" class="overflow-hidden dark:bg-gray-800 dark:border-gray-700">
          <template #header>
            <div class="h-48 bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
          </template>
          <div class="space-y-3">
            <USkeleton class="h-6 w-3/4" />
            <USkeleton class="h-4 w-1/2" />
          </div>
          <template #footer>
            <div class="flex justify-between">
              <USkeleton class="h-8 w-24" />
              <USkeleton class="h-8 w-24" />
            </div>
          </template>
        </UCard>
      </div>

      <!-- No Results -->
      <div 
        v-else-if="filteredVideos.length === 0" 
        class="py-16 text-center"
      >
        <UIcon name="i-heroicons-video-camera-slash" class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500 mb-4" />
        <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">No videos found</h3>
        <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto mb-6">
          We couldn't find any resources matching your search criteria.
        </p>
        <UButton color="teal" @click="resetFilters" class="hover:shadow-md transition-shadow duration-300">
          Reset filters
        </UButton>
      </div>

      <!-- Video Grid -->
      <div 
        v-else
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
      >
        <UCard
          v-for="video in filteredVideos"
          :key="video.id"
          class="overflow-hidden border hover:border-teal-600 transition-all duration-300 group dark:bg-gray-800 dark:border-gray-700"
          @click="selectVideo(video); trackResourceClick(video.id)"
        >
          <!-- Video Thumbnail -->
          <template #header>
            <div class="relative overflow-hidden">
              <img 
                :src="video.thumbnail" 
                :alt="video.title" 
                class="w-full h-48 object-cover transition-transform duration-500 group-hover:scale-105 dark:brightness-90"
              />
              <!-- Play Button Overlay -->
              <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-black/50">
                <UButton 
                  color="white" 
                  variant="solid" 
                  icon="i-heroicons-play" 
                  aria-label="Play video"
                  class="transform hover:scale-110 transition-transform duration-300"
                />
              </div>
              <!-- Duration Badge -->
              <UBadge
                color="black"
                variant="solid"
                class="absolute bottom-2 right-2 bg-black/70"
              >
                {{ video.duration }}
              </UBadge>
            </div>
          </template>

          <!-- Video Info -->
          <div class="p-4">
            <UBadge
              :color="video.category === 'Awareness' ? 'green' : 
                    video.category === 'Basics' ? 'blue' : 
                    video.category === 'Process' ? 'purple' : 'teal'"
              variant="subtle"
              size="sm"
              class="mb-2 dark:bg-opacity-20"
            >
              {{ video.category }}
            </UBadge>
            
            <h3 class="text-lg font-semibold mb-2 group-hover:text-teal-600 dark:text-gray-100 dark:group-hover:text-teal-400 transition-colors">
              {{ video.title }}
            </h3>
            
            <p class="text-gray-500 dark:text-gray-400 text-sm flex items-center">
              <UIcon name="i-heroicons-eye" class="mr-1" />
              {{ video.views }} views
            </p>
          </div>

          <template #footer>
            <div class="flex justify-between items-center">
              <UButton
                color="gray"
                variant="ghost"
                icon="i-heroicons-bookmark"
                size="sm"
                @click.stop
                class="hover:bg-gray-100 dark:hover:bg-gray-700"
              >
                Save
              </UButton>

              <UButton
                color="teal"
                variant="soft"
                icon="i-heroicons-arrow-top-right-on-square"
                size="sm"
                @click.stop="openYouTube(video.youtubeId)"
                class="hover:bg-teal-100 dark:hover:bg-teal-800"
              >
                YouTube
              </UButton>
            </div>
          </template>
        </UCard>
      </div>
    </div>
  </section>

  <!-- Additional Resources Section -->
  <section class="py-16 bg-white dark:bg-gray-900">
    <div class="container mx-auto px-4 max-w-6xl">
      <h2 class="text-3xl font-bold mb-8 text-center text-gray-800 dark:text-white">
        Additional Learning Resources
      </h2>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- PDF Guides -->
        <UCard class="border-l-4 border-blue-500 hover:shadow-lg transition-all duration-300 dark:bg-gray-800 dark:border-l-blue-600 dark:border-t-gray-700 dark:border-r-gray-700 dark:border-b-gray-700">
          <template #header>
            <div class="p-4 bg-blue-50 dark:bg-blue-900/30 flex items-center">
              <UIcon name="i-heroicons-document-text" class="text-blue-500 dark:text-blue-400" size="24" />
              <h3 class="text-xl font-semibold ml-2 dark:text-white">PDF Guides</h3>
            </div>
          </template>
          
          <div class="p-4">
            <ul class="space-y-3">
              <li v-for="i in 3" :key="i" class="flex items-center gap-2">
                <UIcon name="i-heroicons-document" class="text-blue-500 dark:text-blue-400" />
                <span class="dark:text-gray-300">Recycling Guide {{ i }}</span>
                <UBadge color="blue" variant="soft" size="xs" class="dark:bg-blue-900/50 dark:text-blue-300">PDF</UBadge>
              </li>
            </ul>
          </div>
          
          <template #footer>
            <UButton 
              color="blue" 
              variant="ghost" 
              block 
              icon="i-heroicons-arrow-down-tray"
              class="hover:bg-blue-50 dark:hover:bg-blue-900/30 dark:text-blue-300"
            >
              Download All Guides
            </UButton>
          </template>
        </UCard>

        <!-- Infographics -->
        <UCard class="border-l-4 border-amber-500 hover:shadow-lg transition-all duration-300 dark:bg-gray-800 dark:border-l-amber-600 dark:border-t-gray-700 dark:border-r-gray-700 dark:border-b-gray-700">
          <template #header>
            <div class="p-4 bg-amber-50 dark:bg-amber-900/30 flex items-center">
              <UIcon name="i-heroicons-chart-bar" class="text-amber-500 dark:text-amber-400" size="24" />
              <h3 class="text-xl font-semibold ml-2 dark:text-white">Infographics</h3>
            </div>
          </template>
          
          <div class="p-4">
            <ul class="space-y-3">
              <li v-for="i in 3" :key="i" class="flex items-center gap-2">
                <UIcon name="i-heroicons-photo" class="text-amber-500 dark:text-amber-400" />
                <span class="dark:text-gray-300">Recycling Statistics {{ new Date().getFullYear() }}</span>
              </li>
            </ul>
          </div>
          
          <template #footer>
            <UButton 
              color="amber" 
              variant="ghost" 
              block 
              icon="i-heroicons-eye"
              class="hover:bg-amber-50 dark:hover:bg-amber-900/30 dark:text-amber-300"
            >
              View Gallery
            </UButton>
          </template>
        </UCard>

        <!-- External Links -->
        <UCard class="border-l-4 border-purple-500 hover:shadow-lg transition-all duration-300 dark:bg-gray-800 dark:border-l-purple-600 dark:border-t-gray-700 dark:border-r-gray-700 dark:border-b-gray-700">
          <template #header>
            <div class="p-4 bg-purple-50 dark:bg-purple-900/30 flex items-center">
              <UIcon name="i-heroicons-link" class="text-purple-500 dark:text-purple-400" size="24" />
              <h3 class="text-xl font-semibold ml-2 dark:text-white">Useful Links</h3>
            </div>
          </template>
          
          <div class="p-4">
            <ul class="space-y-3">
              <li v-for="i in 3" :key="i" class="flex items-center gap-2">
                <UIcon name="i-heroicons-globe-alt" class="text-purple-500 dark:text-purple-400" />
                <span class="dark:text-gray-300">Environmental Protection Agency</span>
                <UIcon name="i-heroicons-arrow-top-right-on-square" class="text-gray-400" size="14" />
              </li>
            </ul>
          </div>
          
          <template #footer>
            <UButton 
              color="purple" 
              variant="ghost" 
              block 
              icon="i-heroicons-bookmark"
              class="hover:bg-purple-50 dark:hover:bg-purple-900/30 dark:text-purple-300"
            >
              Save All Links
            </UButton>
          </template>
        </UCard>
      </div>
    </div>
  </section>

  <!-- Call to Action -->
  <section class="py-16 bg-gradient-to-br from-teal-600 to-teal-700 dark:from-teal-800 dark:to-teal-900 relative overflow-hidden">
    <!-- Decorative elements -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-teal-500/10 rounded-full -translate-y-1/3 translate-x-1/3 blur-3xl"></div>
    <div class="absolute bottom-0 left-0 w-64 h-64 bg-teal-500/10 rounded-full translate-y-1/3 -translate-x-1/3 blur-3xl"></div>
    
    <div class="container mx-auto px-4 max-w-4xl text-center text-white relative z-10">
      <h2 class="text-3xl font-bold mb-4">Ready to make a difference?</h2>
      <p class="text-teal-100 mb-8 text-lg leading-relaxed">
        Put your newfound knowledge into action by finding recycling centers near you
      </p>
      <div class="flex flex-wrap justify-center gap-4">
        <UButton
          to="/finder"
          color="white"
          variant="solid"
          size="xl"
          class="font-medium text-teal-700 hover:shadow-lg transition-shadow duration-300"
        >
          <template #leading>
            <UIcon name="i-heroicons-map-pin" />
          </template>
          Find Junkshops Near Me
        </UButton>
        
        <UButton
          color="white"
          variant="outline"
          size="xl"
          class="font-medium hover:bg-white/10 transition-colors duration-300"
          to="/support"
        >
          <template #leading>
            <UIcon name="i-heroicons-chat-bubble-left-right" />
          </template>
          Contact Support
        </UButton>
      </div>
    </div>
  </section>

  <!-- Video Modal -->
  <UModal v-model="showVideoModal" :ui="{ width: 'sm:max-w-4xl' }">
    <UCard v-if="selectedVideo" class="dark:bg-gray-800 dark:border-gray-700">
      <template #header>
        <div class="flex justify-between items-center">
          <h3 class="text-xl font-bold dark:text-white">{{ selectedVideo.title }}</h3>
          <UBadge 
            :color="selectedVideo.category === 'Awareness' ? 'green' : 
                    selectedVideo.category === 'Basics' ? 'blue' : 
                    selectedVideo.category === 'Process' ? 'purple' : 'teal'"
            class="dark:bg-opacity-20 h-full"
          >
            {{ selectedVideo.category }}
          </UBadge>
        </div>
      </template>
      
      <div class="aspect-video bg-black">
        <iframe
          :src="`https://www.youtube.com/embed/${selectedVideo.youtubeId}?autoplay=1`"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen
          class="w-full h-96"
        ></iframe>
      </div>
      
      <div class="p-4">
        <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400 mb-4">
          <span class="flex items-center">
            <UIcon name="i-heroicons-clock" class="mr-1" />
            {{ selectedVideo.duration }}
          </span>
          <span class="flex items-center">
            <UIcon name="i-heroicons-eye" class="mr-1" />
            {{ selectedVideo.views }} views
          </span>
        </div>
        
        <p class="text-gray-600 dark:text-gray-300">
          This video provides educational content about recycling and sustainability. Watch and learn how you can make a positive impact on the environment.
        </p>
      </div>
      
      <template #footer>
        <div class="flex justify-between">
          <UButton
            color="gray"
            variant="ghost"
            @click="showVideoModal = false"
            class="hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            Close
          </UButton>
          
          <div class="flex gap-2">
            <UButton
              color="gray"
              variant="soft"
              icon="i-heroicons-bookmark"
              class="hover:bg-gray-200 dark:hover:bg-gray-700"
            >
              Save
            </UButton>
            
            <UButton
              color="teal"
              @click="openYouTube(selectedVideo.youtubeId)"
              icon="i-heroicons-arrow-top-right-on-square"
              class="hover:bg-teal-600 transition-colors duration-300"
            >
              Watch on YouTube
            </UButton>
          </div>
        </div>
      </template>
    </UCard>
  </UModal>
</template>

<style scoped>
@keyframes float {
  0% {
    transform: translateY(0) scale(1);
  }
  50% {
    transform: translateY(-20px) scale(1.1);
  }
  100% {
    transform: translateY(0) scale(1);
  }
}

.animate-float {
  animation: float 15s infinite ease-in-out;
}

/* Improve dark mode shadows */
.dark .hover\:shadow-lg:hover {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5), 0 4px 6px -2px rgba(0, 0, 0, 0.3);
}

/* Enhance card hover effects */
.group {
  transition: all 0.3s ease;
}

.group:hover {
  transform: translateY(-3px);
}
</style>
