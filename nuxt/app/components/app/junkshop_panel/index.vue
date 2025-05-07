<template>
  <div class="flex bg-gray-50 dark:bg-gray-900">
    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar">
      <!-- Header with Avatar and Welcome -->
      <header class="flex items-center justify-between px-6 py-4 bg-white dark:bg-gray-800 shadow-sm">
        <h1 class="text-xl font-semibold text-gray-800 dark:text-white">Junkshop Dashboard</h1>
        <div class="flex items-center gap-4">
          <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Welcome, {{ auth.user.name }}</span>
          <UAvatar size="sm" :src="$storage ? $storage(auth.user.avatar) : ''" :alt="auth.user.name"
            :ui="{ rounded: 'rounded-full', ring: 'ring-2 ring-emerald-500' }" />
        </div>
      </header>

      <div class="p-6">
        <!-- Dashboard Summary Section -->
        <div class="mb-6">
          <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-4">Junkshop Overview</h2>
          <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
            <!-- Card 1: Junkshop Status -->
            <UCard class="relative overflow-hidden rounded-lg shadow-sm border-0">
              <div
                class="p-5 bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/30 dark:to-emerald-800/30">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-sm font-medium text-emerald-600 dark:text-emerald-300">Junkshop Status</p>
                    <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-1">
                      {{ junkshop.ulid ? 'Active' : 'Not Created' }}
                    </h3>
                  </div>
                  <div
                    class="p-3 bg-emerald-500 rounded-full bg-opacity-10 w-[48px] h-[48px] flex justify-center items-center dark:bg-opacity-20">
                    <UIcon name="mdi-home" class="text-emerald-600 dark:text-emerald-300" size="lg" />
                  </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-emerald-500 dark:text-emerald-300">
                  <UIcon name="i-heroicons-information-circle" class="mr-1" />
                  <span>{{ junkshop.ulid ? 'Your junkshop is visible to users' : 'Set up your junkshop' }}</span>
                </div>
              </div>
            </UCard>

            <!-- Card 2: Items Offered -->
            <UCard class="relative overflow-hidden rounded-lg shadow-sm border-0">
              <div class="p-5 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-sm font-medium text-blue-600 dark:text-blue-300">Items Offered</p>
                    <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ items.length }}</h3>
                  </div>
                  <div
                    class="p-3 bg-blue-500 rounded-full bg-opacity-10 w-[48px] h-[48px] flex justify-center items-center dark:bg-opacity-20">
                    <UIcon name="mdi-cube" class="text-blue-600 dark:text-blue-300" size="lg" />
                  </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-blue-500 dark:text-blue-300">
                  <UIcon name="i-heroicons-cube" class="mr-1" />
                  <span>Items available for collection</span>
                </div>
              </div>
            </UCard>

            <!-- Card 3: Active Bids -->
            <UCard class="relative overflow-hidden rounded-lg shadow-sm border-0" @click="activeTab = 2">
              <div class="p-5 bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/30 dark:to-amber-800/30">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-sm font-medium text-amber-600 dark:text-amber-300">Active Bids</p>
                    <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ pendingBidsCount }}</h3>
                  </div>
                  <div
                    class="p-3 bg-amber-500 rounded-full bg-opacity-10 w-[48px] h-[48px] flex justify-center items-center dark:bg-opacity-20">
                    <UIcon name="i-heroicons-currency-dollar" class="text-amber-600 dark:text-amber-300" size="lg" />
                  </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-amber-500 dark:text-amber-300">
                  <UIcon name="i-heroicons-document-text" class="mr-1" />
                  <span>Pending bid offers</span>
                </div>
              </div>
            </UCard>

            <!-- Card 4: Last Updated -->
            <UCard class="relative overflow-hidden rounded-lg shadow-sm border-0">
              <div
                class="p-5 bg-gradient-to-br from-violet-50 to-violet-100 dark:from-violet-900/30 dark:to-violet-800/30">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-sm font-medium text-violet-600 dark:text-violet-300">Last Updated</p>
                    <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ getLastUpdated }}</h3>
                  </div>
                  <div
                    class="p-3 bg-violet-500 rounded-full bg-opacity-10 w-[48px] h-[48px] flex justify-center items-center dark:bg-opacity-20">
                    <UIcon name="mdi-calendar-clock" class="text-violet-600 dark:text-violet-300" size="lg" />
                  </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-violet-500 dark:text-violet-300">
                  <UIcon name="i-heroicons-clock" class="mr-1" />
                  <span>{{ junkshop.updated_at ? 'Last information update' : 'No updates yet' }}</span>
                </div>
              </div>
            </UCard>
          </div>
        </div> <!-- Tabs for Main Dashboard Sections -->
        <div class="mb-6">
          <UTabs v-model="activeTab" :items="dashboardTabs" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm"
            :ui="{ wrapper: 'w-full' }" selected-class="!text-emerald-500 dark:!text-emerald-400">
            <template #default="{ item, selected }">
              <div class="flex items-center gap-2">
                <!-- Use only one icon with name selected dynamically -->
                <span>{{ item.label }}</span>
                <UBadge v-if="item.key === 'bids' && pendingBidsCount > 0" color="amber" size="xs" variant="subtle">
                  {{ pendingBidsCount }}
                </UBadge>
              </div>
            </template>
          </UTabs>
        </div><!-- Main Content Grid -->
        <div v-if="currentTabKey === 'info'" class="grid grid-cols-1 gap-8">
          <!-- Junkshop Information Section -->
          <div>
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200">Junkshop Information</h2>
              <UButton size="sm" color="emerald" variant="soft" icon="i-heroicons-arrow-path" :loading="isLoading"
                @click="refreshData" :tooltip="{ text: 'Refresh Data' }" square />
            </div>

            <UCard class="p-5">
              <UForm :state="junkshop" @submit.prevent="updateJunkshop" class="space-y-4">
                <UFormGroup label="Name" name="name" required>
                  <UInput v-model="junkshop.name" type="text" icon="i-heroicons-building-storefront"
                    placeholder="Junkshop name" required ref="nameInput" />
                </UFormGroup>

                <UFormGroup label="Contact" name="contact" required>
                  <UInput v-model="junkshop.contact" type="tel" icon="i-heroicons-phone" placeholder="Contact number"
                    required />
                </UFormGroup>

                <UFormGroup label="Address" name="address" required>
                  <UInput v-model="junkshop.address" type="text" icon="i-heroicons-map-pin"
                    placeholder="Your junkshop's location address" required />
                </UFormGroup>

                <UFormGroup label="Description" name="description">
                  <UTextarea v-model="junkshop.description" placeholder="Brief description about your Junkshop"
                    :rows="3" />
                </UFormGroup>

                <div class="pt-2">
                  <UButton type="submit" color="emerald" block :loading="isLoading" :disabled="isLoading"
                    class="transition-all duration-200" icon="i-heroicons-save" :label="'Submit'" />
                </div>
              </UForm>
            </UCard>
          </div>
        </div>

        <!-- Items Management Tab Content -->
        <div v-if="currentTabKey === 'items'" class="grid grid-cols-1 gap-8">
          <!-- Items Offered Section -->
          <div>
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200">Items Offered</h2>
              <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ items.length }} items
              </span>
            </div>

            <UCard class="p-5"> <!-- Add Item Input -->
              <div class="flex gap-2 mb-4">
                <UInput v-model="newItem" type="text" placeholder="Add new item" class="flex-1"
                  :disabled="!junkshop.ulid" @keyup.enter="addItem" ref="newItemInput" />
                <UInput v-model="newItemQuantity" type="number" min="0.1" step="0.1" placeholder="Qty (kg)" class="w-24"
                  :disabled="!junkshop.ulid" />
                <UButton @click="addItem" color="emerald" variant="solid"
                  :disabled="!newItem.trim() || !junkshop.ulid || newItemQuantity <= 0" icon="i-heroicons-plus"
                  :tooltip="{ text: 'Add Item' }" square />
              </div>

              <UDivider />

              <!-- Empty State when no junkshop exists -->
              <div v-if="!junkshop.ulid" class="flex flex-col items-center justify-center p-8 text-center">
                <div class="p-4 mb-4 rounded-full bg-amber-100 dark:bg-amber-900/30">
                  <UIcon name="i-heroicons-exclamation-triangle" class="text-2xl text-amber-600 dark:text-amber-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-1">Create your junkshop first</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Complete the form in the Junkshop Info tab</p>
                <!-- Button to navigate to info tab -->
                <UButton @click="activeTab = 0" color="emerald" variant="soft">
                  Go to Junkshop Info
                </UButton>
              </div>

              <!-- Empty State when no items -->
              <div v-else-if="items.length === 0" class="flex flex-col items-center justify-center p-8 text-center">
                <div class="p-4 mb-4 rounded-full bg-blue-100 dark:bg-blue-900/30">
                  <UIcon name="i-heroicons-cube" class="text-2xl text-blue-600 dark:text-blue-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-1">No items yet</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Add the items your junkshop accepts</p>
                <!-- Removed standalone plus button to avoid duplication -->
                <span class="text-sm text-emerald-600 dark:text-emerald-400 cursor-pointer hover:underline"
                  @click="focusNewItemInput">
                  Click here to add your first item
                </span>
              </div>

              <!-- Items List -->
              <div v-else class="max-h-[400px] overflow-y-auto custom-scrollbar pr-1">
                <TransitionGroup tag="ul" name="items-list" class="space-y-2">
                  <li v-for="item in items" :key="item.id"
                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center justify-between p-3 gap-2">
                      <div v-if="editingItemId === item.id" class="flex-1 flex gap-2">
                        <UInput v-model="editingItemName" type="text" placeholder="Item name" class="w-full"
                          @keyup.enter="saveItem(item.id)" ref="editItemInput" />
                        <UInput v-model="editingItemQuantity" type="number" min="0.1" step="0.1" placeholder="Qty (kg)"
                          class="w-24" @keyup.enter="saveItem(item.id)" ref="editItemQuantityInput" />
                      </div>
                      <div v-else class="flex items-center gap-3 flex-1">
                        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                          <UIcon name="i-heroicons-cube" class="text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                          <span class="font-medium text-gray-700 dark:text-gray-200">{{ item.name }}</span>
                          <div class="text-xs text-gray-500 dark:text-gray-400">
                            Quantity: {{ item.quantity || 'Not specified' }} {{ item.quantity ? '/ kg' : '' }}
                          </div>
                        </div>
                      </div>

                      <div class="flex space-x-2">
                        <template v-if="editingItemId === item.id">
                          <UButton @click="saveItem(item.id)" color="emerald" variant="soft" icon="i-heroicons-check"
                            size="sm" square :tooltip="{ text: 'Save' }" />
                          <UButton @click="cancelEdit" color="gray" variant="soft" icon="i-heroicons-x-mark" size="sm"
                            square :tooltip="{ text: 'Cancel' }" />
                        </template>
                        <template v-else>                          <UButton @click="createBidForItem(item)" color="amber" variant="ghost"
                            icon="i-heroicons-currency-dollar" size="xs" square :tooltip="{ text: 'Create Bid' }" />
                          <UButton @click="editItem(item)" color="blue" variant="ghost" icon="i-heroicons-pencil-square"
                            size="xs" square :tooltip="{ text: 'Edit' }" />
                          <UButton @click="confirmDeleteItem(item)" color="red" variant="ghost" icon="i-heroicons-trash"
                            size="xs" square :tooltip="{ text: 'Delete' }" />
                        </template>
                      </div>
                    </div>
                  </li>
                </TransitionGroup>
              </div>
            </UCard>
          </div>
        </div>
      </div>

      <!-- Bid Management Tab Content -->
      <div v-if="currentTabKey === 'bids'" class="mb-6 mx-8">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200">Bid Management</h2>
          <UButton size="sm" color="amber" variant="soft" icon="i-heroicons-arrow-path" :loading="isBidLoading"
            @click="fetchBids" :tooltip="{ text: 'Refresh Bids' }" square />
        </div>

        <UCard class="p-5">
          <!-- Empty State when no junkshop exists -->
          <div v-if="!junkshop.ulid" class="flex flex-col items-center justify-center p-8 text-center">
            <div class="p-4 mb-4 rounded-full bg-amber-100 dark:bg-amber-900/30">
              <UIcon name="i-heroicons-exclamation-triangle" class="text-2xl text-amber-600 dark:text-amber-400" />
            </div>
            <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-1">Create your junkshop first</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Complete the junkshop setup to manage bids</p>
            <UButton @click="activeTab = 0; focusNameInput()" color="emerald" variant="soft">
              Set up your junkshop
            </UButton>
          </div>

          <!-- Empty State when no pending bids -->
          <div v-else-if="pendingBids.length === 0" class="flex flex-col items-center justify-center p-8 text-center">
            <div class="p-4 mb-4 rounded-full bg-amber-100 dark:bg-amber-900/30">
              <UIcon name="i-heroicons-currency-dollar" class="text-2xl text-amber-600 dark:text-amber-400" />
            </div>
            <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-1">No bids created yet</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Create bids for your items that will be reviewed by
              admins</p>
            <UButton @click="activeTab = 1" color="amber" variant="soft">
              Go to Items Management
            </UButton>
          </div>

          <!-- Bids List -->
          <div v-else class="max-h-[600px] overflow-y-auto custom-scrollbar pr-1">
            <UTable :columns="[
              { key: 'item', label: 'Item' },
              { key: 'quantity', label: 'Quantity (kg)' },
              { key: 'price', label: 'Price/kg' },
              { key: 'total', label: 'Total' },
              { key: 'status', label: 'Status' },
              { key: 'created_at', label: 'Date' },
              { key: 'actions', label: 'Actions' }
            ]" :rows="pendingBids">
              <template #item-data="{ row }">
                <div class="flex items-center gap-2">
                  <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                    <UIcon name="i-heroicons-cube" class="text-blue-600 dark:text-blue-400" />
                  </div>
                  <span>{{ row.item ? row.item.name : 'Unknown Item' }}</span>
                </div>
              </template>

              <template #quantity-data="{ row }">
                <span class="font-medium">{{ row.quantity }} kg</span>
              </template> <template #price-data="{ row }">
                <span class="font-medium">₱{{ Number(row.price_per_kg).toFixed(2) }}</span>
              </template>

              <template #total-data="{ row }">
                <span class="font-medium">₱{{ (Number(row.quantity) * Number(row.price_per_kg)).toFixed(2) }}</span>
              </template>

              <template #status-data="{ row }">
                <UBadge :color="row.status === 'pending' ? 'amber' : (row.status === 'accepted' ? 'emerald' : 'red')"
                  variant="subtle">
                  {{ row.status.charAt(0).toUpperCase() + row.status.slice(1) }}
                </UBadge>
              </template>

              <template #created_at-data="{ row }">
                <span>{{ new Date(row.created_at).toLocaleDateString() }}</span>
              </template> <template #actions-data="{ row }">
                <div class="flex space-x-2">
                  <UButton @click="viewBidDetails(row)" color="blue" variant="ghost" icon="i-heroicons-eye" size="xs"
                    square :tooltip="{ text: 'View Details' }" />
                  <UBadge v-if="row.status === 'pending'" color="gray" variant="subtle" class="ml-2">
                    Awaiting Admin Review
                  </UBadge>
                </div>
              </template>
            </UTable>
          </div>
        </UCard>
      </div>
    </div>
  </div>

  <!-- Bid Creation Modal -->
  <UModal v-model="showBidModal" :ui="{ width: 'sm:max-w-md' }">
    <UCard>
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-base font-semibold text-gray-900 dark:text-white">
            Create New Bid
          </h3>
          <UButton color="gray" variant="ghost" icon="i-heroicons-x-mark" class="-my-1" @click="cancelBid" />
        </div>
      </template>
      <div class="space-y-4">
        <div v-if="selectedItem" class="bg-amber-50 dark:bg-amber-900/30 p-3 rounded-lg flex items-center gap-3">
          <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-full">
            <UIcon name="i-heroicons-cube" class="text-blue-600 dark:text-blue-400" />
          </div>
          <div>
            <div class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ selectedItem.name }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Item ID: {{ selectedItem.id }}</div>
          </div>
        </div>

        <UFormGroup label="Quantity (kg)" required>
          <UInput v-model="bidQuantity" type="number" min="0.1" step="0.1" placeholder="Enter quantity in kilograms"
            icon="i-heroicons-scale" />
        </UFormGroup>

        <UFormGroup label="Price per kg (₱)" required>
          <UInput v-model="bidPricePerKg" type="number" min="0" step="0.01" placeholder="Enter price per kilogram"
            icon="i-heroicons-currency-dollar" />
        </UFormGroup>

        <UFormGroup label="Notes">
          <UTextarea v-model="bidNotes" placeholder="Additional notes or conditions (optional)" :rows="3" />
        </UFormGroup>
      </div> <template #footer>
        <div class="flex justify-end gap-2">
          <UButton color="gray" variant="soft" @click="cancelBid">
            Cancel
          </UButton>
          <UButton color="amber" :loading="isBidLoading"
            :disabled="bidQuantity <= 0 || bidPricePerKg <= 0 || isBidLoading" @click="submitBid">
            Create Bid
          </UButton>
        </div>
      </template>
    </UCard>
  </UModal>

  <!-- Bid Details Modal -->
  <UModal v-model="showBidDetailsModal" :ui="{ width: 'sm:max-w-md' }">
    <UCard v-if="selectedBid">
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-base font-semibold text-gray-900 dark:text-white">
            Bid Details
          </h3>
          <UButton color="gray" variant="ghost" icon="i-heroicons-x-mark" class="-my-1" @click="closeBidDetails" />
        </div>
      </template>
      <div class="space-y-4">
        <div class="bg-amber-50 dark:bg-amber-900/30 p-3 rounded-lg">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <div class="text-xs text-gray-500 dark:text-gray-400">Item</div>
              <div class="text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ selectedBid.item ? selectedBid.item.name : 'Unknown Item' }}
              </div>
            </div>
            <div>
              <div class="text-xs text-gray-500 dark:text-gray-400">Quantity</div>
              <div class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ selectedBid.quantity }} kg</div>
            </div>
            <div>
              <div class="text-xs text-gray-500 dark:text-gray-400">Price per kg</div>
              <div class="text-sm font-medium text-gray-700 dark:text-gray-200">₱{{
                Number(selectedBid.price_per_kg).toFixed(2) }}</div>
            </div>
            <div>
              <div class="text-xs text-gray-500 dark:text-gray-400">Total Value</div>
              <div class="text-sm font-medium text-gray-700 dark:text-gray-200">₱{{ (Number(selectedBid.quantity) *
                Number(selectedBid.price_per_kg)).toFixed(2) }}</div>
            </div>
            <div>
              <div class="text-xs text-gray-500 dark:text-gray-400">Status</div>
              <UBadge
                :color="selectedBid.status === 'pending' ? 'amber' : (selectedBid.status === 'accepted' ? 'emerald' : 'red')"
                variant="subtle">
                {{ selectedBid.status.charAt(0).toUpperCase() + selectedBid.status.slice(1) }}
              </UBadge>
            </div>
            <div>
              <div class="text-xs text-gray-500 dark:text-gray-400">Date Created</div>
              <div class="text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ new Date(selectedBid.created_at).toLocaleString() }}
              </div>
            </div>
          </div>
        </div>        <div v-if="selectedBid.notes" class="space-y-2">
          <div class="text-xs text-gray-500 dark:text-gray-400">Notes</div>
          <div class="text-sm bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
            {{ selectedBid.notes }}
          </div>
        </div>

        <!-- Rejection Reason (if rejected) -->
        <div v-if="selectedBid.status === 'rejected' && selectedBid.rejection_reason" class="space-y-2">
          <div class="text-xs text-red-500 dark:text-red-400 font-medium">Rejection Reason</div>
          <div class="text-sm bg-red-50 dark:bg-red-900/20 p-3 rounded-lg text-red-800 dark:text-red-300 border border-red-200 dark:border-red-900/50">
            {{ selectedBid.rejection_reason }}
          </div>
        </div>
      </div><template #footer>
        <div class="flex justify-end gap-2">
          <UButton color="gray" variant="ghost" @click="closeBidDetails">
            Close
          </UButton>
          <div v-if="selectedBid.status === 'pending'" class="flex items-center mr-2">
            <UIcon name="i-heroicons-clock" class="text-amber-500 mr-1" />
            <span class="text-sm text-amber-500">Awaiting Admin Review</span>
          </div>
        </div>      </template>
    </UCard>
  </UModal>
  
  <!-- Delete Confirmation Dialog -->
  <UiConfirmationDialog
    v-model:show="showDeleteConfirmation"
    title="Delete Item"
    :message="'Are you sure you want to delete ' + (itemToDelete?.name || 'this item') + '?'"
    confirm-label="Yes, Delete"
    confirm-color="red"
    confirm-icon="i-heroicons-trash"
    destructive
    @confirm="confirmItemDeletion"
  />
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed, nextTick } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";

