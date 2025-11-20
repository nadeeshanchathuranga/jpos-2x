<template>
  <AppLayout>
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-white">Products</h1>
        <button
          @click="openCreateModal"
          class="px-6 py-2 text-white bg-blue-600 rounded hover:bg-blue-700"
        >
          Add Product
        </button>
      </div>
 
      <div class="overflow-hidden bg-black border-4 border-blue-600 rounded-lg">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-white">
            <thead class="bg-blue-600">
              <tr>
                <th class="px-6 py-3">ID</th>
                <th class="px-6 py-3">Barcode</th>
                <th class="px-6 py-3">Name</th>
                <th class="px-6 py-3">Code</th>
                <th class="px-6 py-3">Brand</th>
                <th class="px-6 py-3">Category</th>
                <th class="px-6 py-3">Purchase Price</th>
                <th class="px-6 py-3">Selling Price</th>
                <th class="px-6 py-3">Qty</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(product, index) in products"
                :key="product.id"
                class="border-b border-gray-700 hover:bg-gray-900"
              >
                <td class="px-6 py-4">{{ index + 1 }}</td>
                <td class="px-6 py-4">{{ product.barcode }}</td>
                <td class="px-6 py-4">{{ product.name }}</td>
                <td class="px-6 py-4">{{ product.code || 'N/A' }}</td>
                <td class="px-6 py-4">{{ product.brand?.name || 'N/A' }}</td>
                <td class="px-6 py-4">{{ product.category?.name || 'N/A' }}</td>
                <td class="px-6 py-4">{{ product.purchase_price || '0.00' }}</td>
                <td class="px-6 py-4">{{ product.selling_price || '0.00' }}</td>
                <td class="px-6 py-4">{{ product.qty }}</td>
                <td class="px-6 py-4">
                  <span
                    :class="{
                      'bg-red-500 text-white px-3 py-1 rounded': product.status == 0,
                      'bg-green-500 text-white px-3 py-1 rounded': product.status == 1,
                      'bg-blue-500 text-white px-3 py-1 rounded': product.status == 2
                    }"
                  >
                    {{ product.status == 1 ? 'Active' : product.status == 0 ? 'Inactive' : 'Default' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <button
                    @click="openViewModal(product)"
                    class="px-4 py-2 mr-2 text-white bg-green-500 rounded hover:bg-green-600"
                  >
                    View
                  </button>
                  <button
                    @click="openEditModal(product)"
                    :disabled="product.status == 2"
                    :class="[
                      'px-4 py-2 mr-2 text-white rounded',
                      product.status == 2
                        ? 'bg-gray-500 cursor-not-allowed opacity-50'
                        : 'bg-yellow-500 hover:bg-yellow-600'
                    ]"
                  >
                    Edit
                  </button>
                  <button
                    @click="openDuplicateModal(product)"
                    class="px-4 py-2 mr-2 text-white bg-blue-500 rounded hover:bg-blue-600"
                  >
                    Duplicate
                  </button>
                  <button
                    @click="openDeleteModal(product)"
                    :disabled="product.status == 2 || product.status == 0"
                    :class="[
                      'px-4 py-2 text-white rounded',
                      product.status == 2 || product.status == 0
                        ? 'bg-gray-500 cursor-not-allowed opacity-50'
                        : 'bg-red-500 hover:bg-red-600'
                    ]"
                  >
                    Delete
                  </button>
                </td>
              </tr>
              <tr v-if="!products || products.length === 0">
                <td colspan="11" class="px-6 py-4 text-center text-gray-400">
                  No products found
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Create Modal -->
    <ProductCreateModal
      v-model:open="isCreateModalOpen"
      :brands="brands"
      :categories="categories"
      :types="types"
      :measurementUnits="measurementUnits"
      :suppliers="suppliers"
      :customers="customers"
      :discounts="discounts"
      :taxes="taxes"
    />

    <!-- View Modal -->
    <ProductViewModal
      v-model:open="isViewModalOpen"
      :product="selectedProductForView"
      v-if="selectedProductForView"
    />

    <!-- Edit Modal -->
    <ProductEditModal
      v-model:open="isEditModalOpen"
      :product="selectedProduct"
      :brands="brands"
      :categories="categories"
      :types="types"
      :measurementUnits="measurementUnits"
      :suppliers="suppliers"
      :customers="customers"
      :discounts="discounts"
      :taxes="taxes"
      v-if="selectedProduct"
    />

    <!-- Duplicate Modal -->
    <ProductDuplicateModal
      v-model:open="isDuplicateModalOpen"
      :product="selectedProductForDuplicate"
      :brands="brands"
      :categories="categories"
      :types="types"
      :measurementUnits="measurementUnits"
      :discounts="discounts"
      :taxes="taxes"
    />

    <!-- Delete Modal -->
    <ProductDeleteModal
      v-model:open="isDeleteModalOpen"
      :product="selectedProductForDelete"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3"; 
import ProductCreateModal from "./Components/ProductCreateModal.vue";
import ProductViewModal from "./Components/ProductViewModal.vue";
import ProductEditModal from "./Components/ProductEditModal.vue";
import ProductDeleteModal from "./Components/ProductDeleteModal.vue";
import ProductDuplicateModal from "./Components/ProductDuplicateModal.vue";

defineProps({
  products: {
    type: Array,
    required: true,
  },
  brands: {
    type: Array,
    required: true,
  },
  categories: {
    type: Array,
    required: true,
  },
  types: {
    type: Array,
    required: true,
  },
  measurementUnits: {
    type: Array,
    required: true,
  },
  discounts: {
    type: Array,
    required: true,
  },
  taxes: {
    type: Array,
    required: true,
  },
});

const isCreateModalOpen = ref(false);
const isViewModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isDuplicateModalOpen = ref(false);
const selectedProduct = ref(null);
const selectedProductForView = ref(null);
const selectedProductForDelete = ref(null);
const selectedProductForDuplicate = ref(null);

const openCreateModal = () => {
  isCreateModalOpen.value = true;
};

const openViewModal = (product) => {
  selectedProductForView.value = product;
  isViewModalOpen.value = true;
};

const openEditModal = (product) => {
  selectedProduct.value = product;
  isEditModalOpen.value = true;
};

const openDeleteModal = (product) => {
  if (!product || !product.id) {
    console.error('Invalid product data');
    return;
  }
  selectedProductForDelete.value = { ...product };
  isDeleteModalOpen.value = true;
};

const openDuplicateModal = (product) => {
  console.log('Opening duplicate modal for:', product);
  if (!product || !product.id) {
    console.error('Invalid product data');
    return;
  }
  selectedProductForDuplicate.value = { ...product };
  isDuplicateModalOpen.value = true;
  console.log('Duplicate modal state:', isDuplicateModalOpen.value);
  console.log('Selected product:', selectedProductForDuplicate.value);
};
</script>
