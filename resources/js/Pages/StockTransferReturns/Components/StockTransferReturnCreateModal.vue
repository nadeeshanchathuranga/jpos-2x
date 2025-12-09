<template>
  <div v-if="inline || open" :class="inline ? '' : 'fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-75'">
    <div :class="inline ? 'w-full' : 'relative w-full max-w-2xl p-6 mx-4 my-8 bg-black border-4 border-blue-600 rounded-lg'">
      <!-- Header -->
      <div v-if="!inline" class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-white">Return Stock to Store</h2>
        <button @click="closeModal" class="text-white hover:text-gray-300">
          <i class="text-2xl fas fa-times"></i>
        </button>
      </div>

      <form @submit.prevent="submitForm">
        <!-- Return Information -->
        <div class="mb-6 overflow-hidden border-2 border-blue-500 rounded-lg">
          <div class="px-6 py-3 bg-blue-600">
            <h5 class="font-bold text-white">Return Information</h5>
          </div>
          <div class="p-6 bg-gray-900">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div>
                <label class="block mb-2 text-sm font-medium text-white">Return Number</label>
                <input
                  type="text"
                  class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                  :value="returnNo"
                  readonly
                />
              </div>
              <div>
                <label class="block mb-2 text-sm font-medium text-white">
                  Return Date <span class="text-red-500">*</span>
                </label>
                <input
                  type="date"
                  class="w-full px-4 py-2 text-white bg-gray-800 border rounded focus:outline-none focus:border-blue-500"
                  :class="form.errors.return_date ? 'border-red-500' : 'border-gray-700'"
                  v-model="form.return_date"
                />
                <div v-if="form.errors.return_date" class="mt-1 text-sm text-red-500">
                  {{ form.errors.return_date }}
                </div>
              </div>

              <div class="md:col-span-2">
                <label class="block mb-2 text-sm font-medium text-white">Reason</label>
                <textarea
                  rows="3"
                  class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                  v-model="form.reason"
                  placeholder="e.g., Damaged, Expired, Quality issue..."
                ></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Products -->
        <div class="overflow-hidden border-2 border-blue-500 rounded-lg">
          <div class="px-6 py-3 bg-blue-600">
            <h5 class="font-bold text-white">Products</h5>
          </div>
          <div class="p-6 bg-gray-900">
            <div
              v-for="(product, index) in form.products"
              :key="index"
              class="pb-6 mb-6 border-b border-gray-700 last:border-b-0"
            >
              <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                <!-- Product -->
                <div class="md:col-span-5">
                  <label class="block mb-2 text-sm font-medium text-white">
                    Product <span class="text-red-500">*</span>
                  </label>
                  <select
                    class="w-full px-4 py-2 text-white bg-gray-800 border rounded focus:outline-none focus:border-blue-500"
                    :class="form.errors[`products.${index}.product_id`] ? 'border-red-500' : 'border-gray-700'"
                    v-model="product.product_id"
                    @change="onProductSelect(index)"
                  >
                    <option value="">Select Product</option>
                    <option v-for="prod in products" :key="prod.id" :value="prod.id">
                      {{ prod.name }} (Shop: {{ prod.shop_quantity }})
                    </option>
                  </select>
                  <div v-if="form.errors[`products.${index}.product_id`]" class="mt-1 text-sm text-red-500">
                    {{ form.errors[`products.${index}.product_id`] }}
                  </div>
                </div>

                <!-- Unit -->
                <div class="md:col-span-2">
                  <label class="block mb-2 text-sm font-medium text-white">
                    Unit <span class="text-red-500">*</span>
                  </label>
                  <select
                    class="w-full px-4 py-2 text-white bg-gray-800 border rounded focus:outline-none focus:border-blue-500"
                    :class="form.errors[`products.${index}.measurement_unit_id`] ? 'border-red-500' : 'border-gray-700'"
                    v-model="product.measurement_unit_id"
                  >
                    <option value="">Select Unit</option>
                    <option v-for="unit in (productUnits[index] || measurementUnits)" :key="unit.id" :value="unit.id">
                      {{ unit.name }}{{ unit.symbol ? ' (' + unit.symbol + ')' : '' }}
                    </option>
                  </select>
                  <div v-if="form.errors[`products.${index}.measurement_unit_id`]" class="mt-1 text-sm text-red-500">
                    {{ form.errors[`products.${index}.measurement_unit_id`] }}
                  </div>
                </div>

                <!-- Quantity -->
                <div class="md:col-span-3">
                  <label class="block mb-2 text-sm font-medium text-white">
                    Quantity <span class="text-red-500">*</span>
                  </label>
                  <input
                    type="number"
                    min="1"
                    step="1"
                    class="w-full px-4 py-2 text-white bg-gray-800 border rounded focus:outline-none focus:border-blue-500"
                    :class="form.errors[`products.${index}.stock_transfer_quantity`] ? 'border-red-500' : 'border-gray-700'"
                    v-model.number="product.stock_transfer_quantity"
                  />
                  <div v-if="form.errors[`products.${index}.stock_transfer_quantity`]" class="mt-1 text-sm text-red-500">
                    {{ form.errors[`products.${index}.stock_transfer_quantity`] }}
                  </div>
                  <div v-if="selectedProducts[index]" class="mt-1 text-xs text-gray-400">
                    Available: {{ selectedProducts[index].shop_quantity }}
                  </div>
                </div>

                <!-- Remove -->
                <div class="flex items-end md:col-span-2">
                  <button
                    v-if="form.products.length > 1"
                    type="button"
                    @click="removeProduct(index)"
                    class="w-full px-4 py-2 text-white bg-red-500 rounded hover:bg-red-600"
                  >
                    Remove
                  </button>
                  <div v-else class="w-full px-4 py-2 text-center text-gray-500 text-sm">
                    -
                  </div>
                </div>
              </div>
            </div>

            <button
              type="button"
              @click="addProduct"
              class="px-6 py-2 text-white bg-gray-700 rounded hover:bg-gray-600"
            >
              Add Product
            </button>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-4">
          <button
            type="button"
            @click="closeModal"
            class="px-6 py-2 text-white bg-gray-600 rounded hover:bg-gray-700"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-6 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 disabled:opacity-50"
            :disabled="form.processing"
          >
            <span v-if="form.processing">
              <i class="fas fa-spinner fa-spin me-2"></i>Processing...
            </span>
            <span v-else>
              <i class="fas fa-save me-2"></i>Create Return
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

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
    default: () => []
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

