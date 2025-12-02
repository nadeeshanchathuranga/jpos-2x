<template>
  <AppLayout>
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
          <button
            @click="$inertia.visit(route('dashboard'))"
            class="px-4 py-2 text-white bg-accent rounded hover:bg-accent"
          >
            Back
          </button>
          <h1 class="text-3xl font-bold text-white">App Settings</h1>
        </div>
      </div>

      <div class="bg-dark border-4 border-accent rounded-lg p-6">
        <form @submit.prevent="submit">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- App Name -->
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
              <p v-if="form.errors.app_name" class="mt-1 text-sm text-red-500">
                {{ form.errors.app_name }}
              </p>
            </div>

            <!-- App Logo -->
            <div class="md:col-span-2">
              <label class="block mb-2 text-sm font-medium text-white">
                App Logo
              </label>
              <div class="flex items-center gap-4">
                <input
                  type="file"
                  @change="handleLogoUpload"
                  accept="image/*"
                  class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700"
                />
                <div v-if="currentLogo || logoPreview" class="flex-shrink-0">
                  <img
                    :src="logoPreview || `/storage/${currentLogo}`"
                    alt="App logo preview"
                    class="h-16 w-16 object-contain border border-gray-700 rounded"
                  />
                </div>
              </div>
              <p class="mt-1 text-xs text-gray-400">
                Accepted formats: JPG, PNG, GIF (Max size: 2MB)
              </p>
              <p v-if="form.errors.app_logo" class="mt-1 text-sm text-red-500">
                {{ form.errors.app_logo }}
              </p>
            </div>

            <!-- App Icon -->
            <div class="md:col-span-2">
              <label class="block mb-2 text-sm font-medium text-white">
                App Icon
              </label>
              <div class="flex items-center gap-4">
                <input
                  type="file"
                  @change="handleIconUpload"
                  accept="image/*"
                  class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700"
                />
                <div v-if="currentIcon || iconPreview" class="flex-shrink-0">
                  <img
                    :src="iconPreview || `/storage/${currentIcon}`"
                    alt="App icon preview"
                    class="h-16 w-16 object-contain border border-gray-700 rounded"
                  />
                </div>
              </div>
              <p class="mt-1 text-xs text-gray-400">
                Accepted formats: JPG, PNG, GIF, ICO (Max size: 2MB)
              </p>
              <p v-if="form.errors.app_icon" class="mt-1 text-sm text-red-500">
                {{ form.errors.app_icon }}
              </p>
            </div>

            <!-- App Footer -->
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
              <p v-if="form.errors.app_footer" class="mt-1 text-sm text-red-500">
                {{ form.errors.app_footer }}
              </p>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end mt-6">
            <button
              type="submit"
              :disabled="form.processing"
              class="px-6 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
            >
              {{ form.processing ? 'Saving...' : 'Save Settings' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  appSetting: {
    type: Object,
    default: null,
  },
});

const logoPreview = ref(null);
const iconPreview = ref(null);
const currentLogo = ref(null);
const currentIcon = ref(null);

const form = useForm({
  app_name: '',
  app_logo: null,
  app_icon: null,
  app_footer: '',
});

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

const submit = () => {
  form.post(route('settings.app.store'), {
    preserveScroll: true,
    onSuccess: (page) => {
      logoPreview.value = null;
      iconPreview.value = null;
      // Update current images with newly saved ones
      if (page.props.appSetting && page.props.appSetting.app_logo) {
        currentLogo.value = page.props.appSetting.app_logo;
      }
      if (page.props.appSetting && page.props.appSetting.app_icon) {
        currentIcon.value = page.props.appSetting.app_icon;
      }
      // Reset file inputs
      const fileInputs = document.querySelectorAll('input[type="file"]');
      fileInputs.forEach(input => input.value = '');
      form.app_logo = null;
      form.app_icon = null;
    },
  });
};

onMounted(() => {
  if (props.appSetting) {
    form.app_name = props.appSetting.app_name || '';
    form.app_footer = props.appSetting.app_footer || '';
    currentLogo.value = props.appSetting.app_logo || null;
    currentIcon.value = props.appSetting.app_icon || null;
  }
});
</script>
