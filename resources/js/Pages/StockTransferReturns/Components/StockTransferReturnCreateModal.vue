<template>
  <TransitionRoot appear :show="open" as="template">
    <Dialog as="div" @close="closeModal" class="relative z-50">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/25" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex items-center justify-center min-h-full p-4 text-center">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel
              class="w-full max-w-4xl p-6 overflow-hidden text-left align-middle transition-all transform bg-gray-50 shadow-2xl rounded-2xl max-h-[85vh] overflow-y-auto [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]"
            >
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-blue-600">✨ Create Stock Transfer Return</h2>
        <button
          @click="closeModal"
          class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-200 rounded-full transition-all duration-200"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            ></path>
          </svg>
        </button>
      </div>

      <form @submit.prevent="submitForm">
        <!-- Return Information -->
        <div class="mb-4 bg-white rounded-xl p-4 shadow-sm border border-gray-200">
          <h3 class="mb-3 text-lg font-semibold text-blue-600 flex items-center gap-2">
            📋 Return Information
          </h3>
          <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <div>
              <label class="block mb-2 text-sm font-medium text-gray-700"
                >Return Number</label
              >
              <input
                type="text"
                class="w-full px-3 py-2 text-sm text-gray-800 bg-gray-100 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :value="returnNo"
                readonly
              />
            </div>
            <div>
              <label class="block mb-2 text-sm font-medium text-gray-700">
                Return Date <span class="text-red-500">*</span>
              </label>
              <input
                type="date"
                class="w-full px-3 py-2 text-sm text-gray-800 bg-white border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="form.errors.return_date ? 'border-red-500' : 'border-gray-300'"
                v-model="form.return_date"
              />
              <div v-if="form.errors.return_date" class="mt-1 text-sm text-red-500">
                {{ form.errors.return_date }}
              </div>
            </div>

            <div class="md:col-span-2">
              <label class="block mb-2 text-sm font-medium text-gray-700">Reason</label>
              <textarea
                rows="3"
                class="w-full px-3 py-2 text-sm text-gray-800 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                v-model="form.reason"
                placeholder="e.g., Damaged, Expired, Quality issue..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Products -->
        <div class="mb-4 bg-white rounded-xl p-4 shadow-sm border border-gray-200">
          <h3 class="mb-3 text-lg font-semibold text-green-600 flex items-center gap-2">
            📦 Products
          </h3>
          <div
            v-for="(product, index) in form.products"
            :key="index"
            class="pb-4 mb-4 border-b border-gray-200 last:border-b-0"
          >
            <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
              <!-- Product -->
              <div class="md:col-span-4">
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Product <span class="text-red-500">*</span>
                </label>
                <select
                  class="w-full px-3 py-2 text-sm text-gray-800 bg-white border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :class="
                    form.errors[`products.${index}.product_id`]
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                  v-model="product.product_id"
                  @change="onProductSelect(index)"
                >
                  <option value="">Select Product</option>
                  <option v-for="prod in products" :key="prod.id" :value="prod.id">
                    {{ prod.name }} (Shop: {{ prod.shop_quantity_in_sales_unit }})
                  </option>
                </select>
                <div
                  v-if="form.errors[`products.${index}.product_id`]"
                  class="mt-1 text-sm text-red-500"
                >
                  {{ form.errors[`products.${index}.product_id`] }}
                </div>
              </div>

              <!-- Unit -->
              <div class="md:col-span-3">
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Unit <span class="text-red-500">*</span>
                </label>
                <select
                  class="w-full px-3 py-2 text-sm text-gray-800 bg-white border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :class="
                    form.errors[`products.${index}.measurement_unit_id`]
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                  v-model="product.measurement_unit_id"
                  @change="onUnitChange(index)"
                >
                  <option value="">Select Unit</option>
                  <option
                    v-for="unit in productUnits[index] || measurementUnits"
                    :key="unit.id"
                    :value="unit.id"
                  >
                    {{ unit.name }}{{ unit.symbol ? " (" + unit.symbol + ")" : "" }}{{ unit.available_quantity !== undefined ? " - Available: " + unit.available_quantity : "" }}
                  </option>
                </select>
                <div
                  v-if="form.errors[`products.${index}.measurement_unit_id`]"
                  class="mt-1 text-sm text-red-500"
                >
                  {{ form.errors[`products.${index}.measurement_unit_id`] }}
                </div>
              </div>

              <!-- Quantity -->
              <div class="md:col-span-3">
                <label class="block mb-2 text-sm font-medium text-gray-700">
                  Quantity <span class="text-red-500">*</span>
                </label>
                <input
                  type="number"
                  min="1"
                  step="1"
                  class="w-full px-3 py-2 text-sm text-gray-800 bg-white border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :class="
                    form.errors[`products.${index}.stock_transfer_quantity`]
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                  v-model.number="product.stock_transfer_quantity"
                />
                <div
                  v-if="form.errors[`products.${index}.stock_transfer_quantity`]"
                  class="mt-1 text-sm text-red-500"
                >
                  {{ form.errors[`products.${index}.stock_transfer_quantity`] }}
                </div>
                <!-- Available Quantity Display and Breakdown After Return -->