import { useNuxtApp } from "#app";

// Helper function to generate a valid ULID in the format the server expects
const generateULID = () => {
  const timestamp = Math.floor(Date.now() / 1000).toString(36).padStart(6, '0');
  const randomPart = Array.from({ length: 20 }, () => 
    "0123456789ABCDEFGHJKMNPQRSTVWXYZ"[Math.floor(Math.random() * 32)]
  ).join('');
  return timestamp + randomPart;
};

const router = useRouter();
const toast = useToast();
const { $storage } = useNuxtApp();

// Store instances
const auth = useAuthStore();
const user = computed(() => auth.user);

// Determine if the role is User
const isUserRole = computed(() => {
  return user.value?.roles?.includes("user");
});

if (isUserRole.value === true) {
  router.push("/account/general");
}

// Tab navigation
const activeTab = ref(0); // Default tab is info (index 0)
const dashboardTabs = [
  { key: 'info', label: 'Junkshop Info', icon: 'i-heroicons-information-circle', activeIcon: 'i-heroicons-information-circle-solid' },
  { key: 'items', label: 'Items Management', icon: 'i-heroicons-cube', activeIcon: 'i-heroicons-cube-solid' },
  { key: 'bids', label: 'Bid Management', icon: 'i-heroicons-currency-dollar', activeIcon: 'i-heroicons-currency-dollar-solid' },
];

