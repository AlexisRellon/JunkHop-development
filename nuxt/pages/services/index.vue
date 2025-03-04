<template>
  <div>
    <h1>All Services</h1>
    <div v-if="pending">Loading...</div>
    <div v-else-if="error">Error loading services: {{ error.message }}</div>
    <div v-else>
      <div v-for="service in services" :key="service.id">
        <!-- Service display logic -->
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { api } from '~/app/services/api'

const services = ref([])
const pending = ref(true)
const error = ref(null)

// Example of how to use the API service
async function fetchServices() {
  try {
    pending.value = true
    services.value = await api.services.getAll()
  } catch (err) {
    error.value = err
    console.error('Failed to fetch services:', err)
  } finally {
    pending.value = false
  }
}

// Fetch services when the component is mounted
onMounted(fetchServices)
</script>