<!-- Replace the Available Quantity Display section in the modal -->
<!-- Available Quantity Display and Breakdown After Return -->
<div class="mt-1 flex flex-col gap-1">
  <span class="text-xs font-medium text-green-700">
    {{ (() => {
      const product = selectedProducts[index];
      const unitId = form.products[index].measurement_unit_id;
      const returnQty = Number(form.products[index].stock_transfer_quantity) || 0;
      if (!product || !unitId) return 'No product selected';
      
      // Get conversion rates
      const salesPerBundle = Number(product.transfer_to_sales_rate) || 1; // bottles per bundle
      const bundlesPerBox = Number(product.purchase_to_transfer_rate) || 1; // bundles per box
      const salesPerBox = salesPerBundle * bundlesPerBox; // bottles per box
      
      // Current shop quantities
      let shopQtySales = Number(product.shop_quantity_in_sales_unit) || 0;
      
      // Convert return quantity to bottles based on selected unit
      let returnInBottles = 0;
      if (unitId == product.sales_unit_id) {
        // Return in bottles
        returnInBottles = returnQty;
      } else if (unitId == product.transfer_unit_id) {
        // Return in bundles
        returnInBottles = returnQty * salesPerBundle;
      } else if (unitId == product.purchase_unit_id) {
        // Return in boxes
        returnInBottles = returnQty * salesPerBox;
      }
      
      // New shop quantity after return
      let newShopQty = shopQtySales - returnInBottles;
      if (newShopQty < 0) newShopQty = 0;
      
      // Calculate breakdown for display
      const boxes = Math.floor(newShopQty / salesPerBox);
      const remAfterBox = newShopQty % salesPerBox;
      const bundles = Math.floor(remAfterBox / salesPerBundle);
      const loose = remAfterBox % salesPerBundle;
      
      // Return formatted breakdown
      let result = `Shop: ${newShopQty} ${product.sales_unit?.symbol || 'btl'}`;
      if (boxes > 0) result += `, Store (${product.purchase_unit?.symbol || 'Box'}): ${boxes}`;
      if (bundles > 0) result += `, Loose (${product.transfer_unit?.symbol || 'Bnl'}): ${bundles}`;
      if (loose > 0) result += `, + Loose (${product.sales_unit?.symbol || 'Btl'}): ${loose}`;
      return result;
    })() }}
  </span>
  <span v-if="productUnits[index] && product.measurement_unit_id" class="text-xs text-gray-500">
    {{ productUnits[index].find(u => u.id == product.measurement_unit_id)?.name || '' }}
  </span>
</div>
              </div>

              <!-- Remove -->
              <div class="flex items-end md:col-span-2">
                <button
                  type="button"
                  @click="removeProduct(index)"
                  class="w-full px-3 py-2 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700 transition"
                >
                  Remove
                </button>
              </div>
            </div>
          </div>

          <button
            type="button"
            @click="addProduct"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition"
          >
            <svg
              class="w-4 h-4 mr-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 4v16m8-8H4"
              ></path>
            </svg>
            Add Product
          </button>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-3 pt-4 mt-4 border-t border-gray-300">
          <button
            type="button"
            @click="closeModal"
            class="px-8 py-2.5 rounded-full font-semibold text-sm bg-gray-500 text-white hover:bg-gray-600 transition-all duration-200"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-8 py-2.5 rounded-full font-semibold text-sm bg-blue-600 text-white hover:bg-blue-700 transition-all duration-200 disabled:opacity-50"
            :disabled="form.processing"
          >
            <span v-if="form.processing"> ⏳ Processing... </span>
            <span v-else> ✨ Create Return </span>
          </button>
        </div>
      </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch, onUnmounted } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import axios from 'axios';
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
} from '@headlessui/vue';
import { logActivity } from "@/composables/useActivityLog";

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  products: {
    type: Array,
    required: true,
  },
  measurementUnits: {
    type: Array,
    default: () => [],
  },
  users: {
    type: Array,
    required: true,
  },
  returnNo: {
    type: String,
    required: true,
  },
  inline: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:open", "close"]);

