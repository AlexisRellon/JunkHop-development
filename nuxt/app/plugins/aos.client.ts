// AOS (Animation on Scroll) plugin initialization
import AOS from 'aos'
import 'aos/dist/aos.css'

export default defineNuxtPlugin((nuxtApp) => {
  // Wait for the app to be mounted before initializing AOS
  nuxtApp.hook('app:mounted', () => {
    AOS.init({
      // Global settings
      duration: 800, // values from 0 to 3000, with step 50ms
      easing: 'ease', // default easing for AOS animations
      once: true, // whether animation should happen only once - while scrolling down
      anchorPlacement: 'top-bottom', // defines which position of the element regarding to window should trigger the animation
    })
  })

  // Re-calculate AOS on window resize and orientation change
  if (process.client) {
    window.addEventListener('resize', () => {
      AOS.refresh()
    })
  }
})