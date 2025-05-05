<template>
    <div class="flex h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Sidebar Component -->
        <AdminSidebar v-model="isSidebarCollapsed" />

        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto custom-scrollbar">
            <!-- Header with Avatar and Breadcrumb -->
            <header class="flex items-center justify-between px-6 py-4 bg-white dark:bg-gray-800 shadow-sm">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-800 dark:text-white">Bid Management</h1>
                    <nav class="ml-4">
                        <ol class="flex text-sm">
                            <li class="flex items-center">
                                <NuxtLink to="/dashboard"
                                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                    Dashboard</NuxtLink>
                                <UIcon name="i-heroicons-chevron-right" class="w-4 h-4 mx-2 text-gray-400" />
                            </li>
                            <li>
                                <span class="text-gray-700 dark:text-gray-300">Bids</span>
                            </li>
                        </ol>
                    </nav>
                </div>
                <div class="flex items-center gap-4"> <span
                        class="text-sm font-medium text-gray-600 dark:text-gray-300">Welcome, {{ auth.user.name
                        }}</span>
                    <UAvatar size="sm" :src="$storage ? $storage(auth.user.avatar) : ''" :alt="auth.user.name"
                        :ui="{ rounded: 'rounded-full', ring: 'ring-2 ring-teal-500' }" />
                </div>
            </header>

            <div class="p-6">
                <AdminPanelBidManagement />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useNuxtApp } from '#app';
import AdminSidebar from '@/components/app/admin_panel/admin_sidebar.vue';
import AdminPanelBidManagement from '@/components/app/admin_panel/admin_panel_bid_management.vue';

const auth = useAuthStore();
const { $storage } = useNuxtApp();

// Sidebar state
const isSidebarCollapsed = ref(false);
</script>

<style scoped>
/* Custom scrollbar class for specific components */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 3px;
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #4b5563;
}
</style>