// Helper function to get the current tab key
const currentTabKey = computed(() => {
  return dashboardTabs[activeTab.value]?.key || 'info';
});

// Loading states
const isLoading = ref(false);
const isBidLoading = ref(false);

// Reactive state for Junkshop details
const junkshop = reactive({
  name: "",
  contact: "",
  description: "",
  address: "",
  ulid: "",
  owner_ulid: "",
  updated_at: null as string | null,
});

// Computed property for last updated
const getLastUpdated = computed(() => {
  if (junkshop.updated_at) {
    // Format date to show just the date (e.g. "May 15, 2023")
    return new Date(junkshop.updated_at).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    });
  }
  return 'Never';
});

// Reactive state for items
const items = ref<any[]>([]);
const newItem = ref("");
const newItemQuantity = ref(1); // Default quantity to 1 kg
const newItemInput = ref(null);
const nameInput = ref(null);
const editItemInput = ref(null);
const editItemQuantityInput = ref(null);

// State for editing items
const editingItemId = ref<number | null>(null);
const editingItemName = ref("");
const editingItemQuantity = ref(0);

// State for item deletion confirmation
const showDeleteConfirmation = ref(false);
const itemToDelete = ref<any>(null);

// State for bids
const pendingBids = ref<any[]>([]);
const pendingBidsCount = computed(() => pendingBids.value.length);
const selectedItem = ref<any>(null);
const bidQuantity = ref<number>(0);
const bidPricePerKg = ref<number>(0);
const bidNotes = ref<string>('');
const showBidModal = ref(false);
const showBidDetailsModal = ref(false);
const selectedBid = ref<any>(null);

