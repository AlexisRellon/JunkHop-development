<template>
  <div class="min-h-screen bg-gray-950">
    <!-- Fixed Header -->
    <header class="fixed top-0 left-0 right-0 flex items-center justify-between h-16 px-7 bg-gradient-to-r from-teal-950/25 to-teal-900/75 backdrop-blur-sm border-b border-teal-900 z-50">
      <AppLogo />
      <p>ver. 1.0.0-pre-release</p>
    </header>

    <!-- Fixed Side Panel -->
    <aside class="fixed top-16 left-0 w-80 h-[calc(100vh-4rem)] bg-teal-950/25 border-r border-teal-900/50 overflow-y-auto">
      <nav class="space-y-8 p-6">
        <!-- API Navigation -->
        <div>
          <h2 class="text-lg font-semibold mb-4 text-teal-500">Navigation</h2>
          <ul>
            <li v-for="link in links" :key="link.to">
              <a :href="link.to"
                class="flex items-center gap-3 h-12 group border-s -ms-px leading-6 ps-4 transition-all duration-200"
                :class="[
                  activeSection === link.to
                    ? 'text-teal-400 border-current font-semibold bg-gradient-to-r from-teal-900/20 to-transparent'
                    : 'text-gray-400 border-teal-900 hover:border-teal-500 hover:text-teal-300 hover:bg-gradient-to-r hover:from-teal-900/10'
                ]"
                @click="handleNavigation(link)"
              >
                {{ link.label }}
              </a>
            </li>
          </ul>
        </div>

        <!-- Additional Resources -->
        <div>
          <h2 class="text-lg font-semibold mb-4 text-teal-500">Additional Resources</h2>
          <ul class="space-y-8">
            <li
              v-for="link in linksDocs"
              :key="link.label"
              class="flex gap-3 items-center"
              :class="link.hover"
            >
              <UIcon :name="link.icon" />
              <ULink :to="link.to" :icon="link.icon">{{ link.label }}</ULink>
            </li>
          </ul>
        </div>

        <!-- Footer -->
        <div class="text-center text-gray-400 mt-8">
          <p>&copy; 2024 JunkHop. All rights reserved.</p>
          <p>Version 1.0.0-pre-release</p>
        </div>

        <!-- Go back -->
        <div class="text-center mt-8">
          <ULink to="/dashboard" class="text-teal-400 hover:text-teal-300 flex items-center gap-2 justify-center">
            <UIcon name="mdi-arrow-left" />
            <span>Go back to Dashboard</span>
          </ULink>
        </div>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="bg-gradient-to-r from-teal-950/25 to-teal-950 ml-80 pt-16 min-h-screen">
      <div class="max-w-4xl mx-auto p-6 space-y-24">
        <h1 class="text-4xl font-bold mb-8 bg-gradient-to-r from-teal-400 to-teal-500 bg-clip-text text-transparent">
          API Documentation
        </h1>

        <section id="overview" class="space-y-4 scroll-mt-20">
          <h2 class="text-3xl font-semibold text-teal-500">Overview</h2>
          <p class="text-gray-300">
            Welcome to the JunkHop API documentation. This API allows you to
            interact with the JunkHop platform programmatically. Below are the
            available endpoints and their usage.
          </p>
        </section>

        <section id="authentication" class="space-y-4 scroll-mt-20">
          <h2 class="text-3xl font-semibold text-teal-500">
            Authentication
          </h2>
          <p class="text-gray-300">To authenticate, use the following endpoint:</p>
          <pre
            class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800/50 dark:to-gray-800 rounded-lg border border-teal-100/20 dark:border-teal-900/20 shadow-sm"
          >
            <code class="text-teal-600 dark:text-teal-400">POST /api/v1/login</code>
          </pre>
          <p class="text-gray-300">Request body:</p>
          <pre class="p-4 bg-gray-100 rounded-lg border border-teal-900/20 dark:bg-gray-800">
            <code class="text-green-600 dark:text-green-400">
              {
                "email": "user@example.com",
                "password": "yourpassword"
              }
            </code>
          </pre>
          <p class="text-gray-300">Response:</p>
          <pre class="p-4 bg-gray-100 rounded-lg border border-teal-900/20 dark:bg-gray-800">
            <code class="text-green-600 dark:text-green-400">
              {
                "token": "your-auth-token",
                "user": {
                  "id": 1,
                  "name": "John Doe",
                  "email": "user@example.com"
                }
              }
            </code>
          </pre>
        </section>

        <section id="fetch-user" class="space-y-4 scroll-mt-20">
          <h2 class="text-3xl font-semibold text-teal-500">
            Fetch User
          </h2>
          <p class="text-gray-300">
            To fetch the authenticated user's data, use the following endpoint:
          </p>
          <pre
            class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800/50 dark:to-gray-800 rounded-lg border border-teal-100/20 dark:border-teal-900/20 shadow-sm"
          >
            <code class="text-teal-600 dark:text-teal-400">GET /api/v1/user</code>
          </pre>
          <p class="text-gray-300">Response:</p>
          <pre class="p-4 bg-gray-100 rounded-lg border border-teal-900/20 dark:bg-gray-800">
            <code class="text-green-600 dark:text-green-400">
              {
                "id": 1,
                "name": "John Doe",
                "email": "user@example.com"
              }
            </code>
          </pre>
        </section>

        <section id="logout" class="space-y-4 scroll-mt-20">
          <h2 class="text-3xl font-semibold text-teal-500">
            Logout
          </h2>
          <p class="text-gray-300">To log out, use the following endpoint:</p>
          <pre
            class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800/50 dark:to-gray-800 rounded-lg border border-teal-100/20 dark:border-teal-900/20 shadow-sm"
          >
            <code class="text-teal-600 dark:text-teal-400">POST /api/v1/logout</code>
          </pre>
          <p class="text-gray-300">Response:</p>
          <pre class="p-4 bg-gray-100 rounded-lg border border-teal-900/20 dark:bg-gray-800">
            <code class="text-green-600 dark:text-green-400">
              {
                "message": "Successfully logged out"
              }
            </code>
          </pre>
        </section>

        <section id="error-handling" class="space-y-4 scroll-mt-20">
          <h2 class="text-3xl font-semibold text-teal-500">
            Error Handling
          </h2>
          <p class="text-gray-300">
            All API responses include a status code to indicate success or
            failure. Here are some common status codes:
          </p>
          <ul class="list-disc list-inside text-gray-300">
            <li><strong>200 OK:</strong> The request was successful.</li>
            <li>
              <strong>400 Bad Request:</strong> The request was invalid or
              cannot be otherwise served.
            </li>
            <li>
              <strong>401 Unauthorized:</strong> Authentication failed or user
              does not have permissions for the requested operation.
            </li>
            <li>
              <strong>404 Not Found:</strong> The requested resource could not
              be found.
            </li>
            <li>
              <strong>500 Internal Server Error:</strong> An error occurred on
              the server.
            </li>
          </ul>
        </section>

        <section id="rate-limiting" class="space-y-4 scroll-mt-20">
          <h2 class="text-3xl font-semibold text-teal-500">
            Rate Limiting
          </h2>
          <p class="text-gray-300">
            To ensure fair usage and prevent abuse, the API enforces rate
            limits. If you exceed the rate limit, you will receive a
            <strong>429 Too Many Requests</strong> response. Please refer to the
            API documentation for specific rate limits.
          </p>
        </section>
      </div>
    </main>
  </div>
