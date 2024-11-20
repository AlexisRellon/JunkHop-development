<template>
  <div class="flex items-center justify-center py-10 min-h-screen">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-3xl">
      <div class="font-black text-3xl tracking-tight text-center mb-8">
        <span class="text-gray-700 dark:text-gray-100">Frequently Asked</span>
        <span class="text-primary-500"> Questions</span>
      </div>
      <div class="space-y-4 mb-10">
        <div>
          <button
            @click="toggle(1)"
            class="hover:bg-primary-100 dark:hover:bg-primary-700 w-full flex justify-between items-center bg-gray-200 dark:bg-gray-800 text-gray-900 dark:text-white font-semibold px-4 py-3 rounded-lg focus:outline-none"
          >
            <span>What is CleanSnap?</span>
            <svg
              :class="openIndex === 1 ? 'rotate-180' : ''"
              class="w-5 h-5 transform transition-transform"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div
            v-if="openIndex === 1"
            class="mt-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 p-4 rounded-lg"
          >
            <p>
              CleanSnap is a web platform designed to enhance waste management and recycling practices through data analytics and real-time reporting.
            </p>
          </div>
        </div>

        <div>
          <button
            @click="toggle(2)"
            class="hover:bg-primary-100 dark:hover:bg-primary-700 w-full flex justify-between items-center bg-gray-200 dark:bg-gray-800 text-gray-900 dark:text-white font-semibold px-4 py-3 rounded-lg focus:outline-none"
          >
            <span>How do I create an account?</span>
            <svg
              :class="openIndex === 2 ? 'rotate-180' : ''"
              class="w-5 h-5 transform transition-transform"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div
            v-if="openIndex === 2"
            class="mt-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 p-4 rounded-lg"
          >
            <p>
              You can create an account by clicking on the "Sign Up" button on the homepage and filling in the required details.
            </p>
          </div>
        </div>


        <div>
          <button
            @click="toggle(3)"
            class="hover:bg-primary-100 dark:hover:bg-primary-700 w-full flex justify-between items-center bg-gray-200 dark:bg-gray-800 text-gray-900 dark:text-white font-semibold px-4 py-3 rounded-lg focus:outline-none"
          >
            <span>Is CleanSnap free to use?</span>
            <svg
              :class="openIndex === 3 ? 'rotate-180' : ''"
              class="w-5 h-5 transform transition-transform"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div
            v-if="openIndex === 3"
            class="mt-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 p-4 rounded-lg"
          >
            <p>
              CleanSnap offers a free tier with basic features. For advanced analytics and premium tools, you can subscribe to our paid plans.
            </p>
          </div>
        </div>

        <div>
          <button
            @click="toggle(4)"
            class="hover:bg-primary-100 dark:hover:bg-primary-700 w-full flex justify-between items-center bg-gray-200 dark:bg-gray-800 text-gray-900 dark:text-white font-semibold px-4 py-3 rounded-lg focus:outline-none"
          >
            <span>How do I report an issue with CleanSnap?</span>
            <svg
              :class="openIndex === 4 ? 'rotate-180' : ''"
              class="w-5 h-5 transform transition-transform"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div
            v-if="openIndex === 4"
            class="mt-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 p-4 rounded-lg"
          >
            <p>
              You can report any issues through the "Contact Us" page or directly email our support team at support@cleansnap.com.
            </p>
          </div>
        </div>

        <div>
          <button
            @click="toggle(5)"
            class="hover:bg-primary-100 dark:hover:bg-primary-700 w-full flex justify-between items-center bg-gray-200 dark:bg-gray-800 text-gray-900 dark:text-white font-semibold px-4 py-3 rounded-lg focus:outline-none"
          >
            <span>What browsers does CleanSnap support?</span>
            <svg
              :class="openIndex === 5 ? 'rotate-180' : ''"
              class="w-5 h-5 transform transition-transform"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div
            v-if="openIndex === 5"
            class="mt-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 p-4 rounded-lg"
          >
            <p>
              CleanSnap is optimized for the latest versions of Chrome, Firefox, Safari, and Edge. Ensure your browser is up to date for the best experience.
            </p>
          </div>
        </div>

        <div>
          <button
            @click="toggle(6)"
            class="hover:bg-primary-100 dark:hover:bg-primary-700 w-full flex justify-between items-center bg-gray-200 dark:bg-gray-800 text-gray-900 dark:text-white font-semibold px-4 py-3 rounded-lg focus:outline-none"
          >
            <span>Can I customize the dashboard?</span>
            <svg
              :class="openIndex === 6 ? 'rotate-180' : ''"
              class="w-5 h-5 transform transition-transform"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div
            v-if="openIndex === 6"
            class="mt-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 p-4 rounded-lg"
          >
            <p>
              Yes, CleanSnap allows you to customize your dashboard to display the metrics and reports most relevant to you.
            </p>
          </div>
        </div>
      </div>

      <div class="bg-gray-200 dark:bg-gray-800 p-6 rounded-lg">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Can't Find Your Question?</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
          If you didn’t find the answer you were looking for, feel free to submit your question below, and we’ll get back to you as soon as possible.
        </p>
        <form @submit.prevent="submitQuestion" class="space-y-4">

          <div>
            <label for="name" class="block text-gray-900 dark:text-gray-300 mb-2">Your Name</label>
            <input
              type="text"
              id="name"
              v-model="form.name"
              required
              placeholder="Enter your name"
              class="w-full px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-200 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>


          <div>
            <label for="email" class="block text-gray-900 dark:text-gray-300 mb-2">Your Email</label>
            <input
              type="email"
              id="email"
              v-model="form.email"
              required
              placeholder="Enter your email"
              class="w-full px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-200 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>


          <div>
            <label for="question" class="block text-gray-900 dark:text-gray-300 mb-2">Your Question</label>
            <textarea
              id="question"
              v-model="form.question"
              required
              placeholder="Type your question here"
              class="w-full px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-200 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500"
              rows="4"
            ></textarea>
          </div>


          <div>
            <button
              type="submit"
              class="w-full px-4 py-2 font-semibold text-center text-white bg-teal-600 rounded-md shadow-md hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 dark:text-white dark:bg-teal-600"
            >
              Submit Question
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue';

const openIndex = ref<number | null>(null);

const form = ref({
  name: '',
  email: '',
  question: '',
});

const toggle = (index: number) => {
  openIndex.value = openIndex.value === index ? null : index;
};

const submitQuestion = () => {
  alert(`Thank you for your question, ${form.value.name}! We'll get back to you at ${form.value.email}.`);
  form.value = { name: '', email: '', question: '' };
};
</script>

<style>
</style>
