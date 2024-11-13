// nuxt/app/plugins/leaflet.client.ts
import { defineNuxtPlugin } from '#app'
import L from 'leaflet'

export default defineNuxtPlugin(() => {
  return {
    provide: {
      L: L
    }
  }
})
