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
              <input v-model="form.name" type="text"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                placeholder="Enter product name" required />
              <span v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</span>
            </div>

            <!-- Barcode -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Barcode</label>
              <input v-model="form.barcode" type="text"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                placeholder="Enter or scan barcode" />
              <span v-if="form.errors.barcode" class="text-sm text-red-500">{{ form.errors.barcode }}</span>
            </div>

            <div>
              <label class="block mb-2 text-sm font-medium text-white">Brand</label>
              <div class="relative">
                <select v-model="form.brand_id"
                  class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500 appearance-none pr-8"
                  title="Select Brand">
                  <option value="">Select Brand</option>
                  <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                    {{ brand.name }}
                  </option>
                </select>

                <button type="button" @click="openBrandModal"
                  class="absolute right-0 top-1/2 transform -translate-y-1/2 translate-x-1/2 p-0.5 text-blue-500 hover:text-blue-300 transition z-20"
                  title="Add New Brand">
                  <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-6 h-6 bg-gray-900 rounded-full border border-gray-700 p-0.5" fill="currentColor"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Category -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Category</label>

              <div class="relative">
                <select v-model="form.category_id"
                  class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500 appearance-none pr-8"
                  title="Select Category">
                  <option value="">Select Category</option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }}
                  </option>
                </select>
                <button type="button" @click="openCategoryModal"
                  class="absolute right-0 top-1/2 transform -translate-y-1/2 translate-x-1/2 p-0.5 text-blue-500 hover:text-blue-300 transition z-20"
                  title="Add New Category">
                  <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-6 h-6 bg-gray-900 rounded-full border border-gray-700 p-0.5" fill="currentColor"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Type -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Type</label>
              <div class="relative">
                <select v-model="form.type_id"
                  class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500 appearance-none pr-8"
                  title="Select Type">
                  <option value="">Select Type</option>
                  <option v-for="type in types" :key="type.id" :value="type.id">
                    {{ type.name }}
                  </option>
                </select>

                <button type="button" @click="openTypeModal"
                  class="absolute right-0 top-1/2 transform -translate-y-1/2 translate-x-1/2 p-0.5 text-blue-500 hover:text-blue-300 transition z-20"
                  title="Add New Type">
                  <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-6 h-6 bg-gray-900 rounded-full border border-gray-700 p-0.5" fill="currentColor"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Status -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Status</label>
              <select v-model="form.status"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500">
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
              <input v-model="form.purchase_price" type="number" step="0.01"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                placeholder="0.00" />
            </div>

            <!-- Wholesale Price -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Wholesale Price</label>
              <input v-model="form.wholesale_price" type="number" step="0.01"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                placeholder="0.00" />
            </div>

            <!-- Retail Price -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">
                Retail Price <span class="text-red-500">*</span>
              </label>
              <input v-model="form.retail_price" type="number" step="0.01" required
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                placeholder="0.00" />
            </div>

            <!-- Discount -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Discount Type</label>
              <select v-model="form.discount_id"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500">
                <option value="">No Discount</option>
                <option v-for="discount in discounts" :key="discount.id" :value="discount.id">
                  {{ discount.name }}
                </option>
              </select>
            </div>

            <!-- Tax -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Tax</label>
              <select v-model="form.tax_id"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500">
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


          <!-- Units Row -->
          <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-3">
            <!-- Purchase Unit -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Purchase Unit</label>
              <div class="relative">
                <select v-model="form.purchase_unit_id"
                  class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500 appearance-none pr-8">
                  <option value="">Select Unit</option>
                  <option v-for="unit in measurementUnits" :key="unit.id" :value="unit.id">
                    {{ unit.name }}
                  </option>
                </select>

                <button type="button" @click="openUnitModal('purchase')"
                  class="absolute right-0 top-1/2 transform -translate-y-1/2 translate-x-1/2 p-0.5 text-blue-500 hover:text-blue-300 transition z-20"
                  title="Add New Unit">
                  <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-6 h-6 bg-gray-900 rounded-full border border-gray-700 p-0.5" fill="currentColor"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Transfer Unit -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Transfer Unit</label>
              <div class="relative">
                <select v-model="form.transfer_unit_id"
                  class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500 appearance-none pr-8">
                  <option value="">Select Unit</option>
                  <option v-for="unit in measurementUnits" :key="unit.id" :value="unit.id">
                    {{ unit.name }}
                  </option>
                </select>

                <button type="button" @click="openUnitModal('transfer')"
                  class="absolute right-0 top-1/2 transform -translate-y-1/2 translate-x-1/2 p-0.5 text-blue-500 hover:text-blue-300 transition z-20"
                  title="Add New Unit">
                  <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-6 h-6 bg-gray-900 rounded-full border border-gray-700 p-0.5" fill="currentColor"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Sales Unit -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Sales Unit</label>
              <div class="relative">
                <select v-model="form.sales_unit_id"
                  class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500 appearance-none pr-8">
                  <option value="">Select Unit</option>
                  <option v-for="unit in measurementUnits" :key="unit.id" :value="unit.id">
                    {{ unit.name }}
                  </option>
                </select>

                <button type="button" @click="openUnitModal('sales')"
                  class="absolute right-0 top-1/2 transform -translate-y-1/2 translate-x-1/2 p-0.5 text-blue-500 hover:text-blue-300 transition z-20"
                  title="Add New Unit">
                  <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-6 h-6 bg-gray-900 rounded-full border border-gray-700 p-0.5" fill="currentColor"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 mt-4">
          

            <!-- Store Quantity -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">
                Store Quantity
                <span v-if="form.purchase_unit_id" class="text-blue-400">
                  ({{ purchaseUnitDisplayName }})
                </span>
              </label>
              <input v-model="form.store_quantity" type="number"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                placeholder="0" />
              <span class="text-xs text-gray-400">Stock quantity in main store</span>
              <p v-if="storeQuantityAsSalesUnit" class="text-xs text-gray-300">
                ≈ {{ storeQuantityAsSalesUnit }} (sales unit)
              </p>
            </div>

            <!-- Store Low Stock Margin -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Store Low Stock Alert  <span v-if="form.purchase_unit_id" class="text-blue-400">
                  ({{ purchaseUnitDisplayName }})
                </span></label>

               
              <input v-model="form.store_low_stock_margin" type="number"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                placeholder="10" />
              <span class="text-xs text-gray-400">Alert when store stock falls below this level</span>
            </div>

           
 </div>

   <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 mt-4">
          
              <!-- Shop Quantity -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">
                Shop Quantity <span class="text-red-500">*</span>
                <span v-if="form.sales_unit_id" class="text-green-400">
                  ({{ getSalesUnitName(form.sales_unit_id) }})
                </span>
              </label>
              <input v-model="form.shop_quantity" type="number" required
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                placeholder="0" />
            </div>

            <!-- Shop Low Stock Margin -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Shop Low Stock Alert

