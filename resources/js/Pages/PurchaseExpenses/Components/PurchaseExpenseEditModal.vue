<template>
  <TransitionRoot appear :show="show" as="template">
    <Dialog as="div" @close="closeModal" class="relative z-50">
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
              class="w-full max-w-md p-6 overflow-hidden text-left align-middle transition-all transform bg-gray-900 shadow-xl rounded-2xl border-2 border-blue-500"
            >
              <DialogTitle
                as="h3"
                class="text-lg font-medium leading-6 text-white"
              >
                Edit Expense
              </DialogTitle>

              <form @submit.prevent="submit" class="mt-4">
                <div class="mb-4">
                  <label class="block mb-2 text-sm font-medium text-white">
                    Amount
                  </label>
                  <input
                    v-model="form.amount"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                  />
                  <p v-if="form.errors.amount" class="mt-1 text-sm text-red-500">
                    {{ form.errors.amount }}
                  </p>
                </div>

                <div class="mb-4">
                  <label class="block mb-2 text-sm font-medium text-white">
                    Expense Date
                  </label>
                  <input
                    v-model="form.expense_date"
                    type="date"
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                  />
                  <p v-if="form.errors.expense_date" class="mt-1 text-sm text-red-500">
                    {{ form.errors.expense_date }}
                  </p>
                </div>

                <div class="mb-4">
                  <label class="block mb-2 text-sm font-medium text-white">
                    Payment Type
                  </label>
                  <select
                    v-model="form.payment_type"
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                  >
                    <option value="">Select Payment Type</option>
                    <option value="0">Cash</option>
                    <option value="1">Card</option>
                    <option value="2">Credit</option>
                    <option value="3">Cheque</option>
                  </select>
                  <p v-if="form.errors.payment_type" class="mt-1 text-sm text-red-500">
                    {{ form.errors.payment_type }}
                  </p>
                </div>

                <!-- Reference field for Card and Cheque -->
                <div v-if="form.payment_type === '1' || form.payment_type === '3'" class="mb-4">
                  <label class="block mb-2 text-sm font-medium text-white">
                    Reference <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.reference"
                    type="text"
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :required="form.payment_type === '1' || form.payment_type === '3'"
                    placeholder="Enter card or cheque reference number"
                  />
                  <p v-if="form.errors.reference" class="mt-1 text-sm text-red-500">
                    {{ form.errors.reference }}
                  </p>
                </div>

                <div class="flex justify-end mt-6 space-x-3">
                  <button
                    type="button"
                    @click="closeModal"
                    class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
                  >
                    {{ form.processing ? 'Updating...' : 'Update Expense' }}
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
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { logActivity } from '@/composables/useActivityLog';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

const props = defineProps({
  show: Boolean,
  expense: Object,
});

const emit = defineEmits(['close']);

const form = useForm({
  amount: '',
  expense_date: '',
  payment_type: '',
  reference: '',
});

const closeModal = () => {
  emit('close');
  form.reset();
  form.clearErrors();
};

const submit = () => {
  form.put(route('purchase-expenses.update', props.expense.id), {
    onSuccess: async () => {
      // Log update activity
      await logActivity('update', 'expenses', {
        expense_id: props.expense.id,
        amount: form.amount,
        old_amount: props.expense.amount,
        expense_date: form.expense_date,
        payment_type: form.payment_type,
      });
      
      closeModal();
    },
  });
};

watch(() => props.expense, (expense) => {
  if (expense) {
    form.amount = expense.amount;
    form.expense_date = expense.expense_date;
    form.payment_type = expense.payment_type.toString();
    form.reference = expense.reference || '';
  }
}, { immediate: true });
</script>
