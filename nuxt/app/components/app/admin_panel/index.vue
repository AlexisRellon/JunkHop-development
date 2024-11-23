<template>
  <div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <div class="w-64 bg-white shadow-md">
      <div class="p-6">
        <h1 class="text-2xl font-bold text-gray-800">Admin Panel</h1>
      </div>
      <nav class="mt-6">
        <ul>
          <li class="mb-2">
            <a
              href="#"
              class="block p-3 text-gray-700 hover:bg-gray-200 rounded-md"
              >Dashboard</a
            >
          </li>
          <li class="mb-2">
            <a
              href="#"
              class="block p-3 text-gray-700 hover:bg-gray-200 rounded-md"
              >Users</a
            >
          </li>
          <li class="mb-2">
            <a
              href="#"
              class="block p-3 text-gray-700 hover:bg-gray-200 rounded-md"
              >Settings</a
            >
          </li>
          <li class="mb-2">
            <a
              href="#"
              class="block p-3 text-gray-700 hover:bg-gray-200 rounded-md"
              >Reports</a
            >
          </li>
        </ul>
      </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-6">
      <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-6">Dashboard</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Card 1 -->
          <div class="bg-blue-500 text-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-bold">Total Users</h3>
            <p class="mt-2 text-3xl">1,234</p>
          </div>
          <!-- Card 2 -->
          <div class="bg-green-500 text-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-bold">Active Users</h3>
            <p class="mt-2 text-3xl">567</p>
          </div>
          <!-- Card 3 -->
          <div class="bg-red-500 text-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-bold">Pending Requests</h3>
            <p class="mt-2 text-3xl">89</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup></script>
