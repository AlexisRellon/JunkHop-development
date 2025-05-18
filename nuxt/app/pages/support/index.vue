<script lang="ts" setup>
import { ref } from 'vue';

// Add SEO metadata
useHead({
  htmlAttrs: {
    lang: 'en'
  }
});

useSeoMeta({
  title: 'Customer Support & Contact | JunkHop',
  description: 'Get help with your JunkHop account, send inquiries, or report issues with our platform',
  ogTitle: 'JunkHop Support Center',
  ogDescription: 'Contact our team for assistance with the JunkHop platform',
  ogImage: '/support-preview.jpg',
  twitterCard: 'summary',
  twitterTitle: 'JunkHop Support',
  twitterDescription: 'Get help with your JunkHop account or platform questions',
  keywords: 'JunkHop support, waste management help, recycling platform assistance, contact JunkHop'
});

/**
 * Reactive reference to the form data.
 * @type {Object}
 * @property {string} name - The name of the user.
 * @property {string} email - The email of the user.
 * @property {subject} subject - The subject of the inquiry.
 * @property {string} message - The message from the user.
 */
const form = ref({
  name: "",
  email: "",
  subject: "",
  message: ""
});

/**
 * Form validation state
 */
const isSubmitting = ref(false);
const formSubmitted = ref(false);
const formError = ref(false);

/**
 * FAQ data
 */
const faqs = ref([
  {
    question: "How do I find the nearest junkshop?",
    answer: "You can use our Junkshop Finder feature to locate recycling centers near you. Simply navigate to the Finder page and enter your location or allow location access."
  },
  {
    question: "What materials can I recycle?",
    answer: "Most junkshops accept paper, plastic, metal, and glass. Some specialized centers also accept electronics, batteries, and other hazardous materials. Check the shop details for materials accepted at each location."
  },
  {
    question: "How do I create an account?",
    answer: "Click the Sign Up button in the navigation menu and follow the registration process. You'll need to provide a valid email address and create a password."
  },
  {
    question: "Are there any fees for using CleanSnap?",
    answer: "No, CleanSnap is completely free for individual users. We offer premium features for businesses and junkshop owners at affordable subscription rates."
  }
]);

/**
 * Toggle FAQ visibility
 */
const openFaq = ref<number | null>(null);
const toggleFaq = (index: number) => {
  if (openFaq.value === index) {
    openFaq.value = null;
  } else {
    openFaq.value = index;
  }
};

/**
 * Contact methods
 */
const contactMethods = ref([
  {
    icon: 'i-heroicons-envelope',
    title: 'Email Us',
    contact: 'support@cleansnap.com',
    description: 'We usually respond within 24 hours'
  },
  {
    icon: 'i-heroicons-phone',
    title: 'Call Us',
    contact: '+63 (2) 123 4567',
    description: 'Mon-Fri 9AM to 6PM'
  },
  {
    icon: 'i-heroicons-chat-bubble-left-right',
    title: 'Live Chat',
    contact: 'Start a conversation',
    description: 'Available on business days'
  }
]);

/**
 * Handles the form submission.
 * Simulates form submission with loading states.
 */
const submitForm = async () => {
  if (!form.value.name || !form.value.email || !form.value.message) {
    return;
  }
  
  isSubmitting.value = true;
  
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1500));
    console.log("Form submitted:", form.value);
    
    // Reset form on success
    form.value = {
      name: "",
      email: "",
      subject: "",
      message: ""
    };
    
    formSubmitted.value = true;
    formError.value = false;
  } catch (error) {
    console.error("Error submitting form:", error);
    formError.value = true;
  } finally {
    isSubmitting.value = false;
  }
};

/**
 * Reset the form submission state to try again
 */
const resetForm = () => {
  formSubmitted.value = false;
  formError.value = false;
};
</script>

