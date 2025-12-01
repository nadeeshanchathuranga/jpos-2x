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
              class="w-full max-w-md p-6 overflow-hidden text-left align-middle transition-all transform bg-gray-900 shadow-xl rounded-2xl border-2 border-red-500"
            >
              <DialogTitle
                as="h3"
                class="text-lg font-medium leading-6 text-white"
              >
                Delete Expense
              </DialogTitle>

              <div class="mt-4">
                <p class="text-sm text-gray-300">
                  Are you sure you want to delete the expense
                  <span class="font-semibold text-white">"{{ expense?.title }}"</span>
                  with amount
                  <span class="font-semibold text-white">Rs. {{ formatAmount(expense?.amount) }}</span>?
                  This action cannot be undone.
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
                  type="button"
                  @click="deleteExpense"
                  :disabled="form.processing"
                  class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 disabled:opacity-50"
                >
                  {{ form.processing ? 'Deleting...' : 'Delete' }}
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

const props = defineProps({
  show: Boolean,
  expense: Object,
});

const emit = defineEmits(['close']);

const form = useForm({});

const closeModal = () => {
  emit('close');
};

const deleteExpense = () => {
  form.delete(route('expenses.destroy', props.expense.id), {
    onSuccess: () => {
      closeModal();
    },
  });
};

const formatAmount = (amount) => {
  if (!amount) return '0.00';
  return parseFloat(amount).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
};
</script>
