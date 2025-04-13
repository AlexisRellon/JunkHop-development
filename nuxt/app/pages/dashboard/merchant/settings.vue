<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Merchant Settings</h1>
    
    <UCard class="mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200">Account Preferences</h2>
        <UBadge color="teal" variant="soft">Merchant</UBadge>
      </div>
      
      <p class="text-gray-600 dark:text-gray-400 mb-4">
        Manage your merchant account settings, notification preferences, and profile information.
      </p>
    </UCard>

    <!-- Settings Tabs -->
    <UTabs :items="tabs" class="mb-6">
      <template #default="{ selectedTabId }">
        <!-- Profile Settings -->
        <div v-if="selectedTabId === 'profile'" class="space-y-6">
          <UCard>
            <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-4">Business Profile</h3>
            
            <div v-if="isLoading" class="py-8 flex justify-center">
              <UIcon name="i-heroicons-arrow-path" class="animate-spin text-teal-500 w-8 h-8" />
            </div>
            
            <div v-else>
              <UForm :state="profileForm" @submit="updateProfile" class="space-y-4">
                <UFormGroup label="Business Name" name="business_name">
                  <UInput v-model="profileForm.business_name" placeholder="Your business name" />
                </UFormGroup>
                
                <UFormGroup label="Address" name="address">
                  <UInput v-model="profileForm.address" placeholder="Business address" />
                </UFormGroup>
                
                <UFormGroup label="Contact Number" name="contact">
                  <UInput v-model="profileForm.contact" placeholder="Contact number" />
                </UFormGroup>
                
                <UFormGroup label="Description" name="description">
                  <UTextarea 
                    v-model="profileForm.description" 
                    placeholder="Describe your business and the materials you're interested in" 
                    :rows="3"
                  />
                </UFormGroup>
                
                <div class="flex justify-end">
                  <UButton
                    type="submit"
                    color="teal"
                    :loading="isUpdating"
                  >
                    Update Profile
                  </UButton>
                </div>
              </UForm>
            </div>
          </UCard>
          
          <UCard>
            <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-4">Profile Image</h3>
            
            <div class="flex items-center gap-6">
              <UAvatar
                :src="profileImage ? profileImage : ''"
                :alt="profileForm.business_name"
                size="xl"
                :fallback="profileForm.business_name?.charAt(0) || 'M'"
                color="teal"
              />
              
              <div class="space-y-2">
                <UButton
                  color="gray"
                  variant="soft"
                  icon="i-heroicons-arrow-up-tray"
                  @click="() => {}"  <!-- This would trigger file selection in a real implementation -->
                >
                  Upload New Image
                </UButton>
                
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  Recommended: Square image, at least 300x300px
                </p>
              </div>
            </div>
          </UCard>
        </div>
        
        <!-- Notifications Settings -->
        <div v-else-if="selectedTabId === 'notifications'" class="space-y-6">
          <UCard>
            <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-4">Notification Preferences</h3>
            
            <div class="space-y-4">
              <div class="flex items-center justify-between py-2 border-b dark:border-gray-700">
                <div>
                  <p class="font-medium text-gray-700 dark:text-gray-300">Email Notifications</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Receive updates via email</p>
                </div>
                <UToggle v-model="notificationPreferences.email" color="teal" />
              </div>
              
              <div class="flex items-center justify-between py-2 border-b dark:border-gray-700">
                <div>
                  <p class="font-medium text-gray-700 dark:text-gray-300">New Connection Requests</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Get notified when junkshops want to connect</p>
                </div>
                <UToggle v-model="notificationPreferences.connectionRequests" color="teal" />
              </div>
              
              <div class="flex items-center justify-between py-2 border-b dark:border-gray-700">
                <div>
                  <p class="font-medium text-gray-700 dark:text-gray-300">New Messages</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Get notified about new messages</p>
                </div>
                <UToggle v-model="notificationPreferences.messages" color="teal" />
              </div>
              
              <div class="flex items-center justify-between py-2">
                <div>
                  <p class="font-medium text-gray-700 dark:text-gray-300">Material Updates</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Get notified about price changes on your materials of interest</p>
                </div>
                <UToggle v-model="notificationPreferences.materialUpdates" color="teal" />
              </div>
            </div>
            
            <template #footer>
              <div class="flex justify-end">
                <UButton
                  color="teal"
                  @click="saveNotificationPreferences"
                  :loading="isSavingPreferences"
                >
                  Save Preferences
                </UButton>
              </div>
            </template>
          </UCard>
        </div>
        
        <!-- Security Settings -->
        <div v-else-if="selectedTabId === 'security'" class="space-y-6">
          <UCard>
            <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-4">Change Password</h3>
            
            <UForm :state="passwordForm" @submit="updatePassword" class="space-y-4">
              <UFormGroup label="Current Password" name="currentPassword">
                <UInput 
                  v-model="passwordForm.currentPassword"
                  type="password"
                  placeholder="Enter your current password"
                />
              </UFormGroup>
              
              <UFormGroup label="New Password" name="newPassword">
                <UInput 
                  v-model="passwordForm.newPassword"
                  type="password"
                  placeholder="Enter your new password"
                />
              </UFormGroup>
              
              <UFormGroup label="Confirm New Password" name="confirmPassword">
                <UInput 
                  v-model="passwordForm.confirmPassword"
                  type="password"
                  placeholder="Confirm your new password"
                />
              </UFormGroup>
              
              <div class="flex justify-end">
                <UButton
                  type="submit"
                  color="teal"
                  :loading="isChangingPassword"
                >
                  Update Password
                </UButton>
              </div>
            </UForm>
          </UCard>
          
          <UCard>
            <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-4">Account Security</h3>
            
            <div class="space-y-4">
              <div class="flex items-center justify-between py-2 border-b dark:border-gray-700">
                <div>
                  <p class="font-medium text-gray-700 dark:text-gray-300">Two-Factor Authentication</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Add an extra layer of security to your account</p>
                </div>
                <UButton
                  color="teal"
                  variant="soft"
                  icon="i-heroicons-shield-check"
                >
                  Setup
                </UButton>
              </div>
              
              <div class="flex items-center justify-between py-2">
                <div>
                  <p class="font-medium text-gray-700 dark:text-gray-300">Active Sessions</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Manage devices where you're logged in</p>
                </div>
                <UButton
                  color="gray"
                  variant="soft"
                  icon="i-heroicons-computer-desktop"
                >
                  View
                </UButton>
              </div>
            </div>
          </UCard>
        </div>
        
        <!-- Preferences Tab -->
        <div v-else-if="selectedTabId === 'preferences'" class="space-y-6">
          <UCard>
            <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200 mb-4">Display Settings</h3>
            
            <div class="space-y-4">
              <div class="flex items-center justify-between py-2 border-b dark:border-gray-700">
                <div>
                  <p class="font-medium text-gray-700 dark:text-gray-300">Dark Mode</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Toggle between light and dark theme</p>
                </div>
                <UToggle v-model="displayPreferences.darkMode" color="teal" />
              </div>
              
              <div class="flex items-center justify-between py-2">
                <div>
                  <p class="font-medium text-gray-700 dark:text-gray-300">Language</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Select your preferred language</p>
                </div>
                <USelectMenu
                  v-model="displayPreferences.language"
                  :options="languageOptions"
                  class="w-40"
                />
              </div>
            </div>
            
            <template #footer>
              <div class="flex justify-end">
                <UButton
                  color="teal"
                  @click="saveDisplayPreferences"
                  :loading="isSavingDisplay"
                >
                  Save Preferences
                </UButton>
              </div>
            </template>
          </UCard>
        </div>
      </template>
    </UTabs>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';

