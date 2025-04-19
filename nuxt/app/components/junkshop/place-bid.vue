<template>
  <div>
    <UButton
      color="teal"
      variant="soft"
      @click="showBidForm = true"
    >
      Place Bid
    </UButton>
    
    <!-- Bid Form Modal -->
    <UModal v-model="showBidForm">
      <UCard>
        <template #header>
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-700 dark:text-gray-200">
              Place Bid on {{ materialName }}
            </h3>
            <UButton 
              color="gray" 
              variant="ghost" 
              icon="i-heroicons-x-mark" 
              @click="showBidForm = false" 
              size="sm"
              square
            />
          </div>
        </template>
        
        <UForm :state="bidForm" class="space-y-4" @submit="submitBid">
          <UFormGroup label="Your Junkshop" name="junkshop_id" required>
            <USelect 
              v-model="bidForm.junkshop_id" 
              :options="junkshopOptions"
              option-attribute="label"
              value-attribute="value"
              placeholder="Select your junkshop"
              required
            />
          </UFormGroup>
          
          <div class="grid grid-cols-2 gap-4">
            <UFormGroup label="Offered Price (₱/kg)" name="offered_price" required>
              <UInput 
                v-model="bidForm.offered_price" 
                type="number" 
                min="0.01" 
                step="0.01"
                required
              />
            </UFormGroup>
            
            <UFormGroup label="Quantity (kg)" name="offered_quantity" required>
              <UInput 
                v-model="bidForm.offered_quantity" 
                type="number" 
                min="0.01" 
                step="0.01"
                required
              />
            </UFormGroup>
          </div>
          
          <UFormGroup label="Material Grade" name="grade">
            <USelect 
              v-model="bidForm.grade" 
              :options="gradeOptions"
              option-attribute="label"
              value-attribute="value"
              placeholder="Select material grade (optional)"
            />
          </UFormGroup>
          
          <UFormGroup label="Expiry Date" name="expiry_date">
            <UInput 
              v-model="bidForm.expiry_date" 
              type="date" 
              :min="minDate"
            />
          </UFormGroup>
          
          <UFormGroup label="Message (Optional)" name="message">
            <UTextarea 
              v-model="bidForm.message" 
              placeholder="Add any additional details about your bid"
              :rows="3"
            />
          </UFormGroup>
          
          <div class="flex justify-end gap-2 mt-6">
            <UButton
              color="gray"
              variant="ghost"
              @click="showBidForm = false"
            >
              Cancel
            </UButton>
            
            <UButton
              type="submit"
              color="teal"
              :loading="isSubmitting"
            >
              Submit Bid
            </UButton>
          </div>
        </UForm>
      </UCard>
    </UModal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';

const props = defineProps({
  wantedMaterialId: {
    type: String,
    required: true
  },
  materialName: {
    type: String,
    default: 'Material'
  },
  merchantInfo: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['bid-placed']);

const toast = useToast();
const showBidForm = ref(false);
const isSubmitting = ref(false);
const junkshopOptions = ref([]);

// Bid form state
const bidForm = reactive({
  junkshop_id: '',
  offered_price: '',
  offered_quantity: '',
  grade: null,
  expiry_date: '',
  message: ''
});

// Computed property for minimum date (tomorrow)
const minDate = computed(() => {
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  return tomorrow.toISOString().split('T')[0];
});

// Grade options
const gradeOptions = [
  { label: 'Grade A (Premium)', value: 'A' },
  { label: 'Grade B (Standard)', value: 'B' },
  { label: 'Grade C (Basic)', value: 'C' }
];

// Fetch user's junkshops
const fetchJunkshops = async () => {
  try {
    const response = await $fetch('/user/junkshops');
    junkshopOptions.value = response.map(junkshop => ({
      label: junkshop.name,
      value: junkshop.ulid
    }));
    
    // If user has only one junkshop, preselect it
    if (junkshopOptions.value.length === 1) {
      bidForm.junkshop_id = junkshopOptions.value[0].value;
    }
  } catch (error) {
    console.error('Failed to fetch junkshops:', error);
    toast.add({
      title: 'Error',
      description: 'Failed to load your junkshops.',
      color: 'red'
    });
  }
};

// Submit bid
const submitBid = async () => {
  try {
    isSubmitting.value = true;
    
    const response = await $fetch(`/material-bids/wanted-material/${props.wantedMaterialId}`, {
      method: 'POST',
      body: bidForm
    });
    
    toast.add({
      title: 'Success',
      description: 'Your bid has been placed successfully.',
      color: 'green'
    });
    
    // Reset form
    bidForm.offered_price = '';
    bidForm.offered_quantity = '';
    bidForm.grade = null;
    bidForm.expiry_date = '';
    bidForm.message = '';
    
    showBidForm.value = false;
    
    // Emit event to parent component
    emit('bid-placed', response.bid);
  } catch (error) {
    console.error('Failed to place bid:', error);
    toast.add({
      title: 'Error',
      description: error.response?.data?.message || 'Failed to place bid.',
      color: 'red'
    });
  } finally {
    isSubmitting.value = false;
  }
};

// Fetch junkshops on component mount
onMounted(() => {
  fetchJunkshops();
});
</script>
