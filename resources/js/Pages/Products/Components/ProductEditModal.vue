<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
      @click.self="closeModal"
    >
      <div class="relative w-full max-w-6xl max-h-[90vh] overflow-y-auto p-6 bg-gray-900 rounded-lg shadow-xl">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold text-white">Edit Product</h2>
          <button
            @click="closeModal"
            class="text-gray-400 transition hover:text-white"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="handleSubmit">
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
                  required
                  class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                  placeholder="Enter product name"
                />
                <span v-if="errors.name" class="text-sm text-red-500">{{ errors.name }}</span>
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
                <span v-if="errors.barcode" class="text-sm text-red-500">{{ errors.barcode }}</span>
              </div>

              <!-- Brand -->
              <div>
                <label class="block mb-2 text-sm font-medium text-white">Brand</label>
                <select
                  v-model="form.brand_id"
                  class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                >
                  <option value="">Select Brand</option>
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
                  <option value="">Select Category</option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }}
                  </option>
                </select>
              </div>

              <!-- Type -->
              <div>
                <label class="block mb-2 text-sm font-medium text-white">Type</label>
                <select
                  v-model="form.type_id"
                  class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                >
                  <option value="">Select Type</option>
                  <option v-for="type in types" :key="type.id" :value="type.id">
                    {{ type.name }}
                  </option>
                </select>
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
                  v-model.number="form.purchase_price"
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
                  v-model.number="form.wholesale_price"
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
                  v-model.number="form.retail_price"
                  type="number"
                  step="0.01"
                  required
                  class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                  placeholder="0.00"
                />
                <span v-if="errors.retail_price" class="text-sm text-red-500">{{ errors.retail_price }}</span>
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
                  Quantity <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.qty"
                  type="number"
                  required
                  class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                  placeholder="0"
                />
                <span v-if="errors.qty" class="text-sm text-red-500">{{ errors.qty }}</span>
              </div>

              <!-- Low Stock Margin -->
              <div>
                <label class="block mb-2 text-sm font-medium text-white">Low Stock Alert Level</label>
                <input
                  v-model.number="form.low_stock_margin"
                  type="number"
                  class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                  placeholder="10"
                />
                <span class="text-xs text-gray-400">Alert when stock falls below this level</span>
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
                  v-model.number="form.purchase_to_transfer_rate"
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
                  v-model.number="form.purchase_to_sales_rate"
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
                  v-model.number="form.transfer_to_sales_rate"
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
                  id="return-product-edit"
                  class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500"
                />
                <label for="return-product-edit" class="ml-3 text-sm font-medium text-white">
                  Allow Product Returns
                </label>
              </div>

              <!-- Current Image Display -->
              <div v-if="product?.image" class="p-3 bg-gray-800 rounded">
                <p class="mb-2 text-sm font-medium text-white">Current Image:</p>
                <img :src="`/storage/${product.image}`" :alt="product.name" class="h-24 rounded" />
              </div>

              <!-- Image Upload -->
              <div>
                <label class="block mb-2 text-sm font-medium text-white">
                  {{ product?.image ? 'Replace Product Image' : 'Product Image' }}
                </label>
                <input
                  @input="form.image = $event.target.files[0]"
                  type="file"
                  accept="image/*"
                  class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700"
                />
                <span v-if="errors.image" class="text-sm text-red-500">{{ errors.image }}</span>
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
              :disabled="processing"
              class="px-6 py-2 text-white transition bg-blue-600 rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ processing ? 'Updating...' : 'Update Product' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  open: {
    type: Boolean,
    required: true,
  },
  product: {
    type: Object,
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
  suppliers: {
    type: Array,
    required: true,
  },
  customers: {
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

const emit = defineEmits(['update:open']);

const form = ref({
  name: '',
  barcode: '',
  brand_id: null,
  category_id: null,
  type_id: null, 
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

const errors = ref({});
const processing = ref(false);

// Initialize form with product data when modal opens
watch(() => props.open, (newVal) => {
  if (newVal && props.product) {
    form.value = {
      name: props.product.name || '',
      barcode: props.product.barcode || '',
      brand_id: props.product.brand_id || null,
      category_id: props.product.category_id || null,
      type_id: props.product.type_id || null, 
      discount_id: props.product.discount_id || null,
      tax_id: props.product.tax_id || null,
      qty: props.product.qty || 0,
      low_stock_margin: props.product.low_stock_margin || 5,
      purchase_price: props.product.purchase_price || null,
      wholesale_price: props.product.wholesale_price || null,
      retail_price: props.product.retail_price || null,
      return_product: props.product.return_product || false,
      purchase_unit_id: props.product.purchase_unit_id || null,
      sales_unit_id: props.product.sales_unit_id || null,
      transfer_unit_id: props.product.transfer_unit_id || null,
      purchase_to_transfer_rate: props.product.purchase_to_transfer_rate || null,
      purchase_to_sales_rate: props.product.purchase_to_sales_rate || null,
      transfer_to_sales_rate: props.product.transfer_to_sales_rate || null,
      status: props.product.status ?? 1,
      image: null,
    };
    errors.value = {};
  }
});

const closeModal = () => {
  emit('update:open', false);
  errors.value = {};
  processing.value = false;
};

const handleSubmit = () => {
  processing.value = true;
  errors.value = {};

  router.post(route('products.update', props.product.id), {
    ...form.value,
    _method: 'PUT',
  }, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      closeModal();
      processing.value = false;
    },
    onError: (err) => {
      errors.value = err;
      processing.value = false;
    },
  });
};
</script>