const toast = useToast();
const isLoading = ref(true);
const isUpdating = ref(false);
const isChangingPassword = ref(false);
const isSavingPreferences = ref(false);
const isSavingDisplay = ref(false);
const profileImage = ref(null);

// Tabs configuration
const tabs = [
  { id: 'profile', label: 'Profile', icon: 'i-heroicons-user-circle' },
  { id: 'notifications', label: 'Notifications', icon: 'i-heroicons-bell' },
  { id: 'security', label: 'Security', icon: 'i-heroicons-lock-closed' },
  { id: 'preferences', label: 'Preferences', icon: 'i-heroicons-cog-6-tooth' }
];

// Form states
const profileForm = reactive({
  business_name: '',
  address: '',
  contact: '',
  description: ''
});

const passwordForm = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
});

const notificationPreferences = reactive({
  email: true,
  connectionRequests: true,
  messages: true,
  materialUpdates: false
});

const displayPreferences = reactive({
  darkMode: false,
  language: 'en'
});

// Language options
const languageOptions = [
  { value: 'en', label: 'English' },
  { value: 'es', label: 'Spanish' },
  { value: 'tl', label: 'Tagalog' }
];

// Fetch merchant profile
const fetchProfile = async () => {
  try {
    isLoading.value = true;
    
    // Fetch profile from API
    const response = await $fetch('/api/v1/merchant/profile');
    
    // Populate form with data
    if (response) {
      profileForm.business_name = response.business_name || '';
      profileForm.address = response.address || '';
      profileForm.contact = response.contact || '';
      profileForm.description = response.description || '';
    }
  } catch (error) {
    console.error('Error fetching merchant profile:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to load profile data.',
      color: 'red'
    });
  } finally {
    isLoading.value = false;
  }
};