// Define proper interface for the junkshop response
interface JunkshopResponse {
  ulid: string;
  name: string;
  contact: string;
  description: string;
  address: string;
  user_id: string;
  created_at?: string;
  updated_at?: string;
}

// Function to fetch all junkshop data
const fetchAllData = async () => {
  try {
    isLoading.value = true;
    console.log("Fetching junkshop data...");

    const junkshopsData = await $fetch<any[]>('/junkshop', {
      headers: {
        Authorization: `Bearer ${auth.token}`
      }
    });

    console.log("Junkshops data received:", junkshopsData);

    // Match using user_id instead of owner_ulid (field name mismatch)
    const userJunkshop = Array.isArray(junkshopsData)
      ? junkshopsData.find(shop => shop.user_id === auth.user.ulid)
      : null;

    console.log("Current user ULID:", auth.user.ulid);
    console.log("Found user junkshop:", userJunkshop); if (userJunkshop) {
      Object.assign(junkshop, userJunkshop);
      junkshop.owner_ulid = auth.user.ulid; // Set this for later use

      await fetchItems(userJunkshop.ulid);
      await fetchBids(userJunkshop.ulid);
    } else {
      console.log("No junkshop found for this user, initializing new one");
      // Initialize new junkshop with owner_ulid
      junkshop.owner_ulid = auth.user.ulid;
    }
  } catch (error) {
    console.error('Error fetching junkshop data:', error);
    handleApiError(error, 'Failed to load junkshop data. Please try again.');
  } finally {
    isLoading.value = false;
  }
};

