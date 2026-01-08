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
              class="w-full max-w-6xl p-6 overflow-hidden text-left align-middle transition-all transform bg-gray-50 shadow-2xl rounded-2xl max-h-[85vh] overflow-y-auto"
            >
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-blue-600">
          ✨ Create Product Transfer Request
        </h2>
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
        <!-- Order Information -->
        <div class="mb-4 bg-white rounded-xl p-4 shadow-sm border border-gray-200">
          <h3 class="mb-3 text-lg font-semibold text-blue-600 flex items-center gap-2">
            📋 Order Information
          </h3>
          <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <div>
              <label class="block mb-2 text-sm font-medium text-gray-700"
                >Transfer Number</label
              >
              <input
                type="text"
                class="w-full px-3 py-2 text-sm text-gray-800 bg-gray-100 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent font-medium"
                :value="transferNo"
                readonly
              />
            </div>
            <div>
              <label class="block mb-2 text-sm font-medium text-gray-700">
                Order Date <span class="text-red-500">*</span>
              </label>
              <input
                type="date"
                class="w-full px-3 py-2 text-sm text-gray-800 bg-white border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="form.errors.request_date ? 'border-red-500' : 'border-gray-300'"
                v-model="form.request_date"
              />
              <div v-if="form.errors.request_date" class="mt-1 text-sm text-red-500">
                {{ form.errors.request_date }}
              </div>
            </div>
            <div class="md:col-span-2">
              <label class="block mb-2 text-sm font-medium text-gray-700">
                User <span class="text-red-500">*</span>
              </label>

              <select
                class="w-full px-3 py-2 text-sm text-gray-800 bg-white border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="form.errors.user_id ? 'border-red-500' : 'border-gray-300'"
                v-model="form.user_id"
              >
                <option value="">Select User</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>
              <div v-if="form.errors.user_id" class="mt-1 text-sm text-red-500">
                {{ form.errors.user_id }}
              </div>
            </div>
          </div>
        </div>

        <!-- Products -->
        <div class="mb-4 bg-white rounded-xl p-4 shadow-sm border border-gray-200">
          <h3 class="mb-3 text-lg font-semibold text-green-600 flex items-center gap-2">
            📦 Products
          </h3>
          <div>
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
                      {{ prod.name }}
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
                      form.errors[`products.${index}.unit_id`]
                        ? 'border-red-500'
                        : 'border-gray-300'
                    "
                    v-model="product.unit_id"
                  >
                    <option value="">Select Unit</option>
                    <option
                      v-for="unit in getUnitsForProduct(index)"
                      :key="unit.id"
                      :value="unit.id"
                    >
                      {{ unit.name }}
                    </option>
                  </select>
                  <div
                    v-if="form.errors[`products.${index}.unit_id`]"
                    class="mt-1 text-sm text-red-500"
                  >
                    {{ form.errors[`products.${index}.unit_id`] }}
                  </div>
                </div>

                <!-- Quantity -->
                <div class="md:col-span-3">
                  <label class="block mb-2 text-sm font-medium text-gray-700">
                    Quantity <span class="text-red-500">*</span>
                  </label>
                  <input
                    type="number"
                    class="w-full px-3 py-2 text-sm text-gray-800 bg-white border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    :class="
                      form.errors[`products.${index}.requested_quantity`]
                        ? 'border-red-500'
                        : 'border-gray-300'
                    "
                    v-model="product.requested_quantity"
                    min="1"
                  />
                  <div
                    v-if="form.errors[`products.${index}.requested_quantity`]"
                    class="mt-1 text-sm text-red-500"
                  >
                    {{ form.errors[`products.${index}.requested_quantity`] }}
                  </div>
                </div>

                <!-- Remove -->
                <div class="flex items-end md:col-span-2">
                  <button
                    v-if="form.products.length > 1"
                    type="button"
                    @click="removeProduct(index)"
                    class="w-full px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 text-sm font-medium"
                  >
                    Remove
                  </button>
                </div>
              </div>
            </div>

            <button
              type="button"
              @click="addProduct"
              class="px-6 py-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition duration-200 text-sm font-semibold flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end pt-4 mt-4 space-x-3 border-t border-gray-300">
          <button
            type="button"
            @click="closeModal"
            class="px-8 py-2.5 rounded-full font-semibold text-sm bg-gray-500 text-white hover:bg-gray-600 transition-all duration-200"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-8 py-2.5 rounded-full font-semibold text-sm bg-blue-600 text-white hover:bg-blue-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="form.processing"
          >
            <span v-if="form.processing">⏳ Creating...</span>
            <span v-else>✨ Create Request</span>
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
import { watch, ref } from "vue";
import { useForm, router } from "@inertiajs/vue3";
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
  measurementUnits: Array,
  users: {
    type: Array,
    required: true,
  },
  transferNo: {
    type: String,
    required: true,
  },
});

