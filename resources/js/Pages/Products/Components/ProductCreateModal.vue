<template>
  <Modal :show="open" @close="closeModal" max-width="6xl">
    <div class="p-6 bg-gray-900">
      <h2 class="mb-6 text-2xl font-bold text-white">Add New Product</h2>
      
      <form @submit.prevent="submit">
        <!-- Basic Information Section -->
        <div class="mb-6">
          <h3 class="mb-4 text-lg font-semibold text-blue-400">Basic Information</h3>
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <!-- Product Name -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">
                Product Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                placeholder="Enter product name"
                required
              />
              <span v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</span>
            </div>

            <!-- Barcode -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Barcode</label>
              <input
                v-model="form.barcode"
                type="text"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                placeholder="Enter or scan barcode"
              />
              <span v-if="form.errors.barcode" class="text-sm text-red-500">{{ form.errors.barcode }}</span>
            </div>

                        <div>
              <label class="block mb-2 text-sm font-medium text-white">Brand</label>
              <div class="relative">
                <select
                  v-model="form.brand_id"
                  class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500 appearance-none pr-8"
                  title="Select Brand"
                >
                  <option value="">Select Brand</option>
                  <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                    {{ brand.name }}
                  </option>
                </select>

                <button
                  type="button"
                  @click="openBrandModal"
                  class="absolute right-0 top-1/2 transform -translate-y-1/2 translate-x-1/2 p-0.5 text-blue-500 hover:text-blue-300 transition z-20"
                  title="Add New Brand"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 bg-gray-900 rounded-full border border-gray-700 p-0.5" fill="currentColor" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Category -->
            <div>
            <label class="block mb-2 text-sm font-medium text-white">Category</label>

            <div class="relative">
              <select
                v-model="form.category_id"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500 appearance-none pr-8"
                title="Select Category"
              >
                <option value="">Select Category</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
              <button
                type="button"
                @click="openCategoryModal"
                class="absolute right-0 top-1/2 transform -translate-y-1/2 translate-x-1/2 p-0.5 text-blue-500 hover:text-blue-300 transition z-20"
                title="Add New Category"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 bg-gray-900 rounded-full border border-gray-700 p-0.5" fill="currentColor" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
              </button>
            </div>
          </div>

            <!-- Type -->
             <div>
             <label class="block mb-2 text-sm font-medium text-white">Type</label>
             <div class="relative">
               <select
                 v-model="form.type_id"
                 class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500 appearance-none pr-8"
                 title="Select Type"
               >
                 <option value="">Select Type</option>
                 <option v-for="type in types" :key="type.id" :value="type.id">
                   {{ type.name }}
                 </option>
               </select>
            
               <button
                 type="button"
                 @click="openTypeModal"
                 class="absolute right-0 top-1/2 transform -translate-y-1/2 translate-x-1/2 p-0.5 text-blue-500 hover:text-blue-300 transition z-20"
                 title="Add New Type"
               >
                 <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 bg-gray-900 rounded-full border border-gray-700 p-0.5" fill="currentColor" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                   <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                 </svg>
               </button>
               </div>
             </div>

            <!-- Status -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Status</label>
              <select
                v-model="form.status"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
              >
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Pricing Section -->
        <div class="mb-6">
          <h3 class="mb-4 text-lg font-semibold text-green-400">Pricing Information</h3>
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <!-- Purchase Price -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Purchase Price</label>
              <input
                v-model="form.purchase_price"
                type="number"
                step="0.01"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                placeholder="0.00"
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
                placeholder="0.00"
              />
            </div>

            <!-- Retail Price -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">
                Retail Price <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.retail_price"
                type="number"
                step="0.01"
                required
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                placeholder="0.00"
              />
            </div>

            <!-- Discount -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Discount Type</label>
              <select
                v-model="form.discount_id"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
              >
                <option value="">No Discount</option>
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
                <option value="">No Tax</option>
                <option v-for="tax in taxes" :key="tax.id" :value="tax.id">
                  {{ tax.name }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Inventory Section -->
        <div class="mb-6">
          <h3 class="mb-4 text-lg font-semibold text-yellow-400">Inventory & Units</h3>
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <!-- Quantity -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">
                Initial Quantity <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.qty"
                type="number"
                required
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                placeholder="0"
              />
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

            <!-- Measurement Unit -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Base Measurement Unit</label>
              <select
                v-model="form.measurement_unit_id"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
              >
                <option value="">Select Unit</option>
                <option v-for="unit in measurementUnits" :key="unit.id" :value="unit.id">
                  {{ unit.name }}
                </option>
              </select>
            </div>

            <!-- Purchase Unit -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Purchase Unit</label>
              <select
                v-model="form.purchase_unit_id"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
              >
                <option value="">Select Unit</option>
                <option v-for="unit in measurementUnits" :key="unit.id" :value="unit.id">
                  {{ unit.name }}
                </option>
              </select>
            </div>

            <!-- Sales Unit -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Sales Unit</label>
              <select
                v-model="form.sales_unit_id"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
              >
                <option value="">Select Unit</option>
                <option v-for="unit in measurementUnits" :key="unit.id" :value="unit.id">
                  {{ unit.name }}
                </option>
              </select>
            </div>

            <!-- Transfer Unit -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Transfer Unit</label>
              <select
                v-model="form.transfer_unit_id"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
              >
                <option value="">Select Unit</option>
                <option v-for="unit in measurementUnits" :key="unit.id" :value="unit.id">
                  {{ unit.name }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Conversion Rates Section -->
        <div class="mb-6">
          <h3 class="mb-4 text-lg font-semibold text-purple-400">Unit Conversion Rates</h3>
          <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <!-- Purchase to Transfer Rate -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Purchase → Transfer Rate</label>
              <input
                v-model="form.purchase_to_transfer_rate"
                type="number"
                step="0.01"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                placeholder="1.00"
              />
            </div>

            <!-- Purchase to Sales Rate -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Purchase → Sales Rate</label>
              <input
                v-model="form.purchase_to_sales_rate"
                type="number"
                step="0.01"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                placeholder="1.00"
              />
            </div>

            <!-- Transfer to Sales Rate -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Transfer → Sales Rate</label>
              <input
                v-model="form.transfer_to_sales_rate"
                type="number"
                step="0.01"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                placeholder="1.00"
              />
            </div>
          </div>
        </div>

        <!-- Additional Options Section -->
        <div class="mb-6">
          <h3 class="mb-4 text-lg font-semibold text-pink-400">Additional Options</h3>
          <div class="space-y-4">
            <!-- Return Product Checkbox -->
            <div class="flex items-center p-3 bg-gray-800 rounded">
              <input
                v-model="form.return_product"
                type="checkbox"
                id="return-product"
                class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500"
              />
              <label for="return-product" class="ml-3 text-sm font-medium text-white">
                Allow Product Returns
              </label>
            </div>

            <!-- Image Upload -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Product Image</label>
              <input
                @input="form.image = $event.target.files[0]"
                type="file"
                accept="image/*"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700"
              />
              <span v-if="form.errors.image" class="text-sm text-red-500">{{ form.errors.image }}</span>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end pt-6 mt-6 space-x-3 border-t border-gray-700">
          <button
            type="button"
            @click="closeModal"
            class="px-6 py-2 text-white transition bg-gray-600 rounded hover:bg-gray-700"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-2 text-white transition bg-blue-600 rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ form.processing ? 'Creating...' : 'Create Product' }}
          </button>
        </div>
      </form>
    </div>
  </Modal>
  <!-- ADD THESE 3 QUICK ADD MODALS HERE (right after the main modal) -->
  <QuickAddModal
    :show="quickAddModal.brand"
    type="brand"
    route-name="brands.store"
    @close="quickAddModal.brand = false"
    @created="fetchBrands"
  />
  <QuickAddModal
    :show="quickAddModal.category"
    type="category"
    route-name="categories.store"
    @close="quickAddModal.category = false"
    @created="fetchCategories"
  />
  <QuickAddModal
    :show="quickAddModal.type"
    type="type"
    route-name="types.store"
    @close="quickAddModal.type = false"
    @created="fetchTypes"
  />
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import { useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";

import QuickAddModal from '@/Pages/Products/Components/QuickAddModal.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  open: Boolean,
  brands: Array,
  categories: Array,
  types: Array,
  measurementUnits: {
    type: Array,
    default: () => []
  },
  suppliers: Array,
  customers: Array,
  discounts: Array,
  taxes: Array,
});