// Function to fetch junkshop items
const fetchItems = async (junkshopUlid: string) => {
  try {
    console.log("Fetching items for junkshop:", junkshopUlid);

    const itemsData = await $fetch<any[]>(`/junkshop/${junkshopUlid}/items`, {
      headers: {
        Authorization: `Bearer ${auth.token}`
      }
    });

    console.log("Raw items data received:", itemsData);    // Make sure we preserve all necessary data from the API response
    items.value = Array.isArray(itemsData) ? itemsData.map(item => ({
      ...item, // Keep all original properties
      pivot: item.pivot || {}, // Ensure pivot exists
      quantity: item.pivot?.quantity || "0.00", // Get quantity from pivot table
      id: item.id, // Main item ID
      name: item.name || `Item ${item.id}`,
      created_at: item.created_at,
      updated_at: item.updated_at
    })) : [];

    console.log("Processed items:", items.value);
  } catch (error) {
    console.error('Error fetching junkshop items:', error);
    handleApiError(error, 'Failed to load junkshop items.');
  }
};

// Refresh data function
const refreshData = () => {
  fetchAllData();
};

// Focus functions for better UX
const focusNewItemInput = () => {
  nextTick(() => {
    if (newItemInput.value) {
      newItemInput.value.focus();
    }
  });
};