<span v-if="form.sales_unit_id" class="text-green-400">
                  ({{ getSalesUnitName(form.sales_unit_id) }})
                </span>

              </label>
              <input v-model="form.shop_low_stock_margin" type="number"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                placeholder="10" />
              <span class="text-xs text-gray-400">Alert when shop stock falls below this level</span>
            </div>
 
          </div>

        </div>

        <!-- Conversion Rates Section -->
        <div class="mb-6">
          <h3 class="mb-4 text-lg font-semibold text-purple-400">Unit Conversion Rates</h3>
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <!-- Purchase to Transfer Rate -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">
                Purchase → Transfer Rate
                <span v-if="form.purchase_unit_id && form.transfer_unit_id" class="text-purple-300">
                  (1 {{ getPurchaseUnitShortName(form.purchase_unit_id) }} = ? {{ getTransferUnitName(form.transfer_unit_id) }})
                </span>
              </label>
              <input v-model="form.purchase_to_transfer_rate" type="number" step="0.01"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                placeholder="1.00" />
              <span class="text-xs text-gray-400">How many transfer units in one purchase unit</span>
            </div>

            <!-- Transfer to Sales Rate -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">
                Transfer → Sales Rate
                <span v-if="form.transfer_unit_id && form.sales_unit_id" class="text-purple-300">
                  (1 {{ getTransferUnitName(form.transfer_unit_id) }} = ? {{ getSalesUnitName(form.sales_unit_id) }})
                </span>
              </label>
              <input v-model="form.transfer_to_sales_rate" type="number" step="0.01"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                placeholder="1.00" />
              <span class="text-xs text-gray-400">How many sales units in one transfer unit</span>
            </div>
          </div>

          <!-- Conversion Calculation Display -->
          <div v-if="form.store_quantity > 0 && form.purchase_to_transfer_rate > 0" 
               class="mt-4 p-4 bg-gray-800 rounded-lg border border-purple-500">
            <h4 class="text-sm font-semibold text-purple-400 mb-2">Store Stock Conversion:</h4>
            <div class="text-white">
              <p class="text-sm">
                <span class="font-bold">{{ form.store_quantity }}</span> 
                <span class="text-blue-400">{{ getPurchaseUnitConvertedName(form.purchase_unit_id) || 'Purchase Unit' }}</span>
                <span class="mx-2">=</span>
                <span class="font-bold text-green-400">{{ calculateStoreInTransfer }}</span>
                <span class="text-yellow-400">{{ getTransferUnitName(form.transfer_unit_id) || 'Transfer Unit' }}</span>
              </p>
              <p v-if="form.transfer_to_sales_rate > 0" class="text-sm mt-2">
                <span class="mx-2">=</span>
                <span class="font-bold text-green-400">{{ calculateStoreInSales }}</span>
                <span class="text-pink-400">{{ getSalesUnitName(form.sales_unit_id) || 'Sales Unit' }}</span>
              </p>
            </div>
          </div>

        </div>

        <!-- Additional Options Section -->
        <div class="mb-6">
          <h3 class="mb-4 text-lg font-semibold text-pink-400">Additional Options</h3>
          <div class="space-y-4">
            <!-- Return Product Checkbox -->
            <div class="flex items-center p-3 bg-gray-800 rounded">
              <input v-model="form.return_product" type="checkbox" id="return-product"
                class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500" />
              <label for="return-product" class="ml-3 text-sm font-medium text-white">
                Allow Product Returns
              </label>
            </div>

            <!-- Image Upload -->
            <div>
              <label class="block mb-2 text-sm font-medium text-white">Product Image</label>
              <input @input="form.image = $event.target.files[0]" type="file" accept="image/*"
                class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700" />
              <span v-if="form.errors.image" class="text-sm text-red-500">{{ form.errors.image }}</span>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end pt-6 mt-6 space-x-3 border-t border-gray-700">
          <button type="button" @click="closeModal"
            class="px-6 py-2 text-white transition bg-gray-600 rounded hover:bg-gray-700">
            Cancel
          </button>
          <button type="submit" :disabled="form.processing"
            class="px-6 py-2 text-white transition bg-blue-600 rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
            {{ form.processing ? 'Creating...' : 'Create Product' }}
          </button>
        </div>
      </form>
    </div>
  </Modal>
  <!-- ADD THESE 3 QUICK ADD MODALS HERE (right after the main modal) -->
  <QuickAddModal :show="quickAddModal.brand" type="brand" route-name="brands.store" @close="quickAddModal.brand = false"
    @created="(item) => handleQuickCreated('brand', item)" />
  <QuickAddModal :show="quickAddModal.category" type="category" route-name="categories.store"
    @close="quickAddModal.category = false" @created="(item) => handleQuickCreated('category', item)" />
  <QuickAddModal :show="quickAddModal.type" type="type" route-name="types.store" @close="quickAddModal.type = false"
    @created="(item) => handleQuickCreated('type', item)" />
  <!-- Quick Add Unit Modal -->
  <QuickAddModal :show="quickAddModal.unit" type="unit" route-name="measurement-units.store"
    @close="quickAddModal.unit = false" @created="(item) => handleQuickCreated('unit', item)" />
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
import QuickAddModal from '@/Pages/Products/Components/QuickAddModal.vue';

