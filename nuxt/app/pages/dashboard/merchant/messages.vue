<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Messages</h1>
    
    <UCard class="mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-medium text-gray-700 dark:text-gray-200">Communication Center</h2>
        <UButton
          color="blue"
          variant="soft"
          size="sm"
          icon="i-heroicons-envelope"
          @click="showNewMessageModal = true"
        >
          New Message
        </UButton>
      </div>
      
      <p class="text-gray-600 dark:text-gray-400 mb-4">
        Communicate with junkshops about pickups, material inquiries, and business opportunities.
      </p>
    </UCard>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Contacts sidebar -->
      <UCard class="lg:col-span-1 h-[600px] flex flex-col">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-medium text-gray-700 dark:text-gray-300">Contacts</h3>
          <UBadge color="blue" size="sm">{{ contacts.length }}</UBadge>
        </div>
        
        <div class="relative mb-4">
          <UInput
            v-model="searchQuery"
            placeholder="Search contacts..."
            icon="i-heroicons-magnifying-glass"
            color="blue"
            trailing
            @clear="searchQuery = ''"
          />
        </div>
        
        <div class="overflow-y-auto flex-grow">
          <div v-if="loading" class="flex justify-center py-8">
            <UIcon name="i-heroicons-arrow-path" class="animate-spin text-blue-500 w-6 h-6" />
          </div>
          
          <div v-else-if="filteredContacts.length === 0" class="text-center py-8">
            <UIcon name="i-heroicons-inbox" class="mx-auto text-gray-400 w-12 h-12 mb-2" />
            <p class="text-gray-500 dark:text-gray-400">No contacts found</p>
          </div>
          
          <div v-else>
            <div 
              v-for="contact in filteredContacts" 
              :key="contact.id"
              class="p-3 rounded-lg mb-2 cursor-pointer transition-colors"
              :class="[
                activeContact && activeContact.id === contact.id 
                  ? 'bg-blue-50 dark:bg-blue-900/30' 
                  : 'hover:bg-gray-50 dark:hover:bg-gray-800/50'
              ]"
              @click="selectContact(contact)"
            >
              <div class="flex items-center gap-3">
                <UAvatar
                  :src="contact.avatar ? $storage(contact.avatar) : ''"
                  :alt="contact.name"
                  size="sm"
                  :fallback="contact.name.charAt(0)"
                  color="blue"
                />
                <div>
                  <p class="font-medium text-gray-800 dark:text-gray-200">{{ contact.name }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                    {{ contact.lastMessage || 'No messages yet' }}
                  </p>
                </div>
                <UBadge 
                  v-if="contact.unread" 
                  color="blue" 
                  size="xs"
                  class="ml-auto"
                >
                  {{ contact.unread }}
                </UBadge>
              </div>
            </div>
          </div>
        </div>
      </UCard>
      
      <!-- Message area -->
      <UCard class="lg:col-span-2 h-[600px] flex flex-col">
        <div v-if="!activeContact" class="h-full flex flex-col items-center justify-center text-center p-4">
          <UIcon name="i-heroicons-chat-bubble-left-right" class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4" />
          <h3 class="text-xl font-medium text-gray-700 dark:text-gray-300 mb-2">No Conversation Selected</h3>
          <p class="text-gray-500 dark:text-gray-400 max-w-md">
            Select a contact from the sidebar to view your conversation history or start a new conversation by clicking "New Message".
          </p>
          <UButton
            class="mt-4"
            color="blue"
            @click="showNewMessageModal = true"
            icon="i-heroicons-envelope"
          >
            Start New Conversation
          </UButton>
        </div>
        
        <template v-else>
          <!-- Chat header -->
          <div class="flex items-center justify-between border-b dark:border-gray-700 pb-3">
            <div class="flex items-center gap-3">
              <UAvatar
                :src="activeContact.avatar ? $storage(activeContact.avatar) : ''"
                :alt="activeContact.name"
                size="sm"
                :fallback="activeContact.name.charAt(0)"
                color="blue"
              />
              <div>
                <p class="font-medium text-gray-800 dark:text-gray-200">{{ activeContact.name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ activeContact.status || 'Junkshop Owner' }}
                </p>
              </div>
            </div>
            <div>
              <UDropdown :items="contactActionItems">
                <UButton
                  color="gray"
                  variant="ghost"
                  icon="i-heroicons-ellipsis-vertical"
                  square
                />
              </UDropdown>
            </div>
          </div>
          
          <!-- Chat messages -->
          <div class="flex-grow overflow-y-auto py-4 space-y-3" ref="messagesContainer">
            <div v-if="loadingMessages" class="flex justify-center py-8">
              <UIcon name="i-heroicons-arrow-path" class="animate-spin text-blue-500 w-6 h-6" />
            </div>
            
            <div v-else-if="messages.length === 0" class="text-center py-8">
              <UIcon name="i-heroicons-chat-bubble-left" class="mx-auto text-gray-400 w-12 h-12 mb-2" />
              <p class="text-gray-500 dark:text-gray-400">No messages yet</p>
              <p class="text-gray-500 dark:text-gray-400 text-sm">Start the conversation by sending a message</p>
            </div>
            
            <template v-else>
              <div v-for="(message, index) in messages" :key="index" class="flex">
                <div 
                  class="max-w-[80%] rounded-lg px-4 py-2 break-words"
                  :class="[
                    message.fromMe 
                      ? 'bg-blue-500 text-white ml-auto' 
                      : 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200'
                  ]"
                >
                  <p>{{ message.text }}</p>
                  <p class="text-xs opacity-70 text-right mt-1">
                    {{ formatTimestamp(message.timestamp) }}
                  </p>
                </div>
              </div>
            </template>
          </div>
          
          <!-- Message input -->
          <div class="border-t dark:border-gray-700 pt-3">
            <form @submit.prevent="sendMessage">
              <div class="flex gap-2">
                <UTextarea
                  v-model="newMessage"
                  placeholder="Type a message..."
                  autoresize
                  :rows="1"
                  class="flex-grow"
                  @keydown.enter.exact.prevent="sendMessage"
                />
                <UButton
                  type="submit"
                  color="blue"
                  icon="i-heroicons-paper-airplane"
                  :loading="sendingMessage"
                  :disabled="!newMessage.trim()"
                />
              </div>
            </form>
          </div>
        </template>
      </UCard>
    </div>
    
    <!-- New Message Modal -->
    <UModal v-model="showNewMessageModal">
      <UCard>
        <template #header>
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">New Message</h3>
            <UButton
              color="gray" 
              variant="ghost"
              icon="i-heroicons-x-mark"
              @click="showNewMessageModal = false"
              size="sm"
              square
            />
          </div>
        </template>
        
        <div class="space-y-4">
          <UFormGroup label="Select Junkshop">
            <USelectMenu
              v-model="selectedNewContact"
              :options="availableContacts"
              placeholder="Select a junkshop"
              searchable
              searchablePlaceholder="Search junkshops..."
            />
          </UFormGroup>
          
          <UFormGroup label="Message">
            <UTextarea
              v-model="newContactMessage"
              placeholder="Type your message here..."
              :rows="3"
            />
          </UFormGroup>
        </div>
        
        <template #footer>
          <div class="flex justify-end gap-2">
            <UButton
              color="gray"
              variant="soft"
              @click="showNewMessageModal = false"
            >
              Cancel
            </UButton>
            <UButton
              color="blue"
              @click="startNewConversation"
              :loading="creatingConversation"
              :disabled="!selectedNewContact || !newContactMessage.trim()"
            >
              Send Message
            </UButton>
          </div>
        </template>
      </UCard>
    </UModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';

