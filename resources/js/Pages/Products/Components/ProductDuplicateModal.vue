 
<template>
  <Modal :show="open" @close="closeModal" max-width="4xl">
    <div class="p-6 bg-gray-900">
      <h2 class="mb-4 text-2xl font-bold text-white">Duplicate Product</h2>
      
      <form @submit.prevent="duplicateProduct" class="space-y-4">
        <!-- Product Name -->
        <div>
          <label class="block mb-2 text-sm font-medium text-white">Product Name</label>
          <input
            v-model="form.name"
            type="text"
            class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
            required
          />
          <span v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</span>
        </div>

        <!-- Barcode (Auto-generated, read-only) -->
        <div>
          <label class="block mb-2 text-sm font-medium text-white">Barcode (Auto-generated)</label>
          <input
            type="text"
            value="Will be auto-generated"
            class="w-full px-4 py-2 text-gray-400 bg-gray-700 border border-gray-700 rounded cursor-not-allowed"
            readonly
            disabled
          />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <!-- Brand -->
          <div>
            <label class="block mb-2 text-sm font-medium text-white">Brand</label>
            <select
              v-model="form.brand_id"
              class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
            >
              <option :value="null">Select Brand</option>
              <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                {{ brand.name }}
              </option>
            </select>
          </div>

          <!-- Category -->
          <div>
            <label class="block mb-2 text-sm font-medium text-white">Category</label>
            <select
              v-model="form.category_id"
              class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
            >
              <option :value="null">Select Category</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <!-- Type -->
          <div>
            <label class="block mb-2 text-sm font-medium text-white">Type</label>
            <select
              v-model="form.type_id"
              class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
            >
              <option :value="null">Select Type</option>
              <option v-for="type in types" :key="type.id" :value="type.id">
                {{ type.name }}
              </option>
            </select>
          </div>

          
        </div>

        <div class="grid grid-cols-3 gap-4">
          <!-- Purchase Price -->
          <div>
            <label class="block mb-2 text-sm font-medium text-white">Purchase Price</label>
            <input
              v-model="form.purchase_price"
              type="number"
              step="0.01"
              class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
            />
          </div>

          <!-- Wholesale Price -->
          <div>
            <label class="block mb-2 text-sm font-medium text-white">Wholesale Price</label>
            <input
              v-model="form.wholesale_price"
              type="number"
              step="0.01"
              class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
            />
          </div>

          <!-- Retail Price -->
          <div>
            <label class="block mb-2 text-sm font-medium text-white">Retail Price</label>
            <input
              v-model="form.retail_price"
              type="number"
              step="0.01"
              class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
              required
            />
          </div>
        </div>

        <!-- Quantity -->
        <div>
          <label class="block mb-2 text-sm font-medium text-white">Quantity</label>
          <input
            v-model="form.qty"
            type="number"
            step="0.01"
            class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
            required
          />
        </div>

        <!-- Storage Stock Quantity -->
        <div>
          <label class="block mb-2 text-sm font-medium text-white">Storage Stock Quantity</label>
          <input
            v-model="form.storage_stock_qty"
            type="number"
            class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
            placeholder="0"
          />
          <span class="text-xs text-gray-400">Reserved stock in storage</span>
        </div>

        <!-- Low Stock Margin -->
        <div>
          <label class="block mb-2 text-sm font-medium text-white">Low Stock Alert Level</label>
          <input
            v-model="form.low_stock_margin"
            type="number"
            class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
            placeholder="10"
          />
          <span class="text-xs text-gray-400">Alert when stock falls below this level</span>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <!-- Discount -->
          <div>
            <label class="block mb-2 text-sm font-medium text-white">Discount</label>
            <select
              v-model="form.discount_id"
              class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
            >
              <option :value="null">Select Discount</option>
              <option v-for="discount in discounts" :key="discount.id" :value="discount.id">
                {{ discount.name }}
              </option>
            </select>
          </div>

          <!-- Tax -->
          <div>
            <label class="block mb-2 text-sm font-medium text-white">Tax</label>
            <select
              v-model="form.tax_id"
              class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
            >
              <option :value="null">Select Tax</option>
              <option v-for="tax in taxes" :key="tax.id" :value="tax.id">
                {{ tax.name }}
              </option>
            </select>
          </div>
        </div>

        <!-- Status -->
        <div>
          <label class="block mb-2 text-sm font-medium text-white">Status</label>
          <select
            v-model="form.status"
            class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
          >
            <option :value="1">Active</option>
            <option :value="0">Inactive</option>
          </select>
        </div>

        <!-- Return Product -->
        <div class="flex items-center">
          <input
            v-model="form.return_product"
            type="checkbox"
            class="w-4 h-4 text-blue-600 bg-gray-800 border-gray-700 rounded focus:ring-blue-500"
          />
          <label class="ml-2 text-sm font-medium text-white">Allow Product Returns</label>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end pt-4 space-x-3">
          <button
            type="button"
            @click="closeModal"
            class="px-4 py-2 text-white bg-gray-600 rounded hover:bg-gray-700"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 disabled:opacity-50"
          >
            {{ form.processing ? 'Creating...' : 'Create Duplicate' }}
          </button>
        </div>
      </form>
    </div>
  </Modal>
