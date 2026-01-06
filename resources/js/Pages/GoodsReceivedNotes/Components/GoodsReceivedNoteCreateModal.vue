<template>
  <Modal :show="open" @close="close" max-width="6xl">
    <div class="p-6 bg-gray-50">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
        <h2 class="text-2xl font-bold text-blue-600">✨ Create New GRN</h2>
        <button
          type="button"
          @click="close"
          class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-200 rounded-full transition-all duration-200"
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

      <form @submit.prevent="submitForm">
        <!-- GRN DETAILS -->
        <div class="mb-4 bg-white rounded-xl p-4 shadow-sm border border-gray-200">
          <h3 class="mb-3 text-lg font-semibold text-blue-600 flex items-center gap-2">
            📋 GRN Details
          </h3>

          <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"
                >GRN Number *</label
              >
              <input
                v-model="form.goods_received_note_no"
                type="text"
                class="w-full px-3 py-2 text-sm text-gray-800 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                required
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"
                >Supplier *</label
              >
              <select
                v-model="form.supplier_id"
                class="w-full px-3 py-2 text-sm text-gray-800 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                required
              >
                <option value="">Select Supplier</option>
                <option
                  v-for="supplier in suppliers"
                  :key="supplier.id"
                  :value="supplier.id"
                >
                  {{ supplier.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"
                >GRN Date *</label
              >
              <input
                v-model="form.goods_received_note_date"
                type="date"
                class="w-full px-3 py-2 text-sm text-gray-800 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                required
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"
                >Purchase Order</label
              >
              <select
                v-model="form.purchase_order_request_id"
                @change="loadPOData"
                class="w-full px-3 py-2 text-sm text-gray-800 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Select PO (Optional)</option>
                <option v-for="po in filteredPurchaseOrders" :key="po.id" :value="po.id">
                  {{ po.order_number }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Discount</label>
              <input
                v-model.number="form.discount"
                type="number"
                step="0.01"
                class="w-full px-3 py-2 text-sm text-gray-800 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"
                >Tax Total</label
              >
              <input
                v-model.number="form.tax_total"
                type="number"
                step="0.01"
                class="w-full px-3 py-2 text-sm text-gray-800 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div></div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
            <textarea
              v-model="form.remarks"
              rows="3"
              class="w-full px-3 py-2 text-sm text-gray-800 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            ></textarea>
          </div>
        </div>

        <!-- PRODUCTS SECTION -->
        <div class="mb-4 bg-white rounded-xl p-4 shadow-sm border border-gray-200">
          <h3 class="mb-3 text-lg font-semibold text-blue-600 flex items-center gap-2">
            📦 Products
          </h3>
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b-2 border-blue-600">
                  <th class="px-4 py-3 text-blue-600 font-semibold text-sm">Product</th>
                  <th class="px-4 py-3 text-blue-600 font-semibold text-sm">Unit</th>
                  <th class="px-4 py-3 text-blue-600 font-semibold text-sm">
                    Requested Qty
                  </th>
                  <th class="px-4 py-3 text-blue-600 font-semibold text-sm">
                    Issued Qty
                  </th>
                  <th class="px-4 py-3 text-blue-600 font-semibold text-sm">
                    Purchase Price ({{ page.props.currency || "" }})
                  </th>
                  <th class="px-4 py-3 text-blue-600 font-semibold text-sm">Discount</th>
                  <th class="px-4 py-3 text-blue-600 font-semibold text-sm">
                    Total ({{ page.props.currency || "" }})
                  </th>

                </tr>
              </thead>

              <tbody>
                <tr
                  v-for="(product, index) in products"
                  :key="index"
                  class="border-b border-gray-200 hover:bg-gray-50 transition-colors duration-200"
                >
                  <td class="px-4 py-4">
                    <select
                      v-model.number="product.product_id"
                      @change="onProductSelect(index)"
                      class="w-full px-3 py-2 text-sm text-gray-800 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" disabled
                    >
                      <option :value="null">Select Product</option>
                      <option
                        v-for="prod in availableProducts"
                        :key="prod.id"
                        :value="prod.id"
                      >
                        {{ prod.name }}
                      </option>
                    </select>
                  </td>

                  <td class="px-4 py-4">
                    <select
                      v-model="product.measurement_unit_id"
                      class="w-full px-3 py-2 text-sm text-gray-800 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" disabled
                    >
                      <option value="">Select Unit</option>
                      <option
                        v-for="unit in measurementUnits"
                        :key="unit.id"
                        :value="unit.id"
                      >
                        {{ unit.name }}
                      </option>
                    </select>
                  </td>

                  <td class="px-4 py-4">
                    <input
                      v-model.number="product.requested_quantity"
                      type="number"
                      step="0.01"
                      min="0.01"
                      class="w-full px-3 py-2 text-sm text-gray-800 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" disabled
                    />
                  </td>

                  <td class="px-4 py-4">
                    <input
                      v-model.number="product.issued_quantity"
                      type="number"
                      step="0.01"
                      min="0.01"
                      @input="calculateTotal(index)"
                      class="w-full px-3 py-2 text-sm text-gray-800 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </td>

                  <td class="px-4 py-4">
                    <input
                      v-model.number="product.purchase_price"
                      type="number"
                      step="0.01"
                      min="0"
                      @input="calculateTotal(index)"
                      class="w-full px-3 py-2 text-sm text-gray-800 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" disabled
                    />
                  </td>

                  <td class="px-4 py-4">
                    <input
                      v-model.number="product.discount"
                      type="number"
                      step="0.01"
                      min="0"
                      @input="calculateTotal(index)"
                      class="w-full px-3 py-2 text-sm text-gray-800 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </td>

                  <td class="px-4 py-4">
                    <span class="font-semibold text-gray-900">
                      {{ formatNumber(product.total) }}
                    </span>
                  </td>


                </tr>

                <tr v-if="products.length === 0">
                  <td colspan="8" class="px-6 py-8 text-center text-gray-500 font-medium">
                    No products added yet. Click "Add Product" to start.
                  </td>
                </tr>
              </tbody>

              <tfoot
                v-if="products.length > 0"
                class="bg-gray-100 border-t-2 border-gray-300"
              >
                <tr>
                  <td
                    colspan="6"
                    class="px-4 py-3 text-right font-semibold text-gray-900"
                  >
                    Grand Total:
                  </td>
                  <td class="px-4 py-3 font-bold text-lg text-gray-900">
                    {{ formatNumber(grandTotal) }} ({{ page.props.currency || "" }})
                  </td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
          <button
            type="button"
            @click="close"
            class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-[5px] hover:bg-gray-50 transition-all duration-200"
          >
            Cancel
          </button>

          <button
            type="submit"
            :disabled="products.length === 0"
            class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-[5px] hover:bg-blue-700 disabled:opacity-50 transition-all duration-200"
          >
            Create GRN
          </button>
        </div>
      </form>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
const page = usePage();

import { logActivity } from "@/composables/useActivityLog";

const props = defineProps({
  open: Boolean,
  suppliers: Array,
  measurementUnits: Array,

  purchaseOrders: Array,
  availableProducts: Array,
  grnNumber: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["update:open"]);

const form = ref({
  goods_received_note_no: props.grnNumber,
  supplier_id: "",
  goods_received_note_date: new Date().toISOString().split("T")[0],
  purchase_order_request_id: "",
  discount: 0,
  tax_total: 0,
  remarks: "",
});

const products = ref([]);

const grandTotal = computed(() => {
  const productsTotal = products.value.reduce(
    (sum, product) => sum + (parseFloat(product.total) || 0),
    0
  );

  const discount = parseFloat(form.value.discount) || 0;
  const taxTotal = parseFloat(form.value.tax_total) || 0;

  return productsTotal - discount + taxTotal;
});

// Filter out completed and cancelled (inactive) purchase orders so they don't appear in the GRN dropdown
const filteredPurchaseOrders = computed(() => {
  const list = props.purchaseOrders || [];
  return list.filter((po) => {
    const status = (po.status || "").toString().toLowerCase();
    return status !== "completed" && status !== "inactive";
  });
});

const close = () => {
  emit("update:open", false);
  resetForm();
};

const resetForm = () => {
  form.value = {
    goods_received_note_no: props.grnNumber,
    supplier_id: "",
    goods_received_note_date: new Date().toISOString().split("T")[0],
    purchase_order_request_id: "",
    discount: 0,
    tax_total: 0,
    remarks: "",
  };
  products.value = [];
};

const loadPOData = async () => {
  if (!form.value.purchase_order_request_id) {
    products.value = [];
    return;
  }

  try {
    const response = await fetch(`/po/${form.value.purchase_order_request_id}/details`);
    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || "Failed to load PO details");
    }

    const poProducts = data.purchaseOrderProducts || [];

    if (poProducts.length === 0) {
      console.warn("No products found in this PO");
      return;
    }

    // The backend now returns `requested_quantity` as the remaining amount
    // (original requested - already issued). Initialize `issued_quantity` to 0
    // so the user can enter the actual received amount for this GRN.
    products.value = poProducts.map((item) => {
      const remainingRequested = parseFloat(item.requested_quantity) || 0;
      const purchasePrice = parseFloat(item.price) || 0;

      return {
        product_id: item.product_id,
        measurement_unit_id: item.measurement_unit_id,
        requested_quantity: remainingRequested,
        // Start with 0 received for this GRN; user will input the issued_quantity
        issued_quantity: 0,
        purchase_price: purchasePrice,
        discount: 0,
        unit: item.name || "",
        total: 0,
      };
    });
  } catch (error) {
    console.error("Failed to load PO data:", error);
    alert("Failed to load Purchase Order details: " + error.message);
  }
};



const onProductSelect = (index) => {
  const product = products.value[index];
  const selectedProduct = props.availableProducts.find(
    (p) => p.id === product.product_id
  );

  if (selectedProduct) {
    product.purchase_price = selectedProduct.price || 0;
    product.measurement_unit_id = selectedProduct.measurement_unit_id;
    product.unit =
      selectedProduct.purchaseUnit?.name ||
      selectedProduct.measurementUnit?.name ||
      "N/A";
    calculateTotal(index);
  }
};

const calculateTotal = (index) => {
  const p = products.value[index];
  // Use issued_quantity for GRN line totals (actual received amount), fallback to requested_quantity
  const qty = parseFloat(p.issued_quantity ?? p.requested_quantity) || 0;
  const price = parseFloat(p.purchase_price) || 0;
  const discount = parseFloat(p.discount) || 0;

  p.total = qty * price - discount;
};

const formatNumber = (number) => {
  return parseFloat(number || 0).toLocaleString("en-US", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

// Reset form when modal opens
watch(
  () => props.open,
  (newVal) => {
    if (newVal) {
      resetForm();
    }
  }
);

const submitForm = () => {
  const payload = {
    ...form.value,
    products: products.value,
  };

  router.post(route("good-receive-notes.store"), payload, {
    onSuccess: async () => {
      // Log create activity
      await logActivity("create", "goods_received_notes", {
        grn_number: form.value.goods_received_note_no,
        grn_date: form.value.goods_received_note_date,
        supplier_id: form.value.supplier_id,
        purchase_order_id: form.value.purchase_order_request_id,
        products_count: products.value.length,
      });

      close();
    },
    onError: (e) => console.error("GRN create error:", e),
  });
};
</script>