const toast = useToast();
const loading = ref(true);
const loadingMessages = ref(false);
const sendingMessage = ref(false);
const creatingConversation = ref(false);
const showNewMessageModal = ref(false);

// Contact and message data
const contacts = ref([]);
const messages = ref([]);
const activeContact = ref(null);
const searchQuery = ref('');
const newMessage = ref('');
const selectedNewContact = ref(null);
const newContactMessage = ref('');
const messagesContainer = ref(null);

// Mock data - replace with API calls
const mockContacts = [
  { 
    id: 1, 
    name: 'EcoCycle Junkshop', 
    avatar: null, 
    lastMessage: 'Do you have any copper wire available?',
    unread: 2,
    status: 'Online' 
  },
  { 
    id: 2, 
    name: 'Green Recyclers', 
    avatar: null,
    lastMessage: 'We can collect the materials tomorrow.',
    unread: 0,
    status: 'Last seen 2h ago'
  },
  { 
    id: 3, 
    name: 'MetalWorks Inc', 
    avatar: null, 
    lastMessage: null,
    unread: 0,
    status: 'Offline'
  }
];

const mockMessages = {
  1: [
    { fromMe: false, text: 'Hello! I noticed you\'re interested in scrap metal.', timestamp: new Date(2023, 3, 10, 9, 25) },
    { fromMe: true, text: 'Yes, we buy scrap metal regularly. What types do you collect?', timestamp: new Date(2023, 3, 10, 9, 30) },
    { fromMe: false, text: 'We collect copper, aluminum, and steel primarily.', timestamp: new Date(2023, 3, 10, 9, 32) },
    { fromMe: false, text: 'Do you have any copper wire available?', timestamp: new Date(2023, 3, 10, 9, 33) }
  ],
  2: [
    { fromMe: true, text: 'Hi, I have about 50kg of mixed paper and cardboard. Are you interested?', timestamp: new Date(2023, 3, 9, 14, 10) },
    { fromMe: false, text: 'Yes, we can collect that. What\'s your address?', timestamp: new Date(2023, 3, 9, 14, 15) },
    { fromMe: true, text: '123 Main St, Downtown. When can you come?', timestamp: new Date(2023, 3, 9, 14, 18) },
    { fromMe: false, text: 'We can collect the materials tomorrow.', timestamp: new Date(2023, 3, 9, 14, 20) }
  ],
  3: []
};

