
<template>
  <AppLayout>
    <div class="p-6">
      <!-- Header Section with Back Button and Title -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
          <button
            @click="$inertia.visit(route('dashboard'))"
            class="px-4 py-2 text-white bg-accent rounded hover:bg-accent"
          >
            Back
          </button>
          <h1 class="text-3xl font-bold text-white">Bill Settings</h1>
        </div>
      </div>
      <div class="bg-dark border-4 border-accent rounded-lg p-6">
        <form @submit.prevent="submit" enctype="multipart/form-data">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Bill Logo Upload -->
            <div class="md:col-span-2">
              <label class="block mb-2 text-sm font-medium text-white">
                Bill Logo
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
                    alt="Logo preview"
                    class="h-16 w-16 object-contain border border-gray-700 rounded"
                  />
                </div>
              </div>
              <p class="mt-1 text-xs text-gray-400">
                Accepted formats: JPG, PNG, GIF (Max size: 2MB)
              </p>
              <p v-if="form.errors.logo" class="mt-1 text-sm text-red-500">
                {{ form.errors.logo }}
              </p>
            </div>
            <!-- Company Name -->
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
              <p v-if="form.errors.company_name" class="mt-1 text-sm text-red-500">
                {{ form.errors.company_name }}
              </p>
            </div>
            <!-- Address -->
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
            <!-- Mobile 1 -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">
                Mobile Number 1 <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.mobile_1"
                type="text"
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
              <p v-if="form.errors.mobile_1" class="mt-1 text-sm text-red-500">
                {{ form.errors.mobile_1 }}
              </p>
            </div>
            <!-- Mobile 2 -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">
                Mobile Number 2
              </label>
              <input
                v-model="form.mobile_2"
                type="text"
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <p v-if="form.errors.mobile_2" class="mt-1 text-sm text-red-500">
                {{ form.errors.mobile_2 }}
              </p>
            </div>
            <!-- Email -->
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
            <!-- Website URL -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">
                Website URL
              </label>
              <input
                v-model="form.website_url"
                type="url"
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="https://example.com"
              />
              <p v-if="form.errors.website_url" class="mt-1 text-sm text-red-500">
                {{ form.errors.website_url }}
              </p>
            </div>
            <!-- Footer Description -->
            <div class="md:col-span-2">
              <label class="block mb-2 text-sm font-medium text-white">
                Footer Description
              </label>
              <textarea
                v-model="form.footer_description"
                rows="2"
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              ></textarea>
              <p v-if="form.errors.footer_description" class="mt-1 text-sm text-red-500">
                {{ form.errors.footer_description }}
              </p>
            </div>
            <!-- Print Bill Size -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">
                Print Bill Size <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.print_size"
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option value="58mm">58mm</option>
                <option value="80mm">80mm</option>
                <option value="112mm">112mm</option>
                <option value="210mm">210mm</option>
              </select>
              <p v-if="form.errors.print_size" class="mt-1 text-sm text-red-500">
                {{ form.errors.print_size }}
              </p>
            </div>
          </div>
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
  setting: {
    type: Object,
    default: null,
  },
});

const logoPreview = ref(null);
const currentLogo = ref(null);

const form = useForm({
  company_name: '',
  address: '',
  mobile_1: '',
  mobile_2: '',
  email: '',
  website_url: '',
  footer_description: '',
  print_size: '80mm',
  logo: null,
});

const handleLogoUpload = (event) => {
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

const submit = () => {
  form.post(route('settings.bill.store'), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      logoPreview.value = null;
      if (form.logo) {
        currentLogo.value = form.logo.name;
      }
      const fileInput = document.querySelector('input[type="file"]');
      if (fileInput) fileInput.value = '';
      form.logo = null;
    },
  });
};

onMounted(() => {
  if (props.setting) {
    form.company_name = props.setting.company_name || '';
    form.address = props.setting.address || '';
    form.mobile_1 = props.setting.mobile_1 || '';
    form.mobile_2 = props.setting.mobile_2 || '';
    form.email = props.setting.email || '';
    form.website_url = props.setting.website_url || '';
    form.footer_description = props.setting.footer_description || '';
    form.print_size = props.setting.print_size || '80mm';
    currentLogo.value = props.setting.logo_path || null;
  }
});
</script>