</template>

<style>
html {
  scroll-behavior: smooth;
}

pre {
  position: relative;
  overflow: hidden;
}

pre::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(to right, transparent, rgba(20, 184, 166, 0.1));
  pointer-events: none;
}

/* Force dark mode */
:root {
  color-scheme: dark;
}
</style>

<script setup>
import { ref, onMounted } from "vue";

const activeSection = ref("#overview");

const links = [
  { label: "Overview", to: "#overview" },
  { label: "Authentication", to: "#authentication" },
  { label: "Fetch User", to: "#fetch-user" },
  { label: "Logout", to: "#logout" },
  { label: "Error Handling", to: "#error-handling" },
  { label: "Rate Limiting", to: "#rate-limiting" },
];

// Handle navigation click
const handleNavigation = (link) => {
  activeSection.value = link.to;
};

// Set up intersection observer
onMounted(() => {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          activeSection.value = `#${entry.target.id}`;
        }
      });
    },
    {
      rootMargin: "-20% 0px -80% 0px", // Adjust these values to control when sections become active
    }
  );

  // Observe all section elements
  document.querySelectorAll("section[id]").forEach((section) => {
    observer.observe(section);
  });
});

const linksDocs = [
  {
    label: "Laravel Sanctum Documentation",
    to: "https://laravel.com/docs/11.x/sanctum",
    icon: "mdi-laravel",
    hover: "hover:text-red-500",
  },
  {
    label: "Nuxt.js Documentation",
    to: "https://nuxt.com/docs",
    icon: "mdi-nuxt",
    hover: "hover:text-green-500",
  },
  {
    label: "Tailwind CSS Documentation",
    to: "https://tailwindcss.com/docs",
    icon: "mdi-tailwind",
    hover: "hover:text-sky-500",
  },
];
</script>