// Filter contacts based on search query
const filteredContacts = computed(() => {
  if (!searchQuery.value) {
    return contacts.value;
  }
  return contacts.value.filter(contact => 
    contact.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

// Available contacts for new conversation
const availableContacts = computed(() => {
  const contactIds = new Set(contacts.value.map(c => c.id));
  // This would normally be fetched from an API
  return [
    { value: 4, label: 'Urban Recyclers' },
    { value: 5, label: 'PaperTech Solutions' },
    { value: 6, label: 'PlasticWorks' },
  ].filter(c => !contactIds.has(c.value));
});

// Contact action dropdown items
const contactActionItems = [
  [
    {
      label: 'View Profile',
      icon: 'i-heroicons-user-circle',
      click: () => viewContactProfile()
    },
    {
      label: 'Mark All as Read',
      icon: 'i-heroicons-check-badge',
      click: () => markAllAsRead()
    }
  ],
  [
    {
      label: 'Block',
      icon: 'i-heroicons-no-symbol',
      click: () => blockContact()
    }
  ]
];

// Format timestamp for messages
const formatTimestamp = (timestamp) => {
  if (!timestamp) return '';
  
  const now = new Date();
  const date = new Date(timestamp);
  
  // If same day, show time
  if (date.toDateString() === now.toDateString()) {
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  }
  
  // If within last 7 days, show day name
  const diffDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));
  if (diffDays < 7) {
    return date.toLocaleDateString([], { weekday: 'short' }) + ' ' + 
           date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  }
  
  // Otherwise show date
  return date.toLocaleDateString();
};

// Select a contact and load their messages
const selectContact = async (contact) => {
  activeContact.value = contact;
  loadingMessages.value = true;
  
  try {
    // In a real app, fetch messages from API
    // For now, use mock data
    await new Promise(resolve => setTimeout(resolve, 500)); // Simulate network delay
    messages.value = mockMessages[contact.id] || [];
    
    // Mark messages as read
    if (contact.unread) {
      contact.unread = 0;
    }
  } catch (error) {
    console.error('Error loading messages:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to load messages.',
      color: 'red'
    });
  } finally {
    loadingMessages.value = false;
    // Scroll to bottom of messages
    await nextTick();
    scrollToBottom();
  }
};