<template>
  <!-- Hero Section -->
  <div
    class="relative h-[25vh] flex items-center justify-center overflow-hidden"
  >
    <div
      class="absolute inset-0 bg-cover bg-center"
      style="
        background-image: url('/images/support-hero.jpg');
        background-size: cover;
      "
    ></div>
    <div class="absolute inset-0 bg-gradient-to-r from-teal-900/90 to-teal-800/80 dark:from-gray-900/95 dark:to-gray-800/90"></div>
    
    <div class="relative z-10 text-center px-4 max-w-xl mx-auto">
      <UBadge
        color="teal"
        variant="solid"
        class="mb-4"
      >
        <UIcon name="i-heroicons-lifebuoy" class="mr-1" />
        <span>We're Here To Help</span>
      </UBadge>
      
      <h1 class="text-3xl md:text-5xl font-extrabold text-white mb-2 drop-shadow-lg">
        Customer Support
      </h1>
      
      <p class="text-lg text-gray-200 leading-relaxed">
        Get help with any questions or concerns about our services
      </p>
    </div>
  </div>

  <div class="bg-gray-50 dark:bg-gray-900 min-h-[75vh] py-12">
    <div class="container mx-auto px-4 max-w-6xl">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Contact Info Cards -->
        <div class="lg:col-span-1">
          <h2 class="text-2xl font-semibold mb-6 text-gray-800 dark:text-white">
            Contact Information
          </h2>
          
          <div class="space-y-4">
            <UCard 
              v-for="(method, index) in contactMethods" 
              :key="index"
              class="transition-all duration-300 dark:bg-gray-800 dark:border-gray-700 hover:border-2 hover:border-teal-500 dark:hover:border-teal-400 transform hover:-translate-y-1"
            >
              <div class="flex items-start gap-4">
                <div class="bg-teal-100 dark:bg-teal-900/40 rounded-full p-3">
                  <UIcon :name="method.icon" class="text-teal-600 dark:text-teal-400" size="24" />
                </div>
                <div>
                  <h3 class="font-medium text-lg mb-1 dark:text-white">{{ method.title }}</h3>
                  <p class="text-teal-600 dark:text-teal-400 font-medium">{{ method.contact }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ method.description }}</p>
                </div>
              </div>
            </UCard>
          </div>
          
          <!-- Social Media Links -->
          <div class="mt-8">
            <h3 class="text-lg font-medium mb-4 text-gray-800 dark:text-white">Connect With Us</h3>
            <div class="flex gap-4">
              <UButton
                v-for="(platform, index) in ['facebook', 'twitter', 'instagram', 'linkedin']"
                :key="index"
                :icon="`i-simple-icons-${platform}`"
                color="gray" 
                variant="ghost"
                class="hover:text-teal-600 dark:hover:text-teal-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors duration-300 hover:ring-1 hover:ring-teal-500 dark:hover:ring-teal-400"
                aria-label="Social media link"
              />
            </div>
          </div>
        </div>

        <!-- Contact Form -->
        <div class="lg:col-span-2">
          <h2 class="text-2xl font-semibold mb-6 text-gray-800 dark:text-white">
            Send Us a Message
          </h2>
          
          <!-- Success Message -->
          <UCard v-if="formSubmitted" color="green" class="mb-6 dark:border-green-600">
            <div class="flex items-center gap-3">
              <UIcon name="i-heroicons-check-circle" class="text-green-500" size="24" />
              <div>
                <h3 class="font-medium dark:text-white">Thank you for your message!</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300">We've received your inquiry and will respond shortly.</p>
              </div>
            </div>
            <template #footer>
              <UButton 
                color="gray" 
                variant="ghost" 
                @click="resetForm"
                class="hover:border hover:border-gray-300 dark:hover:border-gray-600"
              >
                Send another message
              </UButton>
            </template>
          </UCard>
          
          <!-- Error Message -->
          <UCard v-else-if="formError" color="red" class="mb-6 dark:border-red-600">
            <div class="flex items-center gap-3">
              <UIcon name="i-heroicons-exclamation-triangle" class="text-red-500" size="24" />
              <div>
                <h3 class="font-medium dark:text-white">Something went wrong</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300">We couldn't send your message. Please try again later.</p>
              </div>
            </div>
            <template #footer>
              <UButton 
                color="gray" 
                variant="ghost" 
                @click="resetForm"
                class="hover:border hover:border-gray-300 dark:hover:border-gray-600"
              >
                Try again
              </UButton>
            </template>
          </UCard>
          
          <!-- Contact Form -->
          <UCard v-else class="border dark:bg-gray-800 dark:border-gray-700">
            <UForm @submit.prevent="submitForm" :state="form">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Name Field -->
                <UFormGroup label="Full Name" name="name" required class="dark:text-gray-300">
                  <UInput
                    v-model="form.name"
                    placeholder="John Doe"
                    class="dark:bg-gray-700 pl-10 relative"
                  >
                    <template #leading>
                      <UIcon name="i-heroicons-user" class="text-gray-500 dark:text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    </template>
                  </UInput>
                </UFormGroup>
                
                <!-- Email Field -->
                <UFormGroup label="Email Address" name="email" required class="dark:text-gray-300">
                  <UInput
                    v-model="form.email"
                    type="email"
                    placeholder="john.doe@example.com"
                    class="dark:bg-gray-700 pl-10 relative"
                  >
                    <template #leading>
                      <UIcon name="i-heroicons-envelope" class="text-gray-500 dark:text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    </template>
                  </UInput>
                </UFormGroup>
              </div>
              
              <!-- Subject Field -->
              <UFormGroup label="Subject" name="subject" class="mb-6 dark:text-gray-300">
                <UInput
                  v-model="form.subject"
                  placeholder="What is your inquiry about?"
                  class="dark:bg-gray-700 pl-10 relative"
                >
                  <template #leading>
                    <UIcon name="i-heroicons-chat-bubble-bottom-center-text" class="text-gray-500 dark:text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                  </template>
                </UInput>
              </UFormGroup>
              
              <!-- Message Field -->
              <UFormGroup label="Message" name="message" required class="dark:text-gray-300">
                <UTextarea
                  v-model="form.message"
                  placeholder="Please provide details about your inquiry or issue..."
                  color="white"
                  variant="outline"
                  :rows="5"
                  :maxrows="5"
                  required
                  class="dark:bg-gray-900 focus:ring-2 focus:ring-teal-500 dark:focus:ring-teal-400"
                />
              </UFormGroup>
              
              <div class="flex items-center justify-between mt-8 flex-wrap gap-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  <UIcon name="i-heroicons-shield-check" class="inline-block mr-1 text-teal-500 dark:text-teal-400" />
                  Your information is secure and encrypted
                </p>
                
                <UButton
                  type="submit"
                  color="teal"
                  :loading="isSubmitting"
                  :disabled="isSubmitting"
                  size="lg"
                  class="hover:ring-2 hover:ring-teal-500 hover:ring-offset-2 dark:hover:ring-offset-gray-800 transition-all duration-300"
                >
                  <template #leading>
                    <UIcon name="i-heroicons-paper-airplane" />
                  </template>
                  {{ isSubmitting ? 'Sending...' : 'Send Message' }}
                </UButton>
              </div>
            </UForm>
          </UCard>
        </div>
      </div>
      
      <!-- FAQs Section -->
      <div class="mt-16">

        <!-- Still Need Help -->
        <div class="text-center mt-12">
          <p class="text-gray-600 dark:text-gray-300 mb-4">
            Still having issues or questions?
          </p>
          <UButton
            to="/finder"
            color="teal"
            variant="outline"
            icon="i-heroicons-map-pin"
            class="hover:border-teal-600 hover:border-2 dark:hover:border-teal-400 transition-all duration-300"
          >
            Visit a Local Junkshop
          </UButton>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Optional animations */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.fade-in {
  animation: fadeIn 0.5s ease-out forwards;
}

.fade-in-element {
  animation: fadeIn 0.3s ease-out forwards;
}

/* Dark mode card hover improvements */
.dark .hover\:shadow-md:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
}
</style>
