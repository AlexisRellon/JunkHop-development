<template>
  <div class="min-h-screen py-12 bg-gray-50 dark:bg-gray-900">
    <div class="container max-w-3xl px-4 mx-auto">
      <!-- Hero Section -->
      <div class="mb-12 text-center">
        <h1 class="mb-3 text-4xl font-extrabold tracking-tight md:text-5xl">
          <span class="text-gray-800 dark:text-white">Frequently Asked</span>
          <span class="text-teal-500"> Questions</span>
        </h1>
        <p class="mx-auto text-lg text-gray-600 dark:text-gray-400 max-w-lg">
          Find answers to common questions about using JunkHop
        </p>
      </div>

      <!-- FAQ Accordion using Nuxt UI -->
      <div class="overflow-hidden bg-white rounded-xl dark:bg-gray-800 shadow-lg mb-8">
        <div class="p-6 md:p-8">
          <UAccordion 
            :items="faqItems" 
            :ui="{
              wrapper: 'space-y-4',
              container: 'border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden w-full',
              item: {
                base: 'p-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800',
                size: 'text-base',
                color: ''
              },
              default: {
                class: 'w-full p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-300'
              },
              button: {
                base: 'p-4 text-left font-medium focus:outline-none focus:ring-2 focus:ring-offset-2',
                rounded: 'rounded-none',
                size: 'text-base',
                inactive: 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700/50 focus:ring-teal-500',
                active: 'bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 focus:ring-teal-500'
              },
              icon: {
                base: 'w-5 h-5 transition-transform duration-200',
                inactive: 'text-gray-500 dark:text-gray-400',
                active: 'rotate-180 text-teal-500'
              }
            }"
          />
        </div>
      </div>

      <!-- Submit Question Card -->
      <div class="overflow-hidden bg-white rounded-xl dark:bg-gray-800 shadow-lg">
        <div class="p-6 md:p-8">
          <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Can't Find Your Answer?</h2>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
              If you didn't find the answer you were looking for, feel free to submit your question below, and we'll get back to you as soon as possible.
            </p>
          </div>

          <form @submit.prevent="submitQuestion" class="space-y-5">
            <UFormGroup label="Your Name" name="name">
              <UInput
                v-model="form.name"
                placeholder="Enter your name"
                required
                size="lg"
                color="teal"
              />
            </UFormGroup>

            <UFormGroup label="Your Email" name="email">
              <UInput
                v-model="form.email"
                type="email"
                placeholder="Enter your email"
                required
                size="lg"
                color="teal"
              />
            </UFormGroup>

            <UFormGroup label="Your Question" name="question">
              <UTextarea
                v-model="form.question"
                placeholder="Type your question here"
                required
                size="lg"
                color="teal"
                :rows="4"
              />
            </UFormGroup>

            <div>
              <UButton
                type="submit"
                color="teal"
                block
                size="lg"
                class="mt-2"
                :ui="{ 
                  rounded: 'rounded-lg',
                  color: {
                    teal: {
                      solid: 'bg-teal-600 hover:bg-teal-700 text-white' 
                    }
                  }
                }"
              >
                <template #leading>
                  <UIcon name="i-heroicons-paper-airplane" class="h-5 w-5" />
                </template>
                Submit Question
              </UButton>
            </div>
          </form>
        </div>
      </div>

      <!-- Additional Help Section -->
      <div class="mt-10 text-center">
        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
          Need more help with JunkHop?
        </p>
        <div class="flex flex-col sm:flex-row justify-center space-y-3 sm:space-y-0 sm:space-x-4">
          <UButton
            to="#"
            color="teal"
            size="md"
            :ui="{ 
              rounded: 'rounded-lg',
              color: {
                teal: {
                  solid: 'bg-teal-600 hover:bg-teal-700 text-white' 
                }
              }
            }"
          >
            Contact Support
          </UButton>
          
          <UButton
            to="#"
            variant="outline"
            color="gray"
            size="md"
            :ui="{ 
              rounded: 'rounded-lg'
            }"
          >
            View Documentation
          </UButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue';

// Add SEO metadata
useHead({
  htmlAttrs: {
    lang: 'en'
  }
});

useSeoMeta({
  title: 'Frequently Asked Questions | JunkHop',
  description: 'Find answers to common questions about JunkHop waste management platform, recycling processes, and account management',
  ogTitle: 'JunkHop FAQ - Get Your Questions Answered',
  ogDescription: 'Frequently asked questions about using JunkHop for waste management and recycling',
  ogImage: '/social-preview.jpg',
  twitterCard: 'summary',
  twitterTitle: 'JunkHop FAQ',
  twitterDescription: 'Get answers to your questions about JunkHop',
  keywords: 'JunkHop FAQ, waste management questions, recycling FAQ, junk shop platform help'
});

// FAQs data formatted for UAccordion
const faqItems = [
  {
    label: "What is JunkHop?",
    defaultOpen: true,
    content: "JunkHop is a web platform designed to enhance waste management and recycling practices through data analytics and real-time reporting."
  },
  {
    label: "How do I create an account?",
    content: "You can create an account by clicking on the 'Sign Up' button on the homepage and filling in the required details."
  },
  {
    label: "Is JunkHop free to use?",
    content: "JunkHop offers a free tier with basic features. For advanced analytics and premium tools, you can subscribe to our paid plans."
  },
  {
    label: "How do I report an issue with JunkHop?",
    content: "You can report any issues through the 'Contact Us' page or directly email our support team at support@junkhop.com."
  },
  {
    label: "What browsers does JunkHop support?",
    content: "JunkHop is optimized for the latest versions of Chrome, Firefox, Safari, and Edge. Ensure your browser is up to date for the best experience."
  },
  {
    label: "Can I customize the dashboard?",
    content: "Yes, JunkHop allows you to customize your dashboard to display the metrics and reports most relevant to you."
  }
];

// Form state
const form = ref({
  name: '',
  email: '',
  question: '',
});

// Submit question form
const submitQuestion = () => {
  // Here you would typically handle form submission with API
  alert(`Thank you for your question, ${form.value.name}! We'll get back to you at ${form.value.email} shortly.`);
  form.value = { name: '', email: '', question: '' };
};

definePageMeta({
  title: 'Frequently Asked Questions',
  description: 'Find answers to common questions about JunkHop waste management platform'
})
</script>
