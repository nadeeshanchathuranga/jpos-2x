<template>
  <AppLayout>
    <!-- Main Container -->
    <div class="p-6">
      <!-- Header Section with Navigation and Actions -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
          <!-- Back to Dashboard Button -->
          <button
            @click="$inertia.visit(route('dashboard'))"
            class="px-4 py-2 text-white bg-accent rounded hover:bg-accent"
          >
            Back
          </button>
          <h1 class="text-3xl font-bold text-white">Products</h1>
        </div>
        <!-- Add New Product Button -->
        <button
          @click="openCreateModal"
          class="px-6 py-2 text-white bg-accent rounded hover:bg-accent"
        >
          Add Product
        </button>
      </div>
 
      <!-- Products Table Container -->
      <div class="overflow-hidden bg-dark border-4 border-accent rounded-lg">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-white">
            <!-- Table Header -->
            <thead class="bg-accent">
              <tr>
                <th class="px-6 py-3">ID</th>
                <th class="px-6 py-3">Barcode</th>
                <th class="px-6 py-3">Name</th>
                <th class="px-6 py-3">Code</th>
                <th class="px-6 py-3">Brand</th>
                <th class="px-6 py-3">Category</th>
                <th class="px-6 py-3">Purchase Price
                  ({{ currencySymbol.currency }})
                </th>
                <th class="px-6 py-3">Selling Price
                    ({{ currencySymbol.currency }})
                </th>
                <th class="px-6 py-3">Qty</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Actions</th>
              </tr>
            </thead>
            <!-- Table Body - Product Rows -->
            <tbody>
              <tr
                v-for="(product, index) in products"
                :key="product.id"
                class="border-b border-secondary hover:bg-gray-900"
              >
                <!-- Sequential ID -->
                <td class="px-6 py-4">{{ index + 1 }}</td>
                <!-- Product Barcode -->
                <td class="px-6 py-4">{{ product.barcode }}</td>
                <!-- Product Name -->
                <td class="px-6 py-4">{{ product.name }}</td>
                <!-- Optional Product Code -->
                <td class="px-6 py-4">{{ product.code || 'N/A' }}</td>
                <!-- Brand Name (with fallback) -->
                <td class="px-6 py-4">{{ product.brand?.name || 'N/A' }}</td>
                <!-- Category Name (with fallback) -->
                <td class="px-6 py-4">{{ product.category?.name || 'N/A' }}</td>
                <!-- Purchase Price -->
                <td class="px-6 py-4">{{ product.purchase_price || '0.00' }}</td>
                <!-- Selling/Retail Price -->
                <td class="px-6 py-4">{{ product.selling_price || '0.00' }}</td>
                <!-- Current Stock Quantity -->
                <td class="px-6 py-4">{{ product.qty }}</td>
                <!-- Product Status Badge -->
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
                <!-- Action Buttons -->
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
                        : 'bg-accent hover:bg-accent'
                    ]"
                  >
                    Edit
                  </button>
                  <button
                    @click="openDuplicateModal(product)"
                    class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600"
                  >
                    Duplicate
                  </button>
                </td>
              </tr>
              <!-- Empty State Message -->
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

    <!-- Modal Components for CRUD Operations -->
    
    <!-- Create Product Modal - Full product creation form with image upload, units, pricing -->
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
      :currencySymbol="currencySymbol"
    />

    <!-- View Product Modal - Read-only display with barcode printing capability -->
    <ProductViewModal
      v-model:open="isViewModalOpen"
      :product="selectedProductForView"
        :currencySymbol="currencySymbol"
      v-if="selectedProductForView"
    />

    <!-- Edit Product Modal - Full editing interface for existing products -->
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
      :currencySymbol="currencySymbol"
      :taxes="taxes"
      v-if="selectedProduct"
    />

    <!-- Duplicate Product Modal - Clone product with new barcode for variants -->
    <ProductDuplicateModal
      v-model:open="isDuplicateModalOpen"
      :product="selectedProductForDuplicate"
      :brands="brands"
      :categories="categories"
      :types="types"
      :currencySymbol="currencySymbol"
      :measurementUnits="measurementUnits"
      :discounts="discounts"
      :taxes="taxes"
    />
  </AppLayout>
</template>

<script setup>
/**
 * Products Index Component Script
 * 
 * Manages the products listing page with modal-based CRUD operations
 * Handles product viewing, editing, duplication, and deletion
 */

import { ref } from "vue";
import { router } from "@inertiajs/vue3"; 
import { logActivity } from "@/composables/useActivityLog";
import ProductCreateModal from "./Components/ProductCreateModal.vue";
import ProductViewModal from "./Components/ProductViewModal.vue";
import ProductEditModal from "./Components/ProductEditModal.vue";
import ProductDuplicateModal from "./Components/ProductDuplicateModal.vue";

/**
 * Component Props
 * All data passed from ProductController
 */
defineProps({
  products: {
    type: Array,
    required: true,
  },
  brands: {
    type: Array,
    required: true,
  },
   currencySymbol: {
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

/**
 * Reactive State Variables
 * 
 * Modal visibility states for each operation
 * Selected product references for edit/view/delete/duplicate operations
 */
const isCreateModalOpen = ref(false);
const isViewModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDuplicateModalOpen = ref(false);
const selectedProduct = ref(null);
const selectedProductForView = ref(null);
const selectedProductForDuplicate = ref(null);

/**
 * Open Create Product Modal
 * Opens empty form for new product creation
 */
const openCreateModal = () => {
  isCreateModalOpen.value = true;
};

/**
 * Open View Product Modal
 * Displays product details in read-only mode with barcode printing
 * Also logs the view activity to activity_logs table
 * 
 * @param {Object} product - Product object to view
 */
const openViewModal = async (product) => {
  selectedProductForView.value = product;
  isViewModalOpen.value = true;

  // Log the view activity
  await logActivity('view', 'products', {
    product_id: product.id,
    product_name: product.name,
    barcode: product.barcode,
    brand: product.brand?.name || 'N/A',
    category: product.category?.name || 'N/A',
    purchase_price: product.purchase_price,
    selling_price: product.selling_price,
    qty: product.qty,
    status: product.status,
  });
};

/**
 * Open Edit Product Modal
 * Loads product data into edit form
 * Also logs the edit activity to activity_logs table
 * 
 * @param {Object} product - Product object to edit
 */
const openEditModal = async (product) => {
  selectedProduct.value = product;
  isEditModalOpen.value = true;

  // Log the edit activity
  await logActivity('edit', 'products', {
    product_id: product.id,
    product_name: product.name,
    barcode: product.barcode,
    brand: product.brand?.name || 'N/A',
    category: product.category?.name || 'N/A',
    purchase_price: product.purchase_price,
    selling_price: product.selling_price,
    qty: product.qty,
    status: product.status,
  });
};

/**
 * Open Delete Confirmation Modal
 * Shows confirmation dialog before deletion
 * 
 * @param {Object} product - Product object to delete
 */
const openDeleteModal = (product) => {
  if (!product || !product.id) {
    console.error('Invalid product data');
    return;
  }
  selectedProductForDelete.value = { ...product };
  isDeleteModalOpen.value = true;
};

/**
 * Open Duplicate Product Modal
 * Clones product data for creating variants
 * Useful for creating similar products with different attributes
 * 
 * @param {Object} product - Product object to duplicate
 */
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
