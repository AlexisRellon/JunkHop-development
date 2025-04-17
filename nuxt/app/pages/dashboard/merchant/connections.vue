<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Junkshop Connections</h1>
    
    <UCard class="mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200">Manage Your Connections</h2>
        <UButton
          to="/finder"
          color="teal"
          variant="soft"
          size="sm"
          icon="i-heroicons-map"
        >
          Find New Junkshops
        </UButton>
      </div>
      
      <p class="text-gray-600 dark:text-gray-400 mb-4">
        Connect with junkshops that collect the materials you're interested in. Managing your connections helps streamline your recycling business operations.
      </p>
    </UCard>

    <!-- Loading state -->
    <div v-if="loading" class="flex justify-center py-8">
      <UIcon name="i-heroicons-arrow-path" class="animate-spin text-teal-500 w-8 h-8" />
    </div>
    
    <!-- No connections state -->
    <UCard v-else-if="connections.length === 0" class="text-center py-8">
      <div class="p-4 mb-4 bg-blue-100 dark:bg-blue-900/30 rounded-full inline-flex">
        <UIcon name="i-heroicons-information-circle" class="text-blue-500 w-8 h-8" />
      </div>
      <h3 class="text-xl font-medium text-gray-700 dark:text-gray-300 mb-2">No Connections Yet</h3>
      <p class="text-gray-600 dark:text-gray-400 mb-4 max-w-md mx-auto">
        You haven't connected with any junkshops yet. Visit the finder to discover junkshops in your area.
      </p>
      <UButton
        to="/finder"
        color="teal"
        icon="i-heroicons-map"
      >
        Find Junkshops
      </UButton>
    </UCard>
    
    <!-- Connection list -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <UCard 
        v-for="junkshop in connections" 
        :key="junkshop.ulid"
        class="transition-all duration-300 hover:shadow-lg"
      >
        <div class="flex items-center gap-3 mb-3">
          <UAvatar
            :src="junkshop.logo ? $storage(junkshop.logo) : ''"
            :alt="junkshop.name"
            size="lg"
            :fallback="junkshop.name?.charAt(0) || 'J'"
            color="teal"
          />
          <div>
            <h3 class="font-medium text-gray-800 dark:text-gray-200">{{ junkshop.name }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ junkshop.address }}</p>
          </div>
        </div>
        
        <div class="mb-3 space-y-2 text-gray-600 dark:text-gray-400">
          <div class="flex items-center gap-2">
            <UIcon name="i-heroicons-phone" class="text-teal-500" />
            <span class="text-sm">{{ junkshop.contact }}</span>
          </div>
        </div>
        
        <div class="mt-4 flex flex-wrap gap-1">
          <UBadge 
            v-for="(item, index) in junkshop.items?.slice(0, 3)" 
            :key="index" 
            color="gray" 
            size="xs"
          >
            {{ item.name }}
          </UBadge>
          <UBadge v-if="junkshop.items?.length > 3" color="gray" size="xs">
            +{{ junkshop.items.length - 3 }} more
          </UBadge>
        </div>
        
        <template #footer>
          <div class="flex justify-between">
            <UButton
              :to="`/finder?junkshop=${junkshop.ulid}`"
              color="blue"
              variant="ghost"
              icon="i-heroicons-information-circle"
              size="sm"
            >
              View Details
            </UButton>
            
            <UButton
              @click="disconnectJunkshop(junkshop.ulid)"
              color="red"
              variant="ghost"
              icon="i-heroicons-x-mark"
              size="sm"
            >
              Remove Connection
            </UButton>
          </div>
        </template>
      </UCard>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const toast = useToast();
const loading = ref(true);
const connections = ref([]);

// Fetch junkshop connections
const fetchConnections = async () => {
  try {
    loading.value = true;
    const response = await $fetch('/merchant/connected-junkshops');
    connections.value = response || [];
  } catch (error) {
    console.error('Error fetching junkshop connections:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to load junkshop connections.',
      color: 'red'
    });
  } finally {
    loading.value = false;
  }
};

// Disconnect from a junkshop
const disconnectJunkshop = async (junkshopUlid) => {
  try {
    const response = await $fetch(`/merchant/connect/${junkshopUlid}`, {
      method: 'POST'
    });
    
    if (response.status === 'disconnected') {
      // Remove this junkshop from the list
      connections.value = connections.value.filter(shop => shop.ulid !== junkshopUlid);
      
      toast.add({
        title: 'Success',
        description: 'Disconnected from junkshop successfully.',
        color: 'green'
      });
    }
  } catch (error) {
    console.error('Error disconnecting from junkshop:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to disconnect from junkshop.',
      color: 'red'
    });
  }
};

// Fetch connections when component mounts
onMounted(() => {
  fetchConnections();
});

// Define the parent layout
definePageMeta({
  layout: 'dashboard'
});
</script>