// Track which field opened the unit modal (purchase/sales/transfer)
const unitTargetField = ref(null);

const quickAddModal = ref({
  brand: false,
  category: false,
  type: false,
  unit: false,
});

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

const form = useForm({
  name: "",
  barcode: "",
  brand_id: null,
  category_id: null,
  type_id: null,
  discount_id: null,
  tax_id: null,
  shop_quantity: 0,
  shop_low_stock_margin: 0, 
  store_quantity: 0,
  store_low_stock_margin: 0, 
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
  image: null,
});

// Helper function to get Purchase Unit name by ID
const getPurchaseUnitName = (unitId) => {
  if (!unitId) return '';
  const unit = props.measurementUnits.find(u => u.id === unitId);
  return unit ? unit.name : '';
};

// Helper function to get Purchase Unit short name by ID
const getPurchaseUnitShortName = (unitId) => {
  if (!unitId) return '';
  const unit = props.measurementUnits.find(u => u.id === unitId);
  return unit ? (unit.short_name || unit.name) : '';
};

// Helper function to get Purchase Unit converted name by ID
const getPurchaseUnitConvertedName = (unitId) => {
  if (!unitId) return '';
  const unit = props.measurementUnits.find(u => u.id === unitId);
  return unit ? unit.name : '';
};

// Helper function to get Sales Unit name by ID
const getSalesUnitName = (unitId) => {
  if (!unitId) return '';
  const unit = props.measurementUnits.find(u => u.id === unitId);
  return unit ? unit.name : '';
};

// Helper function to get Transfer Unit name by ID
const getTransferUnitName = (unitId) => {
  if (!unitId) return '';
  const unit = props.measurementUnits.find(u => u.id === unitId);
  return unit ? unit.name : '';
};

// Computed property for purchase unit display name
const purchaseUnitDisplayName = computed(() => {
  return getPurchaseUnitName(form.purchase_unit_id);
});