const emit = defineEmits(["update:open"]);

const productUnits = ref({});

const form = useForm({
  product_transfer_request_no: "",
  request_date: new Date().toISOString().split("T")[0],
  user_id: "",
  products: [
    {
      product_id: "",
      requested_quantity: 1,
      unit_id: "",
    },
  ],
});

watch(
  () => props.open,
  (newVal) => {
    if (newVal) {
      form.product_transfer_request_no = props.transferNo;
      form.request_date = new Date().toISOString().split("T")[0];
      form.user_id = "";
      form.clearErrors();

      form.products =
        form.products.length > 0
          ? form.products
          : [{ product_id: "", requested_quantity: 1, unit_id: "" }];

      form.products.forEach((p, i) => {
        initUnitsForProduct(i);
      });
    }
  }
);

const initUnitsForProduct = (index) => {
  const selectedProductId = form.products[index].product_id;
  if (!selectedProductId) {
    productUnits.value[index] = [];
    form.products[index].unit_id = "";
    return;
  }

  const product = props.products.find((p) => p.id == selectedProductId);

  if (!product) return;

  if (product.measurement_units && product.measurement_units.length > 0) {
    productUnits.value[index] = product.measurement_units;
    form.products[index].unit_id =
      form.products[index].unit_id || product.measurement_units[0].id;
  } else if (product.measurement_unit && product.measurement_unit.id) {
    productUnits.value[index] = [product.measurement_unit];
    form.products[index].unit_id = product.measurement_unit.id;
  } else if (product.measurement_unit_id) {
    const defaultUnit = props.measurementUnits.find(
      (u) => u.id == product.measurement_unit_id
    );
    productUnits.value[index] = defaultUnit ? [defaultUnit] : props.measurementUnits;
    form.products[index].unit_id = product.measurement_unit_id;
  } else {
    productUnits.value[index] = props.measurementUnits || [];
    form.products[index].unit_id = "";
  }
};

const addProduct = () => {
  form.products.push({
    product_id: "",
    requested_quantity: 1,
    unit_id: "",
  });
};

const removeProduct = (index) => {
  form.products.splice(index, 1);
};

const closeModal = () => {
  emit("update:open", false);
  form.reset();
};

const submitForm = () => {
  // Use prop value for product_transfer_request_no
  form.product_transfer_request_no = props.transferNo;

  console.log("Submitting form:", form.data());

  form.post(route("product-transfer-requests.store"), {
    onSuccess: async () => {
      // Log create activity
      await logActivity("create", "product_transfer_requests", {
        request_number: form.product_transfer_request_no,
        request_date: form.product_transfer_request_date,
        user_id: form.user_id,
        products_count: form.products.length,
      });

      closeModal();
      router.reload();
    },
    onError: (errors) => {
      console.error("Form submission errors:", errors);
    },
  });
};

const onProductSelect = (index) => {
  const selectedProductId = form.products[index].product_id;

  if (!selectedProductId) {
    form.products[index].unit_id = "";
    productUnits.value[index] = [];
    return;
  }

  const product = props.products.find((p) => p.id === parseInt(selectedProductId));

  if (product) {
    if (
      product.measurement_units &&
      Array.isArray(product.measurement_units) &&
      product.measurement_units.length > 0
    ) {
      productUnits.value[index] = product.measurement_units;
      form.products[index].unit_id =
        product.measurement_unit_id || product.measurement_units[0].id;
    } else if (product.measurement_unit && product.measurement_unit.id) {
      productUnits.value[index] = [product.measurement_unit];
      form.products[index].unit_id = product.measurement_unit.id;
    } else if (product.measurement_unit_id) {
      const defaultUnit = props.measurementUnits.find(
        (u) => u.id === product.measurement_unit_id
      );
      productUnits.value[index] = defaultUnit ? [defaultUnit] : props.measurementUnits;
      form.products[index].unit_id = product.measurement_unit_id;
    } else {
      productUnits.value[index] = props.measurementUnits || [];
      form.products[index].unit_id = "";
    }
  }
};

const getUnitsForProduct = (index) => {
  return productUnits.value[index] || props.measurementUnits || [];
};

const getUnitName = (unitId) => {
  if (!unitId) {
    return "-";
  }

  const productWithUnit = props.products.find((p) => p.measurement_unit_id == unitId);
  if (productWithUnit?.measurement_unit?.name) {
    return productWithUnit.measurement_unit.name;
  }

  if (Array.isArray(props.measurementUnits)) {
    const unit = props.measurementUnits.find((u) => u.id == unitId);
    return unit?.name || "-";
  }

  return "-";
};
</script>

<style scoped>
/* Tailwind CSS is used for styling */
</style>
