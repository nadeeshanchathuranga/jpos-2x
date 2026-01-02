<template>
  <div
    v-if="open"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
    @click.self="closeModal"
  >
    <div
      class="relative w-full max-w-6xl bg-gray-50 rounded-2xl max-h-[90vh] overflow-y-auto shadow-xl"
    >
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h2 class="text-2xl font-bold text-blue-600">✨ Create Purchase Order Request</h2>
        <button
          @click="closeModal"
          class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-200 rounded-full transition-all duration-200"
          type="button"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="w-6 h-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>

      <form @submit.prevent="submitForm" class="p-6">
        <!-- Order Information -->
        <div class="mb-4 bg-white rounded-xl p-4 shadow-sm border border-gray-200">
          <h3 class="mb-3 text-lg font-semibold text-blue-600 flex items-center gap-2">
            📋 Order Information
          </h3>
          <div>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
              <div>
                <label class="block mb-2 text-sm font-medium text-gray-700"
                  >Order Number</label
                >
                <input
                  type="text"
                  class="w-full px-3 py-2 text-sm text-gray-800 bg-gray-100 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :value="orderNumber"
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
                  :class="form.errors.order_date ? 'border-red-500' : 'border-gray-300'"
                  v-model="form.order_date"
                  :min="new Date().toISOString().split('T')[0]"
                />
                <div v-if="form.errors.order_date" class="mt-1 text-sm text-red-500">
                  {{ form.errors.order_date }}
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
                  disabled
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
        </div>

        <!-- Products Table -->
        <div class="mb-4 bg-white rounded-xl p-4 shadow-sm border border-gray-200">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold text-blue-600 flex items-center gap-2">
              📦 Products
            </h3>
            <button
              type="button"
              @click="addProduct"
              class="px-4 py-2 text-xs font-medium text-white bg-green-600 rounded-[5px] hover:bg-green-700 transition-all duration-200"
            >
              + Add Product
            </button>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b-2 border-blue-600">
                  <th class="px-4 py-3 text-blue-600 font-semibold text-sm text-left">
                    Product
                  </th>
                  <th class="px-4 py-3 text-blue-600 font-semibold text-sm text-left">
                    Unit
                  </th>
                  <th class="px-4 py-3 text-blue-600 font-semibold text-sm text-center">
                    Quantity
                  </th>
                  <th class="px-4 py-3 text-blue-600 font-semibold text-sm text-center">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(product, index) in form.products"
                  :key="index"
                  class="border-b border-gray-200 hover:bg-gray-50 transition-colors duration-200"
                >
                  <td class="px-4 py-4">
                    <div
                      v-if="product.product_id"
                      class="text-sm text-gray-900 font-medium"
                    >
                      {{
                        product.product_obj
                          ? product.product_obj.name
                          : getProductName(product.product_id)
                      }}
                    </div>
                    <div v-else>
                      <select
                        class="w-full px-3 py-2 text-sm text-gray-800 bg-white border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        :class="
                          form.errors[`products.${index}.product_id`]
                            ? 'border-red-500'
                            : 'border-gray-300'
                        "
                        v-model="form.products[index].product_id"
                        @change="onProductChange(index)"
                      >
                        <option value="">Select Product</option>
                        <option
                          v-for="option in allProducts"
                          :key="option.id"
                          :value="option.id"
                        >
                          {{ option.name }}
                        </option>
                      </select>
                      <div
                        v-if="form.errors[`products.${index}.product_id`]"
                        class="mt-1 text-xs text-red-500"
                      >
                        {{ form.errors[`products.${index}.product_id`] }}
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-4">
                    <span class="text-sm text-gray-700">{{
                      product.product_obj
                        ? product.product_obj.measurement_unit &&
                          product.product_obj.measurement_unit.name
                          ? product.product_obj.measurement_unit.name
                          : product.product_obj.purchaseUnit &&
                            product.product_obj.purchaseUnit.name
                          ? product.product_obj.purchaseUnit.name
                          : "N/A"
                        : getUnitName(product.product_id)
                    }}</span>
                  </td>
                  <td class="px-4 py-4 text-center">
                    <input
                      type="number"
                      class="w-24 px-3 py-2 text-sm text-gray-800 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      v-model.number="form.products[index].requested_quantity"
                      min="1"
                      step="1"
                    />
                    <div
                      v-if="form.errors[`products.${index}.requested_quantity`]"
                      class="mt-1 text-xs text-red-500"
                    >
                      {{ form.errors[`products.${index}.requested_quantity`] }}
                    </div>
                  </td>
                  <td class="px-4 py-4 text-center">
                    <button
                      type="button"
                      class="px-4 py-2 text-xs font-medium text-white bg-red-600 rounded-[5px] hover:bg-red-700 disabled:opacity-50 transition-all duration-200"
                      @click="removeProduct(index)"
                      :disabled="form.products.length <= 1"
                    >
                      Remove
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
          <button
            type="button"
            @click="closeModal"
            class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-[5px] hover:bg-gray-50 transition-all duration-200"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-[5px] hover:bg-blue-700 disabled:opacity-50 transition-all duration-200"
            :disabled="form.processing"
          >
            <span v-if="form.processing"> Creating... </span>
            <span v-else> Create POR </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { watch, computed, onUnmounted } from "vue";