const emit = defineEmits(["update:open"]);

 // ADD THIS REACTIVE OBJECT HERE
const quickAddModal = ref({
  brand: false,
  category: false,
  type: false,
});

// ADD THESE FUNCTIONS (can be placed just before or after your submit/closeModal functions)
const openBrandModal = () => quickAddModal.value.brand = true;
const openCategoryModal = () => quickAddModal.value.category = true;
const openTypeModal = () => quickAddModal.value.type = true;

// Refresh data after quick creation
const fetchBrands = () => {
  router.reload({ only: ['brands'] });
};
const fetchCategories = () => {
  router.reload({ only: ['categories'] });
};
const fetchTypes = () => {
  router.reload({ only: ['types'] });
};

const form = useForm({
  name: "",
  barcode: "",
  brand_id: null,
  category_id: null,
  type_id: null,
  measurement_unit_id: null,
  discount_id: null,
  tax_id: null,
  qty: 0,
  low_stock_margin: 5,
  purchase_price: null,
  wholesale_price: null,
  retail_price: null,
  return_product: false,
  purchase_unit_id: null,
  sales_unit_id: null,
  transfer_unit_id: null,
  purchase_to_transfer_rate: null,
  purchase_to_sales_rate: null,
  transfer_to_sales_rate: null,
  status: 1,
  image: null,
});

const submit = () => {
  form.post(route("products.store"), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      closeModal();
      form.reset();
    },
    onError: (errors) => {
      console.error('Validation errors:', errors);
    },
  });
};

const closeModal = () => {
  emit("update:open", false);
  form.reset();
  form.clearErrors();
};
</script>