// Send a new message
const sendMessage = async () => {
  if (!newMessage.value.trim() || !activeContact.value) return;
  
  sendingMessage.value = true;
  
  try {
    const message = {
      fromMe: true,
      text: newMessage.value.trim(),
      timestamp: new Date()
    };
    
    // In a real app, send to API
    await new Promise(resolve => setTimeout(resolve, 300)); // Simulate network delay
    
    // Add to messages and update last message in contacts
    messages.value.push(message);
    const contactIndex = contacts.value.findIndex(c => c.id === activeContact.value.id);
    if (contactIndex !== -1) {
      contacts.value[contactIndex].lastMessage = message.text;
    }
    
    // Clear input
    newMessage.value = '';
    
    // Scroll to bottom
    await nextTick();
    scrollToBottom();
  } catch (error) {
    console.error('Error sending message:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to send message.',
      color: 'red'
    });
  } finally {
    sendingMessage.value = false;
  }
};

// Start a new conversation
const startNewConversation = async () => {
  if (!selectedNewContact || !newContactMessage.value.trim()) return;
  
  creatingConversation.value = true;
  
  try {
    // In a real app, send to API
    await new Promise(resolve => setTimeout(resolve, 500)); // Simulate network delay
    
    // Create a new contact and message
    const newContact = {
      id: selectedNewContact.value,
      name: availableContacts.value.find(c => c.value === selectedNewContact.value)?.label || 'New Contact',
      avatar: null,
      lastMessage: newContactMessage.value.trim(),
      unread: 0,
      status: 'New conversation'
    };
    
    contacts.value.unshift(newContact);
    mockMessages[newContact.id] = [
      { 
        fromMe: true, 
        text: newContactMessage.value.trim(), 
        timestamp: new Date() 
      }
    ];
    
    // Reset form and close modal
    selectedNewContact.value = null;
    newContactMessage.value = '';
    showNewMessageModal.value = false;
    
    // Select the new contact
    selectContact(newContact);
    
    toast.add({
      title: 'Message Sent',
      description: 'Your message has been sent successfully.',
      color: 'green'
    });
  } catch (error) {
    console.error('Error creating conversation:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to create conversation.',
      color: 'red'
    });
  } finally {
    creatingConversation.value = false;
  }
};

// Contact actions
const viewContactProfile = () => {
  if (!activeContact.value) return;
  toast.add({
    title: 'View Profile',
    description: `Viewing profile of ${activeContact.value.name}`,
    color: 'blue'
  });
};

const markAllAsRead = () => {
  if (!activeContact.value) return;
  activeContact.value.unread = 0;
  toast.add({
    title: 'Marked as Read',
    description: 'All messages marked as read',
    color: 'green'
  });
};

const blockContact = () => {
  if (!activeContact.value) return;
  toast.add({
    title: 'Contact Blocked',
    description: `${activeContact.value.name} has been blocked`,
    color: 'red'
  });
};

// Scroll chat to bottom
const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

// Load contacts on mount
onMounted(async () => {
  try {
    // In a real app, fetch from API
    await new Promise(resolve => setTimeout(resolve, 800)); // Simulate network delay
    contacts.value = mockContacts;
  } catch (error) {
    console.error('Error loading contacts:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to load contacts.',
      color: 'red'
    });
  } finally {
    loading.value = false;
  }
});

// Auto-scroll when new messages are added
watch(messages, () => {
  nextTick(() => {
    scrollToBottom();
  });
}, { deep: true });

// Define the parent layout
definePageMeta({
  layout: 'dashboard'
});
</script>