import { useForm, router, usePage } from "@inertiajs/vue3";
import { logActivity } from "@/composables/useActivityLog";

const page = usePage();

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  products: {
    type: Array,
    required: true,
  },
  allProducts: {
    type: Array,
    default: () => [],
  },
  measurementUnits: {
    type: Array,
    default: () => [],
  },
  users: {
    type: Array,
    required: true,
  },
  orderNumber: {
    type: String,
    required: true,
  },
});

const emit = defineEmits(["update:open"]);

// Low-stock products provided via props (already filtered server-side)
const lowStockProducts = computed(() => props.products || []);
// Full product list for dropdowns
const allProducts = computed(() => props.allProducts || []);

const form = useForm({
  order_number: "",
  order_date: new Date().toISOString().split("T")[0],
  user_id: "",
  products: [], // Will be populated with all products
});

// Initialize form with all products
watch(
  () => props.products,
  (newProducts) => {
    if (newProducts && newProducts.length > 0) {
      form.products = newProducts.map((p) => ({
        product_id: p.id,
        requested_quantity: 1,
        measurement_unit_id: p.measurement_unit_id || p.purchase_unit_id || "",
        product_obj: p,
      }));
    } else {
      form.products = [
        {
          product_id: "",
          requested_quantity: 1,
          measurement_unit_id: "",
          product_obj: null,
        },
      ];
    }
  },
  { immediate: true }
);

// Reset form when modal opens/closes
watch(
  () => props.open,
  (newVal) => {
    if (newVal) {
      // Lock body scroll
      document.body.style.overflow = 'hidden';
      
      form.order_number = props.orderNumber;
      form.order_date = new Date().toISOString().split("T")[0];
      // Auto-fill logged-in user
      form.user_id = page.props.auth?.user?.id || "";
      form.clearErrors();

      // Initialize products array
      if (props.products && props.products.length > 0) {
        form.products = props.products.map((p) => ({
          product_id: p.id,
          requested_quantity: 1,
          measurement_unit_id: p.measurement_unit_id || p.purchase_unit_id || "",
          product_obj: p,
        }));
      } else {
        form.products = [
          {
            product_id: "",
            requested_quantity: 1,
            measurement_unit_id: "",
            product_obj: null,
          },
        ];
      }
    } else {
      // Unlock body scroll
      document.body.style.overflow = '';
    }
  }
);

// Cleanup on unmount
onUnmounted(() => {
  document.body.style.overflow = "";
});

const getProductById = (id) => {
  if (!id && id !== 0) return undefined;
  const pid = parseInt(id);
  // try full product list first
  let p = allProducts.value.find((item) => item.id == pid || item.id === id);
  if (p) return p;
  // fallback to low-stock list
  p = lowStockProducts.value.find((item) => item.id == pid || item.id === id);
  return p;
};

const getProductName = (id) => getProductById(id)?.name || "Select product";
const getUnitName = (id) => getProductById(id)?.measurement_unit?.name || "N/A";

const onProductChange = (index) => {
  const selectedId = form.products[index].product_id;
  const product = getProductById(selectedId);
  form.products[index].measurement_unit_id =
    product?.measurement_unit_id || product?.purchase_unit_id || "";
  // store the full product object on the row for immediate display
  form.products[index].product_obj = product || null;
};

const addProduct = () => {
  form.products.push({
    product_id: "",
    requested_quantity: 1,
    measurement_unit_id: "",
    product_obj: null,
  });
};

const removeProduct = (index) => {
  if (form.products.length <= 1) return;
  form.products.splice(index, 1);
};

const closeModal = () => {
  emit("update:open", false);
  form.reset();
};

const submitForm = () => {
  // Filter products with a selection and quantity > 0
  const productsWithQuantity = form.products.filter(
    (p) => p.product_id && p.requested_quantity > 0
  );

  if (productsWithQuantity.length === 0) {
    alert("Please select at least one product and enter a quantity");
    return;
  }

  // Transform data to ensure proper types
  const formattedData = {
    order_number: props.orderNumber,
    order_date: form.order_date,
    user_id: parseInt(form.user_id) || form.user_id,
    products: productsWithQuantity.map((p) => ({
      product_id: parseInt(p.product_id) || p.product_id,
      requested_quantity: parseInt(p.requested_quantity) || 0,
      measurement_unit_id: parseInt(p.measurement_unit_id) || p.measurement_unit_id,
    })),
  };

  form
    .transform(() => formattedData)
    .post(route("purchase-order-requests.store"), {
      onSuccess: async () => {
        // Log create activity
        await logActivity("create", "purchase_orders", {
          order_number: form.order_number,
          order_date: form.order_date,
          user_id: form.user_id,
          products_count: form.products.length,
        });

        closeModal();
        router.reload();
      },
      onError: (errors) => {
        console.error("Validation errors:", errors);
      },
    });
};
</script>

<style scoped>
/* Tailwind CSS is used for styling */
</style>