// Computed property to calculate store stock in transfer units
const calculateStoreInTransfer = computed(() => {
  const store = parseFloat(form.store_quantity) || 0;
  const rate = parseFloat(form.purchase_to_transfer_rate) || 0;
  return (store * rate).toFixed(2);
});

// Computed property to calculate store stock in sales units
const calculateStoreInSales = computed(() => {
  const store = parseFloat(form.store_quantity) || 0;
  const purchaseToTransfer = parseFloat(form.purchase_to_transfer_rate) || 0;
  const transferToSales = parseFloat(form.transfer_to_sales_rate) || 0;
  return (store * purchaseToTransfer * transferToSales).toFixed(2);
});

const storeQuantityAsSalesUnit = computed(() => {
  const store = Number(form.store_quantity);
  const p2t = Number(form.purchase_to_transfer_rate || 0);
  const t2s = Number(form.transfer_to_sales_rate || 0);
  if (!store || !p2t || !t2s) return '';
  const qty = store * p2t * t2s;
  const unit = getSalesUnitName(form.sales_unit_id);
  return unit ? `${qty} ${unit}` : `${qty}`;
});

// Computed property to calculate shop stock in transfer units
const calculateShopInTransfer = computed(() => {
  const shop = parseFloat(form.shop_quantity) || 0;
  const transferToSales = parseFloat(form.transfer_to_sales_rate) || 0;
  if (transferToSales === 0) return '0.00';
  return (shop / transferToSales).toFixed(2);
});

// Computed property to calculate shop stock in purchase units
const calculateShopInPurchase = computed(() => {
  const shop = parseFloat(form.shop_quantity) || 0;
  const purchaseToTransfer = parseFloat(form.purchase_to_transfer_rate) || 0;
  const transferToSales = parseFloat(form.transfer_to_sales_rate) || 0;
  if (purchaseToTransfer === 0 || transferToSales === 0) return '0.00';
  return (shop / (purchaseToTransfer * transferToSales)).toFixed(2);
});

// Open functions
const openBrandModal = () => quickAddModal.value.brand = true;
const openCategoryModal = () => quickAddModal.value.category = true;
const openTypeModal = () => quickAddModal.value.type = true;

// Special function for unit + buttons
const openUnitModal = (field) => {
  unitTargetField.value = field;
  quickAddModal.value.unit = true;
};

// Handle all quick creations (brand, category, type, AND unit)
const handleQuickCreated = (field, newItem) => {
  if (!newItem) return;

  if (field === 'brand') {
    props.brands.push(newItem);
    form.brand_id = newItem.id;
  }
  else if (field === 'category') {
    props.categories.push(newItem);
    form.category_id = newItem.id;
  }
  else if (field === 'type') {
    props.types.push(newItem);
    form.type_id = newItem.id;
  }
  else if (field === 'unit') {
    props.measurementUnits.push(newItem);

    if (unitTargetField.value === 'purchase') form.purchase_unit_id = newItem.id;
    if (unitTargetField.value === 'sales') form.sales_unit_id = newItem.id;
    if (unitTargetField.value === 'transfer') form.transfer_unit_id = newItem.id;

    unitTargetField.value = null;
  }

  quickAddModal.value[field] = false;
};

const submit = () => {
  const storeInTransfer = parseFloat(calculateStoreInTransfer.value) || 0;
  const storeInSales = parseFloat(calculateStoreInSales.value) || 0;
  const shopInTransfer = parseFloat(calculateShopInTransfer.value) || 0;
  const shopInPurchase = parseFloat(calculateShopInPurchase.value) || 0;

  const formData = {
    name: form.name,
    barcode: form.barcode,
    brand_id: form.brand_id,
    category_id: form.category_id,
    type_id: form.type_id,
    discount_id: form.discount_id,
    tax_id: form.tax_id,
    shop_quantity: parseFloat(form.shop_quantity) || 0,
    shop_low_stock_margin: parseFloat(form.shop_low_stock_margin) || 0,
    
    store_quantity: parseFloat(form.store_quantity) || 0,
    store_low_stock_margin: parseFloat(form.store_low_stock_margin) || 0,
   
    purchase_price: form.purchase_price,
    wholesale_price: form.wholesale_price,
    retail_price: form.retail_price,
    return_product: form.return_product,
    purchase_unit_id: form.purchase_unit_id,
    sales_unit_id: form.sales_unit_id,
    transfer_unit_id: form.transfer_unit_id,
    purchase_to_transfer_rate: form.purchase_to_transfer_rate,
    transfer_to_sales_rate: form.transfer_to_sales_rate,
    status: form.status,
    image: form.image,
    store_in_transfer_units: storeInTransfer,
    store_in_sales_units: storeInSales,
    shop_in_transfer_units: shopInTransfer,
    shop_in_purchase_units: shopInPurchase,
  };

  form.transform(() => formData).post(route("products.store"), {
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