const focusNameInput = () => {
  nextTick(() => {
    if (nameInput.value) {
      nameInput.value.focus();
    }
  });
};

// Handle API errors consistently
const handleApiError = async (error: any, defaultMessage: string) => {
  // More detailed error logging
  if (error.response) {
    console.error('Response status:', error.response.status);
    try {
      const responseData = await error.response.json();
      console.error('Response data:', responseData);
    } catch (e) {
      console.error('Could not parse error response');
    }
  }

  toast.add({
    title: 'Error',
    description: defaultMessage,
    color: 'red'
  });
};

// Fetch Junkshop details and items on component mount
onMounted(() => {
  fetchAllData();
});

// Function to update or create Junkshop details
const updateJunkshop = async () => {
  try {
    isLoading.value = true;

    // Ensure required fields
    if (!junkshop.name || !junkshop.contact || !junkshop.address) {
      toast.add({
        title: 'Error',
        description: 'Please fill all required fields',
        color: 'red'
      });
      return;
    }

    // Create new junkshop or update existing one
    if (!junkshop.ulid) {
      // Create new junkshop
      const response = await $fetch<JunkshopResponse>('/junkshop', {
        method: "POST",
        body: {
          name: junkshop.name,
          contact: junkshop.contact,
          description: junkshop.description,
          address: junkshop.address,
          owner_ulid: auth.user.ulid,
        },
        headers: {
          Authorization: `Bearer ${auth.token}`,
          'Content-Type': 'application/json'
        },
      });

      // Update local state with new ulid
      if (response && response.ulid) {
        junkshop.ulid = response.ulid;
        junkshop.updated_at = response.updated_at || new Date().toISOString();
      }

      toast.add({
        title: 'Success',
        description: 'Junkshop created successfully',
        color: 'green'
      });

      // Focus on the new item input after creating the junkshop
      setTimeout(() => {
        focusNewItemInput();
      }, 500);
    } else {
      // Update existing junkshop
      const response = await $fetch<JunkshopResponse>(`/junkshop/${junkshop.ulid}`, {
        method: "PUT",
        body: {
          name: junkshop.name,
          contact: junkshop.contact,
          description: junkshop.description,
          address: junkshop.address,
          owner_ulid: auth.user.ulid,
        },
        headers: {
          Authorization: `Bearer ${auth.token}`,
          'Content-Type': 'application/json'
        },
      });

      if (response && response.updated_at) {
        junkshop.updated_at = response.updated_at;
      }

      toast.add({
        title: 'Success',
        description: 'Junkshop updated successfully',
        color: 'green'
      });
    }
  } catch (error) {
    console.error('Error updating junkshop:', error);
    handleApiError(error, 'Failed to update junkshop. Please try again.');
  } finally {
    isLoading.value = false;
  }
};

