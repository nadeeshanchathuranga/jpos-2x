
<template>
  <AppLayout>
    <!-- Main Container -->
    <div class="p-6">
      <!-- Header Section with Back Button and Title -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
          <!-- Back to Dashboard Button -->
          <button
            @click="$inertia.visit(route('dashboard'))"
            class="px-4 py-2 text-white bg-accent rounded hover:bg-accent"
          >
            Back
          </button>
          <h1 class="text-3xl font-bold text-white">Company Settings</h1>
        </div>
      </div>

      <!-- Settings Form Container -->
      <div class="bg-dark border-4 border-accent rounded-lg p-6">
        <form @submit.prevent="submit">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Company Name Field (Required) -->
            <div class="md:col-span-2">
              <label class="block mb-2 text-sm font-medium text-white">
                Company Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.company_name"
                type="text"
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
              <!-- Validation Error Display -->
              <p v-if="form.errors.company_name" class="mt-1 text-sm text-red-500">
                {{ form.errors.company_name }}
              </p>
            </div>

            <!-- Company Address Field (Multi-line) -->
            <div class="md:col-span-2">
              <label class="block mb-2 text-sm font-medium text-white">
                Address
              </label>
              <textarea
                v-model="form.address"
                rows="3"
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              ></textarea>
              <p v-if="form.errors.address" class="mt-1 text-sm text-red-500">
                {{ form.errors.address }}
              </p>
            </div>

            <!-- Phone Number Field -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">
                Phone
              </label>
              <input
                v-model="form.phone"
                type="text"
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <p v-if="form.errors.phone" class="mt-1 text-sm text-red-500">
                {{ form.errors.phone }}
              </p>
            </div>

            <!-- Email Address Field -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">
                Email
              </label>
              <input
                v-model="form.email"
                type="email"
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">
                {{ form.errors.email }}
              </p>
            </div>

            <!-- Website URL Field -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">
                Website
              </label>
              <input
                v-model="form.website"
                type="url"
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="https://example.com"
              />
              <p v-if="form.errors.website" class="mt-1 text-sm text-red-500">
                {{ form.errors.website }}
              </p>
            </div>

            <!-- Currency Selection Dropdown (Required) -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">
                Currency <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.currency"
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option value="LKR">LKR - Sri Lankan Rupee</option>
                <option value="USD">USD - US Dollar</option>
                <option value="EUR">EUR - Euro</option>
                <option value="GBP">GBP - British Pound</option>
                <option value="INR">INR - Indian Rupee</option>
                <option value="AUD">AUD - Australian Dollar</option>
              </select>
              <p v-if="form.errors.currency" class="mt-1 text-sm text-red-500">
                {{ form.errors.currency }}
              </p>
            </div>

            <!-- Company Logo Upload with Preview -->
            <div class="md:col-span-2">
              <label class="block mb-2 text-sm font-medium text-white">
                Company Logo
              </label>
              <div class="flex items-center gap-4">
                <!-- File Input for Logo Upload -->
                <input
                  type="file"
                  @change="handleFileUpload"
                  accept="image/*"
                  class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700"
                />
                <!-- Logo Preview: Shows either new preview or existing logo -->
                <div v-if="currentLogo || logoPreview" class="flex-shrink-0">
                  <img
                    :src="logoPreview || `/storage/${currentLogo}`"
                    alt="Logo preview"
                    class="h-16 w-16 object-contain border border-gray-700 rounded"
                  />
                </div>
              </div>
              <!-- File Format Guidelines -->
              <p class="mt-1 text-xs text-gray-400">
                Accepted formats: JPG, PNG, GIF (Max size: 2MB)
              </p>
              <!-- Validation Error Display -->
              <p v-if="form.errors.logo" class="mt-1 text-sm text-red-500">
                {{ form.errors.logo }}
              </p>
            </div>
          </div>

          <!-- Form Submit Button -->
          <div class="flex justify-end mt-6">
            <button
              type="submit"
              :disabled="form.processing"
              class="px-6 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
            >
              <!-- Dynamic button text based on processing state -->
              {{ form.processing ? 'Saving...' : 'Save Settings' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
/**
 * Company Information Component Script
 * 
 * Manages company profile settings including logo upload
 * Company name and logo are displayed globally in the navigation bar
 * Uses Inertia.js form helper for seamless form submission with file handling
 */

import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';

/**
 * Component Props
 * @property {Object} companyInfo - Existing company information from database (nullable)
 */
const props = defineProps({
  companyInfo: {
    type: Object,
    default: null,
  },
});

/**
 * Reactive State Variables
 * 
 * logoPreview: Stores base64 preview of newly selected logo
 * currentLogo: Stores path of existing logo from database
 */
const logoPreview = ref(null);
const currentLogo = ref(null);

/**
 * Inertia Form Instance
 * Handles form data, validation errors, and submission state
 */
const form = useForm({
  company_name: '',
  address: '',
  phone: '',
  email: '',
  website: '',
  logo: null,
  currency: 'LKR',
});

/**
 * Handle Logo File Upload
 * Reads selected file and creates a preview using FileReader API
 * This logo will be displayed in the navigation bar after saving
 * 
 * @param {Event} event - File input change event
 */
const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.logo = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      logoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

/**
 * Submit Form Handler
 * Posts form data to backend including file upload
 * On success: clears preview, updates current logo, resets file input
 * Logo will be automatically displayed in navigation via shared props
 */
const submit = () => {
  form.post(route('settings.company.store'), {
    preserveScroll: true,
    onSuccess: (page) => {
      // Clear preview image
      logoPreview.value = null;
      
      // Update current logo with newly saved one from response
      if (page.props.companyInfo && page.props.companyInfo.logo) {
        currentLogo.value = page.props.companyInfo.logo;
      }
      
      // Reset the file input element
      const fileInput = document.querySelector('input[type="file"]');
      if (fileInput) {
        fileInput.value = '';
      }
      
      // Clear form file reference
      form.logo = null;
    },
  });
};

/**
 * Component Mounted Hook
 * Populates form with existing company information when component loads
 */
onMounted(() => {
  if (props.companyInfo) {
    form.company_name = props.companyInfo.company_name || '';
    form.address = props.companyInfo.address || '';
    form.phone = props.companyInfo.phone || '';
    form.email = props.companyInfo.email || '';
    form.website = props.companyInfo.website || '';
    form.currency = props.companyInfo.currency || 'LKR';
    currentLogo.value = props.companyInfo.logo || null;
  }
});
</script>
