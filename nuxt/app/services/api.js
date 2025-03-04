import { useRuntimeConfig } from '#app'

class ApiService {
  constructor() {
    const config = useRuntimeConfig()
    this.baseUrl = config.public.apiBaseUrl || '/api'
    this.headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    }
  }

  // Helper methods for different HTTP requests
  async get(endpoint, params = {}) {
    const queryString = new URLSearchParams(params).toString()
    const url = `${this.baseUrl}/${endpoint}${queryString ? `?${queryString}` : ''}`

    return await $fetch(url, {
      method: 'GET',
      headers: this.headers,
      credentials: 'include'
    })
  }

  async post(endpoint, data = {}) {
    const url = `${this.baseUrl}/${endpoint}`

    return await $fetch(url, {
      method: 'POST',
      body: data,
      headers: this.headers,
      credentials: 'include'
    })
  }

  async put(endpoint, data = {}) {
    const url = `${this.baseUrl}/${endpoint}`

    return await $fetch(url, {
      method: 'PUT',
      body: data,
      headers: this.headers,
      credentials: 'include'
    })
  }

  async delete(endpoint) {
    const url = `${this.baseUrl}/${endpoint}`

    return await $fetch(url, {
      method: 'DELETE',
      headers: this.headers,
      credentials: 'include'
    })
  }

  // Authentication endpoints
  auth = {
    login: (credentials) => this.post('auth/login', credentials),
    register: (userData) => this.post('auth/register', userData),
    logout: () => this.post('auth/logout'),
    user: () => this.get('user')
  }

  // Services endpoints
  services = {
    getAll: (params) => this.get('services', params),
    getById: (id) => this.get(`services/${id}`),
    create: (serviceData) => this.post('services', serviceData),
    update: (id, serviceData) => this.put(`services/${id}`, serviceData),
    delete: (id) => this.delete(`services/${id}`)
  }

  // Bookings endpoints
  bookings = {
    getAll: (params) => this.get('bookings', params),
    getById: (id) => this.get(`bookings/${id}`),
    create: (bookingData) => this.post('bookings', bookingData),
    update: (id, bookingData) => this.put(`bookings/${id}`, bookingData),
    cancel: (id) => this.put(`bookings/${id}/cancel`),
    delete: (id) => this.delete(`bookings/${id}`)
  }

  // Add more resource endpoints as needed
}

// Create and export a singleton instance
export const api = new ApiService()
