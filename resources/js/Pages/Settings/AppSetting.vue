
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
          <h1 class="text-3xl font-bold text-white">App Settings</h1>
        </div>
      </div>

      <!-- Settings Form Container -->
      <div class="bg-dark border-4 border-accent rounded-lg p-6">
        <form @submit.prevent="submit">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- App Name Field (Required) -->
            <div class="md:col-span-2">
              <label class="block mb-2 text-sm font-medium text-white">
                App Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.app_name"
                type="text"
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
                placeholder="Enter application name"
              />
              <!-- Validation Error Display -->
              <p v-if="form.errors.app_name" class="mt-1 text-sm text-red-500">
                {{ form.errors.app_name }}
              </p>
            </div>

            <!-- App Logo Upload Field with Preview -->
            <div class="md:col-span-2">
              <label class="block mb-2 text-sm font-medium text-white">
                App Logo
              </label>
              <div class="flex items-center gap-4">
                <!-- File Input for Logo Upload -->
                <input
                  type="file"
                  @change="handleLogoUpload"
                  accept="image/*"
                  class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700"
                />
                <!-- Logo Preview: Shows either new preview or existing logo -->
                <div v-if="currentLogo || logoPreview" class="flex-shrink-0">
                  <img
                    :src="logoPreview || `/storage/${currentLogo}`"
                    alt="App logo preview"
                    class="h-16 w-16 object-contain border border-gray-700 rounded"
                  />
                </div>
              </div>
              <!-- File Format Guidelines -->
              <p class="mt-1 text-xs text-gray-400">
                Accepted formats: JPG, PNG, GIF (Max size: 2MB)
              </p>
              <!-- Validation Error Display -->
              <p v-if="form.errors.app_logo" class="mt-1 text-sm text-red-500">
                {{ form.errors.app_logo }}
              </p>
            </div>

            <!-- App Icon Upload Field with Preview -->
            <div class="md:col-span-2">
              <label class="block mb-2 text-sm font-medium text-white">
                App Icon
              </label>
              <div class="flex items-center gap-4">
                <!-- File Input for Icon Upload -->
                <input
                  type="file"
                  @change="handleIconUpload"
                  accept="image/*"
                  class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700"
                />
                <!-- Icon Preview: Shows either new preview or existing icon -->
                <div v-if="currentIcon || iconPreview" class="flex-shrink-0">
                  <img
                    :src="iconPreview || `/storage/${currentIcon}`"
                    alt="App icon preview"
                    class="h-16 w-16 object-contain border border-gray-700 rounded"
                  />
                </div>
              </div>
              <!-- File Format Guidelines -->
              <p class="mt-1 text-xs text-gray-400">
                Accepted formats: JPG, PNG, GIF, ICO (Max size: 2MB)
              </p>
              <!-- Validation Error Display -->
              <p v-if="form.errors.app_icon" class="mt-1 text-sm text-red-500">
                {{ form.errors.app_icon }}
              </p>
            </div>

            <!-- App Footer Text Field -->
            <div class="md:col-span-2">
              <label class="block mb-2 text-sm font-medium text-white">
                App Footer
              </label>
              <textarea
                v-model="form.app_footer"
                rows="3"
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter footer text (e.g., Copyright © 2025 Your Company)"
              ></textarea>
              <!-- Validation Error Display -->
              <p v-if="form.errors.app_footer" class="mt-1 text-sm text-red-500">
                {{ form.errors.app_footer }}
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
 * App Settings Component Script
 * 
 * Manages the application settings form including file uploads for logo and icon
 * Uses Inertia.js form helper for seamless form submission with file handling
 */

import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { logActivity } from '@/composables/useActivityLog';

/**
 * Component Props
 * @property {Object} appSetting - Existing app settings from database (nullable)
 */
const props = defineProps({
  appSetting: {
    type: Object,
    default: null,
  },
});

/**
 * Reactive State Variables
 * 
 * logoPreview: Stores base64 preview of newly selected logo
 * iconPreview: Stores base64 preview of newly selected icon
 * currentLogo: Stores path of existing logo from database
 * currentIcon: Stores path of existing icon from database
 */
const logoPreview = ref(null);
const iconPreview = ref(null);
const currentLogo = ref(null);
const currentIcon = ref(null);

/**
 * Inertia Form Instance
 * Handles form data, validation errors, and submission state
 */
const form = useForm({
  app_name: '',
  app_logo: null,
  app_icon: null,
  app_footer: '',
});

/**
 * Handle Logo File Upload
 * Reads selected file and creates a preview using FileReader API
 * 
 * @param {Event} event - File input change event
 */
const handleLogoUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.app_logo = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      logoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

/**
 * Handle Icon File Upload
 * Reads selected file and creates a preview using FileReader API
 * 
 * @param {Event} event - File input change event
 */
const handleIconUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.app_icon = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      iconPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

/**
 * Submit Form Handler
 * Posts form data to backend including file uploads
 * On success: clears previews, updates current images, resets file inputs
 */
const submit = () => {
  form.post(route('settings.app.store'), {
    preserveScroll: true,
    onSuccess: async (page) => {
      await logActivity('update', 'app_settings', {
        app_name: form.app_name,
        has_logo: form.app_logo !== null,
        has_icon: form.app_icon !== null
      });
      // Clear preview images
      logoPreview.value = null;
      iconPreview.value = null;
      
      // Update current images with newly saved ones from response
      if (page.props.appSetting && page.props.appSetting.app_logo) {
        currentLogo.value = page.props.appSetting.app_logo;
      }
      if (page.props.appSetting && page.props.appSetting.app_icon) {
        currentIcon.value = page.props.appSetting.app_icon;
      }
      
      // Reset file input elements
      const fileInputs = document.querySelectorAll('input[type="file"]');
      fileInputs.forEach(input => input.value = '');
      
      // Clear form file references
      form.app_logo = null;
      form.app_icon = null;
    },
  });
};

/**
 * Component Mounted Hook
 * Populates form with existing settings data when component loads
 */
onMounted(() => {
  if (props.appSetting) {
    form.app_name = props.appSetting.app_name || '';
    form.app_footer = props.appSetting.app_footer || '';
    currentLogo.value = props.appSetting.app_logo || null;
    currentIcon.value = props.appSetting.app_icon || null;
  }
});
</script>
