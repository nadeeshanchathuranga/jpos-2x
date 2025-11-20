<template>
  <Modal :show="open" @close="closeModal" max-width="md">
    <div class="p-6 bg-gray-900">
      <h2 class="mb-4 text-2xl font-bold text-white">Delete Product</h2>
      <p class="mb-6 text-gray-300" v-if="product">
        Are you sure you want to delete the product
        <span class="font-bold text-white">"{{ product.name }}"</span>
        <span v-if="product.barcode">(Barcode: {{ product.barcode }})</span>?
        This action cannot be undone.
      </p>

      <div class="flex justify-end space-x-3">
        <button
          type="button"
          @click="closeModal"
          class="px-4 py-2 text-white bg-gray-600 rounded hover:bg-gray-700"
        >
          Cancel
        </button>
        <button
          @click="deleteProduct"
          :disabled="form.processing || !product"
          class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700 disabled:opacity-50"
        >
          {{ form.processing ? 'Deleting...' : 'Delete' }}
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import { watch } from "vue";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
  open: Boolean,
  product: Object,
});

const emit = defineEmits(["update:open"]);

const form = useForm({});

// Watch for modal open state changes
watch(() => props.open, (newVal) => {
  console.log('Delete modal open state:', newVal);
  console.log('Product data:', props.product);
});

const deleteProduct = () => {
  if (!props.product || !props.product.id) {
    console.error('No product selected for deletion');
    return;
  }

  console.log('Deleting product:', props.product.id);

  form.delete(route("products.destroy", props.product.id), {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Product deleted successfully');
      closeModal();
      form.reset();
    },
    onError: (errors) => {
      console.error("Delete failed:", errors);
    },
  });
};

const closeModal = () => {
  form.reset();
  emit("update:open", false);
};
</script>