// Prevent background scrolling when modal is open
watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      document.body.style.overflow = "hidden";
    } else {
      document.body.style.overflow = "";
    }
  }
);

// Cleanup on unmount
onUnmounted(() => {
  document.body.style.overflow = "";
});

const form = useForm({
  return_no: "",
  return_date: new Date().toISOString().split("T")[0],
  reason: "",
  products: [
    {
      product_id: "",
      measurement_unit_id: "",
      stock_transfer_quantity: 1,
    },
  ],
});

const selectedProducts = ref({});
const productUnits = ref({});
const availableQuantities = ref({}); // Track available quantities by index

watch(
  () => props.open,
  (newVal) => {
    if (newVal) {
      form.return_no = props.returnNo;
      form.return_date = new Date().toISOString().split("T")[0];
      form.reason = "";
      form.products = [
        {
          product_id: "",
          measurement_unit_id: "",
          stock_transfer_quantity: 1,
        },
      ];
      selectedProducts.value = {};
      productUnits.value = {};
      availableQuantities.value = {};
      form.clearErrors();
    }
  }
);

const fetchAvailableQuantity = async (index) => {
  const productId = form.products[index].product_id;
  const unitId = form.products[index].measurement_unit_id;

  if (!productId || !unitId) {
    availableQuantities.value[index] = null;
    return;
  }

  try {
    const response = await axios.post(route('stock-transfer-returns.available-quantity'), {
      product_id: productId,
      measurement_unit_id: unitId
    });
    
    availableQuantities.value[index] = response.data.available_quantity;
  } catch (error) {
    console.error('Error fetching available quantity:', error);
    availableQuantities.value[index] = null;
  }
};

const onProductSelect = (index) => {
  const productId = form.products[index].product_id;
  const product = props.products.find((p) => p.id == productId);
  selectedProducts.value[index] = product;

  if (product && product.measurement_units) {
    productUnits.value[index] = product.measurement_units;
    if (product.measurement_units.length > 0) {
      // Auto-select first available unit
      const firstUnit = product.measurement_units[0];
      form.products[index].measurement_unit_id = firstUnit.id;
      
      // If available_quantity is already in the unit data (pre-loaded), use it
      if (firstUnit.available_quantity !== undefined) {
        availableQuantities.value[index] = firstUnit.available_quantity;
      } else {
        // Otherwise fetch from API
        fetchAvailableQuantity(index);
      }
    }
  }
};

const onUnitChange = (index) => {
  const selectedUnitId = form.products[index].measurement_unit_id;
  const units = productUnits.value[index];
  
  if (units) {
    // Check if unit has pre-loaded available_quantity
    const selectedUnit = units.find(u => u.id == selectedUnitId);
    if (selectedUnit && selectedUnit.available_quantity !== undefined) {
      availableQuantities.value[index] = selectedUnit.available_quantity;
    } else {
      // Fetch from API if not pre-loaded
      fetchAvailableQuantity(index);
    }
  } else {
    fetchAvailableQuantity(index);
  }
};

const addProduct = () => {
  form.products.push({
    product_id: "",
    measurement_unit_id: "",
    stock_transfer_quantity: 1,
  });
};

const removeProduct = (index) => {
  form.products.splice(index, 1);
  delete selectedProducts.value[index];
};

const closeModal = () => {
  if (props.inline) {
    emit("close");
  } else {
    emit("update:open", false);
  }
};

const submitForm = () => {
  form.return_no = props.returnNo;

  form.post(route("stock-transfer-returns.store"), {
    onSuccess: async () => {
      // Log create activity
      await logActivity("create", "stock_transfer_returns", {
        return_number: form.return_no,
        return_date: form.return_date,
        user_id: form.user_id,
        products_count: form.products.length,
      });

      if (props.inline) {
        router.reload();
      } else {
        closeModal();
        router.reload();
      }
    },
    onError: (errors) => {
      console.error("Form submission errors:", errors);
    },
  });
};
</script>