// Function to add a new item
const addItem = async () => {
  if (newItem.value.trim() === "" || !junkshop.ulid || newItemQuantity.value <= 0) return;

  try {
    const addedItem = await $fetch(`/junkshop/${junkshop.ulid}/items`, {
      method: "POST",
      body: {
        name: newItem.value,
        quantity: newItemQuantity.value.toString()
      },
      headers: {
        Authorization: `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      },
    });

    console.log("Added item response:", addedItem);

    // Instead of manually adding to the array, refresh the items
    // This ensures we have the correct data structure with pivot info
    await fetchItems(junkshop.ulid);

    newItem.value = "";
    newItemQuantity.value = 1; // Reset to default 1 kg

    toast.add({
      title: 'Success',
      description: 'Item added successfully',
      color: 'green'
    });

    // Re-focus the input for quick addition of multiple items
    focusNewItemInput();
  } catch (error) {
    console.error('Error adding item:', error);
    handleApiError(error, 'Failed to add item. Please try again.');
  }
};

// Function to handle delete confirmation
const confirmDeleteItem = (item: any) => {
  itemToDelete.value = item;
  showDeleteConfirmation.value = true;
};

// Function to proceed with item deletion after confirmation
const confirmItemDeletion = async () => {
  if (itemToDelete.value && itemToDelete.value.id) {
    await deleteItem(itemToDelete.value.id);
    itemToDelete.value = null;
  }
};

// Function to delete an item
const deleteItem = async (itemId: number) => {
  if (!junkshop.ulid) return;

  try {
    console.log(`Deleting item with ID: ${itemId}`);

    // Use the main item ID for deletion
    await $fetch(`/junkshop/${junkshop.ulid}/items/${itemId}`, {
      method: "DELETE",
      headers: {
        Authorization: `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      }
    });

    toast.add({
      title: 'Success',
      description: 'Item deleted successfully',
      color: 'green'
    });

    // Directly fetch items for this junkshop to ensure we're up to date
    await fetchItems(junkshop.ulid);
  } catch (error) {
    console.error('Error deleting item:', error);
    handleApiError(error, 'Failed to delete item. Please try again.');
  }
};

// Function to edit an item
const editItem = (item: any) => {
  // Set the editing item ID using the main item ID, not the pivot ID
  editingItemId.value = item.id;
  editingItemName.value = item.name;
  // Get quantity from the item's quantity field which we set in fetchItems
  editingItemQuantity.value = parseFloat(item.quantity) || 0;

  // Focus the edit input after rendering
  nextTick(() => {
    if (editItemInput.value) {
      editItemInput.value.focus();
    }
  });
};

