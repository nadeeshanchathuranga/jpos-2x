<template>
  <div
    v-if="open"
    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-50"
  >
    <div
      class="relative w-full max-w-4xl p-6 mx-4 my-8 bg-gray-50 rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]"
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
                    {{ prod.name }} (Shop: {{ prod.shop_quantity }})
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
                >
                  <option value="">Select Unit</option>
                  <option
                    v-for="unit in productUnits[index] || measurementUnits"
                    :key="unit.id"
                    :value="unit.id"
                  >
                    {{ unit.name }}{{ unit.symbol ? " (" + unit.symbol + ")" : "" }}
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
                <div v-if="selectedProducts[index]" class="mt-1 text-xs text-gray-500">
                  Available: {{ selectedProducts[index].shop_quantity }}
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
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onUnmounted } from "vue";
import { useForm, router } from "@inertiajs/vue3";
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
      form.clearErrors();
    }
  }
);

const onProductSelect = (index) => {
  const productId = form.products[index].product_id;
  const product = props.products.find((p) => p.id == productId);
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
