<template>
  <Modal :show="open" @close="closeModal" max-width="md">
    <div class="p-6 bg-gray-50">
      <!-- Modal Title -->
      <h2 class="mb-4 text-2xl font-bold text-gray-900">
        {{ por?.deleted_at ? 'Restore Purchase Order Request' : 'Delete Purchase Order Request' }}
      </h2>
      
      <!-- Restore Info -->
      <div v-if="por?.deleted_at" class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
        <p class="text-sm text-yellow-800">
          <strong>Info:</strong> This POR was marked as <span class="font-semibold">INACTIVE</span> on {{ formatDate(por.deleted_at) }}.
        </p>
        <p class="mt-2 text-sm text-yellow-800">You can restore it to active status.</p>
      </div>

      <!-- Delete Warning -->
      <div v-else class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
        <p class="text-sm text-yellow-800">
          <strong>Note:</strong> This will <strong>mark</strong> the POR as <span class="font-semibold">INACTIVE</span> (soft-delete). Related products will be preserved.
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end space-x-3">
        <button
          type="button"
          @click="closeModal"
          class="px-6 py-2.5 rounded-full font-medium text-sm bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 hover:border-gray-300 transition-all duration-200"
        >
          Cancel
        </button>
        <button
          v-if="por?.deleted_at"
          @click="confirmRestore"
          :disabled="isProcessing"
          class="px-6 py-2.5 rounded-full font-medium text-sm bg-green-600 text-white hover:bg-green-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ isProcessing ? 'Processing...' : 'Restore' }}
        </button>
        <button
          v-else
          @click="confirmDelete"
          :disabled="isProcessing || por?.status === 'inactive'"
          class="px-6 py-2.5 rounded-full font-medium text-sm bg-red-600 text-white hover:bg-red-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ isProcessing ? 'Processing...' : 'Mark Inactive' }}
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Modal from "@/Components/Modal.vue";

const props = defineProps({
  open: Boolean,
  por: Object
});

const emit = defineEmits(['update:open']);

const isProcessing = ref(false);

const closeModal = () => {
  emit('update:open', false);
};

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const confirmDelete = () => {
  if (!props.por) return;

  isProcessing.value = true;

  router.delete(`/purchase-order-requests/${props.por.id}`, {
    onSuccess: () => {
      isProcessing.value = false;
      emit('update:open', false);
      router.reload();
    },
    onError: () => {
      isProcessing.value = false;
    }
  });
};

const confirmRestore = () => {
  if (!props.por || !props.por.deleted_at) return;

  isProcessing.value = true;

  router.post(`/purchase-order-requests/${props.por.id}/restore`, {}, {
    onSuccess: () => {
      isProcessing.value = false;
      emit('update:open', false);
      router.reload();
    },
    onError: () => {
      isProcessing.value = false;
    }
  });
};

</script>

<style scoped>
/* Tailwind CSS handles all styling */
</style>