const emit = defineEmits(['update:open', 'close']);

const form = useForm({
  return_no: '',
  return_date: new Date().toISOString().split('T')[0],
  reason: '',
  products: [
    {
      product_id: '',
      measurement_unit_id: '',
      stock_transfer_quantity: 1,
    }
  ],
});

const selectedProducts = ref({});
const productUnits = ref({});

watch(
  () => props.open,
  (newVal) => {
    if (newVal) {
      form.return_no = props.returnNo;
      form.return_date = new Date().toISOString().split('T')[0];
      form.reason = '';
      form.products = [
        {
          product_id: '',
          measurement_unit_id: '',
          stock_transfer_quantity: 1,
        }
      ];
      selectedProducts.value = {};
      productUnits.value = {};
      form.clearErrors();
    }
  }
);

const onProductSelect = (index) => {
  const productId = form.products[index].product_id;
  const product = props.products.find(p => p.id == productId);
  selectedProducts.value[index] = product;
  
  if (product && product.measurement_units) {
    productUnits.value[index] = product.measurement_units;
    if (product.measurement_units.length > 0) {
      form.products[index].measurement_unit_id = product.measurement_units[0].id;
    }
  }
};

const addProduct = () => {
  form.products.push({
    product_id: '',
    measurement_unit_id: '',
    stock_transfer_quantity: 1,
  });
};

const removeProduct = (index) => {
  form.products.splice(index, 1);
  delete selectedProducts.value[index];
};

const closeModal = () => {
  if (props.inline) {
    emit('close');
  } else {
    emit('update:open', false);
  }
};

const submitForm = () => {
  form.return_no = props.returnNo;

  form.post(route('stock-transfer-returns.store'), {
    onSuccess: () => {
      if (props.inline) {
        router.reload();
      } else {
        closeModal();
        router.reload();
      }
    },
    onError: (errors) => {
      console.error('Form submission errors:', errors);
    }
  });
};
</script>
