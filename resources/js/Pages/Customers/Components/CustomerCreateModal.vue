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
        <div class="fixed inset-0 bg-black/50" />
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
              class="w-full max-w-md p-6 overflow-hidden text-left align-middle transition-all transform bg-gray-800 shadow-xl rounded-2xl"
            >
              <DialogTitle
                as="h3"
                class="text-lg font-medium leading-6 text-white mb-4"
              >
                Add New Customer
              </DialogTitle>

              <form @submit.prevent="submit" class="space-y-4">
                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-300">
                    Customer Name *
                  </label>
                  <input
                    v-model="form.name"
                    type="text"
                    class="w-full px-3 py-2 bg-gray-700 text-white rounded-md focus:ring-2 focus:ring-blue-500"
                    required
                  />
                  <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">
                    {{ form.errors.name }}
                  </p>
                </div>

                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-300">
                    Email
                  </label>
                  <input
                    v-model="form.email"
                    type="email"
                    class="w-full px-3 py-2 bg-gray-700 text-white rounded-md focus:ring-2 focus:ring-blue-500"
                  />
                  <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">
                    {{ form.errors.email }}
                  </p>
                </div>

                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-300">
                    Phone Number
                  </label>
                  <input
                    v-model="form.phone_number"
                    type="text"
                    class="w-full px-3 py-2 bg-gray-700 text-white rounded-md focus:ring-2 focus:ring-blue-500"
                  />
                  <p v-if="form.errors.phone_number" class="mt-1 text-sm text-red-500">
                    {{ form.errors.phone_number }}
                  </p>
                </div>

                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-300">
                    Credit Limit
                  </label>
                  <input
                    v-model="form.credit_limit"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full px-3 py-2 bg-gray-700 text-white rounded-md focus:ring-2 focus:ring-blue-500"
                  />
                  <p v-if="form.errors.credit_limit" class="mt-1 text-sm text-red-500">
                    {{ form.errors.credit_limit }}
                  </p>
                </div>

                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-300">
                    Address
                  </label>
                  <textarea
                    v-model="form.address"
                    rows="3"
                    class="w-full px-3 py-2 bg-gray-700 text-white rounded-md focus:ring-2 focus:ring-blue-500"
                  ></textarea>
                  <p v-if="form.errors.address" class="mt-1 text-sm text-red-500">
                    {{ form.errors.address }}
                  </p>
                </div>

                <div>
                  <label class="block mb-2 text-sm font-medium text-gray-300">
                    Status *
                  </label>
                  <div class="flex items-center space-x-4">
                    <label class="flex items-center space-x-2">
                      <input
                        v-model="form.status"
                        type="radio"
                        value="1"
                        class="text-blue-500 focus:ring-2 focus:ring-blue-500"
                      />
                      <span class="text-gray-300">Active</span>
                    </label>
                    <label class="flex items-center space-x-2">
                      <input
                        v-model="form.status"
                        type="radio"
                        value="0"
                        class="text-blue-500 focus:ring-2 focus:ring-blue-500"
                      />
                      <span class="text-gray-300">Inactive</span>
                    </label>
                  </div>
                </div>

                <div class="flex justify-end space-x-3">
                  <button
                    type="button"
                    @click="closeModal"
                    class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-600 rounded-md hover:bg-gray-500"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-500 disabled:opacity-50"
                  >
                    {{ form.processing ? 'Creating...' : 'Create Customer' }}
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
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from '@headlessui/vue';
import { logActivity } from '@/composables/useActivityLog';

const props = defineProps({
  open: Boolean,
});

const emit = defineEmits(['update:open']);

const form = useForm({
  name: '',
  email: '',
  phone_number: '',
  address: '',
  credit_limit: 0,
  status: '1',
});

const submit = () => {
  form.post(route('customers.store'), {
    onSuccess: async () => {
      await logActivity('create', 'customers', {
        customer_name: form.name,
        contact_person: form.contact_person
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
