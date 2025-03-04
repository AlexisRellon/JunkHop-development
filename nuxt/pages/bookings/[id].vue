<template>
  <div>
    <h1>Booking Details</h1>
    <div v-if="pending">Loading...</div>
    <div v-else-if="error">Error: {{ error.message }}</div>
    <div v-else>
      <!-- Booking details display -->
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { api } from '~/app/services/api'

const route = useRoute()
const booking = ref(null)
const pending = ref(true)
const error = ref(null)

const bookingId = route.params.id

async function fetchBookingDetails() {
  try {
    pending.value = true
    booking.value = await api.bookings.getById(bookingId)
  } catch (err) {
    error.value = err
    console.error('Failed to fetch booking details:', err)
  } finally {
    pending.value = false
  }
}

onMounted(fetchBookingDetails)
</script>
