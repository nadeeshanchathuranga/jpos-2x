<template>
  <TransitionRoot appear :show="open" as="template">
    <Dialog as="div" @close="closeModal" class="relative z-10">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/25" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex items-center justify-center min-h-full p-4 text-center">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel
              class="w-full max-w-md p-6 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl"
            >
              <DialogTitle
                as="h3"
                class="text-lg font-medium leading-6 text-gray-900"
              >
                Add New Supplier
              </DialogTitle>

            <form @submit.prevent="submit" class="mt-4 space-y-5">


  <!-- Supplier Name -->
  <div class="space-y-1.5">
    <label class="block text-sm font-medium text-gray-800">
      Supplier Name <span class="text-red-500">*</span>
    </label>
    <input
      v-model="form.name"
      type="text"
      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm
             focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
             placeholder:text-gray-400"
      placeholder="e.g. Global Trading Pvt Ltd"
      required
    />
    <p v-if="form.errors.name" class="text-xs text-red-600">
      {{ form.errors.name }}
    </p>
  </div>

  <!-- Email & phone_number in 2-column on desktop -->
  <div class="grid gap-4 md:grid-cols-2">
    <!-- Email -->
    <div class="space-y-1.5">
      <label class="block text-sm font-medium text-gray-800">
        Email
      </label>
      <input
        v-model="form.email"
        type="email"
        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm
               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
               placeholder:text-gray-400"
        placeholder="name@example.com"
      />
      <p v-if="form.errors.email" class="text-xs text-red-600">
        {{ form.errors.email }}
      </p>
    </div>

    <!-- phone_number -->
    <div class="space-y-1.5">
      <label class="block text-sm font-medium text-gray-800">
        phone_number
      </label>
      <input
        v-model="form.phone_number"
        type="text"
        maxlength="10"
        @input="form.phone_number = form.phone_number.replace(/[^0-9]/g, '')"
        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm
               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
               placeholder:text-gray-400"
        placeholder="07XXXXXXXX"
      />
      <p v-if="form.errors.phone_number" class="text-xs text-red-600">
        {{ form.errors.phone_number }}
      </p>
    </div>
  </div>

  <!-- Address -->
  <div class="space-y-1.5">
    <label class="block text-sm font-medium text-gray-800">
      Address
    </label>
    <textarea
      v-model="form.address"
      rows="3"
      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm
             focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
             placeholder:text-gray-400 resize-none"
      placeholder="Street, City, Country"
    ></textarea>
    <p v-if="form.errors.address" class="text-xs text-red-600">
      {{ form.errors.address }}
    </p>
  </div>

  <!-- Status -->
  <div class="space-y-1.5">
    <label class="block text-sm font-medium text-gray-800">
      Status <span class="text-red-500">*</span>
    </label>
    <select
      v-model="form.status"
      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg shadow-sm
             bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
      required
    >
      <option value="1">Active</option>
      <option value="0">Inactive</option>
    </select>
    <p v-if="form.errors.status" class="text-xs text-red-600">
      {{ form.errors.status }}
    </p>
  </div>

  <!-- Actions -->
  <div class="flex items-center justify-end pt-4 space-x-3 border-t border-gray-100">
    <button
      type="button"
      @click="closeModal"
      class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300
             rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-300"
    >
      Cancel
    </button>
    <button
      type="submit"
      :disabled="form.processing"
      class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm
             hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500
             disabled:opacity-60 disabled:cursor-not-allowed"
    >
      {{ form.processing ? 'Creating...' : 'Create Supplier' }}
    </button>
  </div>
</form>

            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { logActivity } from '@/composables/useActivityLog';
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from '@headlessui/vue';

const props = defineProps({
  open: Boolean,
});

const emit = defineEmits(['update:open']);

const form = useForm({
  name: '',
  email: '',
  phone_number: '',
  address: '',
  status: '1',
});

const submit = () => {
  form.post(route('suppliers.store'), {
    onSuccess: async () => {
      // Log create activity
      await logActivity('create', 'suppliers', {
        supplier_name: form.name,
        email: form.email,
        phone_number: form.phone_number,
        address: form.address,
        status: form.status,
      });

      closeModal();
      form.reset();
    },
  });
};

const closeModal = () => {
  emit('update:open', false);
  form.reset();
  form.clearErrors();
};
</script>
