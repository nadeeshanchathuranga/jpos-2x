<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75">
    <div class="w-full max-w-md p-6 bg-gray-900 border-2 border-red-600 rounded-lg">
      <h2 class="text-xl font-bold text-white mb-4">Delete Purchase Order Request</h2>
      
      <div v-if="por?.status === 'inactive'" class="mb-4 p-3 bg-gray-800 border border-gray-600 rounded text-gray-200 text-sm">
        <p><strong>Info:</strong> This POR is already marked as <span class="font-semibold">INACTIVE</span>.</p>
      </div>

      <div v-else class="mb-4 p-3 bg-yellow-900 border border-yellow-600 rounded text-yellow-200 text-sm">
        <p><strong>Note:</strong> This will <strong>mark</strong> the POR as <span class="font-semibold">INACTIVE</span> (soft-delete). Related products will be preserved.</p>
      </div>

      <div class="flex justify-end space-x-4">
        <button
          @click="$emit('update:open', false)"
          class="px-4 py-2 text-white bg-gray-600 rounded hover:bg-gray-700"
        >
          Cancel
        </button>
        <button
          @click="confirmDelete"
          :disabled="isDeleting || por?.status === 'inactive'"
          class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ isDeleting ? 'Processing...' : 'Mark Inactive' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  open: Boolean,
  por: Object
});

const emit = defineEmits(['update:open']);

const isDeleting = ref(false);

const confirmDelete = () => {
  if (!props.por || props.por.status === 'active') return;

  isDeleting.value = true;
  router.delete(route('purchase-order-requests.destroy', props.por.id), {
    onSuccess: () => {
      isDeleting.value = false;
      emit('update:open', false);
      // Reload the page data so the list reflects the updated status
      router.reload();
    },
    onError: () => {
      isDeleting.value = false;
    }
  });
};
</script>

<style scoped>
/* Tailwind CSS handles all styling */
</style>