// Function to save an edited item
const saveItem = async (itemId: number) => {
  if (!junkshop.ulid || editingItemQuantity.value < 0) return;

  try {
    console.log("Saving item with ID:", itemId, "Quantity:", editingItemQuantity.value);

    const updatedItem = await $fetch(`/junkshop/${junkshop.ulid}/items/${itemId}`, {
      method: "PUT",
      body: {
        name: editingItemName.value,
        quantity: editingItemQuantity.value.toString()
      },
      headers: {
        Authorization: `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      },
    });

    console.log("API Response:", updatedItem);

    // Update the item in the local items array
    const index = items.value.findIndex((item) => item.id === itemId);
    if (index !== -1) {
      // Preserve pivot and other data while updating quantity and name
      items.value[index] = {
        ...items.value[index],
        name: editingItemName.value,
        quantity: editingItemQuantity.value.toString(),
        pivot: {
          ...items.value[index].pivot,
          quantity: editingItemQuantity.value.toString()
        }
      };
    }

    // Reset editing state
    editingItemId.value = null;
    editingItemName.value = "";
    editingItemQuantity.value = 0;

    toast.add({
      title: 'Success',
      description: 'Item updated successfully',
      color: 'green'
    });

    // Refresh the items list to get the latest data
    await fetchItems(junkshop.ulid);
  } catch (error) {
    console.error('Error updating item:', error);
    handleApiError(error, 'Failed to update item. Please try again.');
  }
};

// Function to cancel editing an item
const cancelEdit = () => {
  editingItemId.value = null;
  editingItemName.value = "";
};

// Bid Management Functions
// Function to fetch all bids for the junkshop
// Function to fetch all bids for the junkshop
const fetchBids = async (junkshopUlid?: string) => {
  // Use the provided ULID or fall back to the junkshop.ulid from state
  const ulid = junkshopUlid || junkshop.ulid;

  if (!ulid) return;

  try {
    isBidLoading.value = true;
    console.log("Fetching bids for junkshop:", ulid);

    const bidsData = await $fetch<any[]>(`/junkshop/${ulid}/bids`, {
      headers: {
        Authorization: `Bearer ${auth.token}`
      }
    });

    console.log("Raw bids data received:", bidsData);
    pendingBids.value = Array.isArray(bidsData) ? bidsData : [];
    console.log("Processed bids:", pendingBids.value);
  } catch (error) {
    console.error('Error fetching bids:', error);
    handleApiError(error, 'Failed to load bids. Please try again.');
  } finally {
    isBidLoading.value = false;
  }
};

// Function to open bid creation modal
const createBidForItem = (item: any) => {
  selectedItem.value = item;
  bidQuantity.value = 1; // Default to 1 kg
  bidPricePerKg.value = 0;
  bidNotes.value = '';
  showBidModal.value = true;
};

// Function to submit a new bid
const submitBid = async () => {
  if (!junkshop.ulid || !selectedItem.value || bidQuantity.value <= 0 || bidPricePerKg.value <= 0) {
    toast.add({
      title: 'Error',
      description: 'Please enter valid quantity and price per kg',
      color: 'red'
    });
    return;
  }

  try {
    isBidLoading.value = true;

    const newBid = await $fetch(`/junkshop/${junkshop.ulid}/bids`, {
      method: "POST",
      body: {
        ulid: generateULID(), // Generate proper ULID format
        item_id: selectedItem.value.id,
        quantity: bidQuantity.value,
        price_per_kg: bidPricePerKg.value,
        notes: bidNotes.value,
        status: 'pending'
      },
      headers: {
        Authorization: `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      },
    });

    // Add new bid to the list
    pendingBids.value.push(newBid);

    // Close modal and reset values
    showBidModal.value = false;
    selectedItem.value = null;
    bidQuantity.value = 0;
    bidPricePerKg.value = 0;
    bidNotes.value = '';

    toast.add({
      title: 'Success',
      description: 'Bid created successfully',
      color: 'green'
    });
  } catch (error) {
    console.error('Error creating bid:', error);
    handleApiError(error, 'Failed to create bid. Please try again.');
  } finally {
    isBidLoading.value = false;
  }
};

// Function to cancel bid creation
const cancelBid = () => {
  showBidModal.value = false;
  selectedItem.value = null;
  bidQuantity.value = 0;
  bidPricePerKg.value = 0;
  bidNotes.value = '';
};

// Function to view bid details
const viewBidDetails = (bid: any) => {
  selectedBid.value = bid;
  showBidDetailsModal.value = true;
};

// Function to close bid details modal
const closeBidDetails = () => {
  showBidDetailsModal.value = false;
  selectedBid.value = null;
};

// Function to update bid status
const updateBidStatus = async (bidId: string, newStatus: string) => {
  if (!junkshop.ulid) return;

  try {
    isBidLoading.value = true;

    const updatedBid = await $fetch(`/junkshop/${junkshop.ulid}/bids/${bidId}`, {
      method: "PUT",
      body: {
        status: newStatus
      },
      headers: {
        Authorization: `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      },
    });

    // Update the bid in the list
    const index = pendingBids.value.findIndex(bid => bid.id === bidId);
    if (index !== -1) {
      pendingBids.value[index] = updatedBid;
    }

    // Close details modal
    closeBidDetails();

    toast.add({
      title: 'Success',
      description: `Bid ${newStatus === 'accepted' ? 'accepted' : 'rejected'} successfully`,
      color: 'green'
    });
  } catch (error) {
    console.error('Error updating bid status:', error);
    handleApiError(error, 'Failed to update bid status. Please try again.');
  } finally {
    isBidLoading.value = false;
  }
};
</script>

<style scoped>
/* Add subtle animations to cards for a more interactive feel */
.u-card {
  transition: all 0.3s ease;
}

.u-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Transition animations for the item list */
.items-list-enter-active,
.items-list-leave-active {
  transition: all 0.3s ease;
}

.items-list-enter-from,
.items-list-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

/* Custom scrollbar styling */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.5);
  border-radius: 20px;
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(75, 85, 99, 0.5);
}
</style>