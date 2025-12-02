<template>
  <div v-if="open" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-gray-900 rounded-lg p-6 w-full max-w-md">
      <h2 class="text-2xl font-bold text-white mb-4">Delete GRN Return</h2>
      <p class="text-gray-300 mb-6">
            Are you sure you want to delete GRN Return <strong>{{ grn.grn?.grn_no || grn.id }}</strong>? This action cannot be undone.
          </p>
      <div class="flex justify-end gap-2">
        <button @click="close" class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">
          Cancel
        </button>
        <button @click="deleteGrn" :disabled="saving" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50">
          <span v-if="!saving">Delete</span>
          <span v-else>Deleting...</span>
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
    grn: Object,
});

const emit = defineEmits(['update:open', 'deleted']);

const saving = ref(false);

const close = () => {
    emit('update:open', false);
};

const deleteGrn = () => {
    if (!props.grn) return;
    saving.value = true;
    router.delete(route('grn-returns.destroy', props.grn.id), {
        onSuccess: () => {
            saving.value = false;
            emit('deleted', props.grn.id);
            close();
        },
        onError: () => {
            saving.value = false;
        }
    });
};
</script>