</template>

<script setup>
/**
 * Product Duplicate Modal Component Script
 * 
 * Handles creation of product duplicates with pre-filled data
 * Uses Inertia.js form helper for reactive form management
 */

import { useForm, router } from "@inertiajs/vue3";
import { watch } from "vue";
import Modal from "@/Components/Modal.vue";

/**
 * Component Props
 * @property {Boolean} open - Controls modal visibility
 * @property {Object} product - Source product to duplicate
 * @property {Array} brands - List of brands for dropdown
 * @property {Array} categories - List of categories for dropdown
 * @property {Array} types - List of types for dropdown
 * @property {Array} measurementUnits - List of units for dropdown
 * @property {Array} discounts - List of discounts for dropdown
 * @property {Array} taxes - List of taxes for dropdown
 */
const props = defineProps({
  open: Boolean,
  product: Object,
  brands: Array,
  categories: Array,
  types: Array,
  measurementUnits: Array,
  discounts: Array,
  taxes: Array,
});

/**
 * Component Emits
 * @event update:open - Emitted to close modal
 */
const emit = defineEmits(["update:open"]);

/**
 * Inertia Form Instance
 * Manages form data with reactive state and validation
 * Note: barcode is not included as it will be auto-generated by backend
 */
const form = useForm({
  name: '',
  brand_id: null,
  category_id: null,
  type_id: null, 
  discount_id: null,
  tax_id: null,
  qty: 0,
  storage_stock_qty: 0,
  low_stock_margin: 0,
  purchase_price: null,
  wholesale_price: null,
  retail_price: null,
  return_product: false,
  purchase_unit_id: null,
  sales_unit_id: null,
  transfer_unit_id: null,
  purchase_to_transfer_rate: null, 
  transfer_to_sales_rate: null,
  status: 1,
});

/**
 * Watch for Product Changes and Populate Form
 * When source product is provided, copy all fields to form
 * Special handling:
 * - qty: Set to 0 for new duplicate
 * - status: Set to Active (1)
 * - barcode: Not copied (will be auto-generated)
 * - image: Not copied
 */
watch(() => props.product, (newProduct) => {
  if (newProduct) {
    form.name = newProduct.name;
    form.brand_id = newProduct.brand_id;
    form.category_id = newProduct.category_id;
    form.type_id = newProduct.type_id; 
    form.discount_id = newProduct.discount_id;
    form.tax_id = newProduct.tax_id;
    form.qty = 0; // Default to 0 for duplicate
    form.storage_stock_qty = newProduct.storage_stock_qty || 0;
    form.low_stock_margin = newProduct.low_stock_margin || 5;
    form.purchase_price = newProduct.purchase_price;
    form.wholesale_price = newProduct.wholesale_price;
    form.retail_price = newProduct.retail_price;
    form.return_product = newProduct.return_product || false;
    form.purchase_unit_id = newProduct.purchase_unit_id;
    form.sales_unit_id = newProduct.sales_unit_id;
    form.transfer_unit_id = newProduct.transfer_unit_id;
    form.purchase_to_transfer_rate = newProduct.purchase_to_transfer_rate; 
    form.transfer_to_sales_rate = newProduct.transfer_to_sales_rate;
    form.status = 1; // Default to active
  }
}, { immediate: true });

/**
 * Duplicate Product Handler
 * Validates product exists and submits duplication request
 * 
 * Process:
 * 1. Check if source product exists
 * 2. POST to products.duplicate route with product ID
 * 3. Send all form data in request
 * 4. On success: Close modal and reset form
 * 5. On error: Log errors to console
 */
const duplicateProduct = () => {
  if (!props.product || !props.product.id) {
    console.error('No product selected for duplication');
    return;
  }

  form.post(route("products.duplicate", props.product.id), {
    preserveScroll: true,
    onSuccess: () => {
      closeModal();
      form.reset();
    },
    onError: (errors) => {
      console.error("Duplication failed:", errors);
    },
  });
};

/**
 * Close Modal Handler
 * Resets form and emits close event to parent
 */
const closeModal = () => {
  form.reset();
  emit("update:open", false);
};
</script>