// Update merchant profile
const updateProfile = async () => {
  try {
    isUpdating.value = true;
    
    // Send update to API
    const response = await $fetch('/api/v1/merchant/profile', {
      method: 'PUT',
      body: profileForm
    });
    
    toast.add({
      title: 'Profile Updated',
      description: 'Your merchant profile has been updated successfully.',
      color: 'green'
    });
  } catch (error) {
    console.error('Error updating profile:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to update profile.',
      color: 'red'
    });
  } finally {
    isUpdating.value = false;
  }
};

// Update password
const updatePassword = async () => {
  try {
    isChangingPassword.value = true;
    
    // Validate passwords match
    if (passwordForm.newPassword !== passwordForm.confirmPassword) {
      toast.add({
        title: 'Error',
        description: 'New passwords do not match.',
        color: 'red'
      });
      return;
    }
    
    // Send password update to API
    await $fetch('/api/v1/account/password', {
      method: 'POST',
      body: {
        current_password: passwordForm.currentPassword,
        password: passwordForm.newPassword,
        password_confirmation: passwordForm.confirmPassword
      }
    });
    
    // Reset form
    passwordForm.currentPassword = '';
    passwordForm.newPassword = '';
    passwordForm.confirmPassword = '';
    
    toast.add({
      title: 'Password Updated',
      description: 'Your password has been changed successfully.',
      color: 'green'
    });
  } catch (error) {
    console.error('Error changing password:', error);
    toast.add({
      title: 'Error',
      description: error.response?.data?.message || 'Failed to change password.',
      color: 'red'
    });
  } finally {
    isChangingPassword.value = false;
  }
};

// Save notification preferences
const saveNotificationPreferences = async () => {
  try {
    isSavingPreferences.value = true;
    
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 800));
    
    toast.add({
      title: 'Preferences Updated',
      description: 'Your notification preferences have been saved.',
      color: 'green'
    });
  } catch (error) {
    console.error('Error saving preferences:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to save notification preferences.',
      color: 'red'
    });
  } finally {
    isSavingPreferences.value = false;
  }
};

// Save display preferences
const saveDisplayPreferences = async () => {
  try {
    isSavingDisplay.value = true;
    
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 800));
    
    toast.add({
      title: 'Display Settings Updated',
      description: 'Your display preferences have been saved.',
      color: 'green'
    });
  } catch (error) {
    console.error('Error saving display settings:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to save display preferences.',
      color: 'red'
    });
  } finally {
    isSavingDisplay.value = false;
  }
};

// Fetch data when component mounts
onMounted(() => {
  fetchProfile();
});

// Define the parent layout
definePageMeta({
  layout: 'dashboard'
});
</script>
