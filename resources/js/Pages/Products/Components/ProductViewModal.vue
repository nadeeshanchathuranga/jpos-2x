 
<template>
  <Modal :show="open" @close="closeModal" max-width="4xl">
    <div
      class="p-6 bg-gray-50 max-h-[90vh] overflow-y-auto [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]"
    >
      <!-- Modal Header with Title and Close Button -->
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-blue-600">Product Details</h2>
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
            />
          </svg>
        </button>
      </div>

      <!-- Barcode Print Quantity Section -->
      <div class="p-4 mb-4 bg-white rounded-xl shadow-sm border border-gray-200">
        <label class="block mb-2 text-sm font-semibold text-gray-700">
          🖨️ Number of Barcodes to Print
        </label>
        <div class="flex gap-3">
          <!-- Quantity Input (1-100) -->
          <input
            v-model.number="barcodeQuantity"
            type="number"
            min="1"
            max="100"
            class="flex-1 px-3 py-2 text-sm text-gray-800 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Enter quantity (1-100)"
          />
          <!-- Print Button with Current Quantity Display -->
          <button
            @click="printBarcode"
            class="px-6 py-2 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg transition-all duration-200 hover:shadow-sm"
          >
            Print Barcode ({{ barcodeQuantity }})
          </button>
        </div>
      </div>

      <!-- Product Image Display (if available) -->
      <div
        v-if="product?.image"
        class="flex justify-center p-4 mb-4 bg-white rounded-xl shadow-sm border border-gray-200"
      >
        <img
          :src="`/storage/${product.image}`"
          :alt="product.name"
          class="max-w-xs rounded-lg shadow-sm"
        />
      </div>

      <div class="space-y-4 text-gray-800">
        <!-- SECTION 1: Basic Information -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200">
          <h3 class="mb-3 text-lg font-semibold text-blue-600 flex items-center gap-2">
            📋 Basic Information
          </h3>
          <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
            <div class="p-3 bg-white rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">Product Name</p>
              <p class="text-sm font-medium text-gray-800">
                {{ product?.name || "N/A" }}
              </p>
            </div>
            <div class="p-3 bg-white rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">Barcode</p>
              <p class="text-sm font-medium text-gray-800">
                {{ product?.barcode || "N/A" }}
              </p>
            </div>
            <div class="p-3 bg-white rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">Status</p>
              <span
                :class="{
                  'bg-red-500 text-white px-3 py-1 rounded text-sm': product?.status == 0,
                  'bg-green-500 text-white px-3 py-1 rounded text-sm':
                    product?.status == 1,
                }"
              >
                {{ product?.status == 1 ? "Active" : "Inactive" }}
              </span>
            </div>
            <div class="p-3 bg-white rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">Brand</p>
              <p class="text-sm font-medium text-gray-800">
                {{ product?.brand?.name || "N/A" }}
              </p>
            </div>
            <div class="p-3 bg-white rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">Category</p>
              <p class="text-sm font-medium text-gray-800">
                {{
                  product?.category?.hierarchy_string
                    ? product.category.hierarchy_string + " → " + product.category.name
                    : product?.category?.name || "N/A"
                }}
              </p>
            </div>
            <div class="p-3 bg-white rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">Type</p>
              <p class="text-sm font-medium text-gray-800">
                {{ product?.type?.name || "N/A" }}
              </p>
            </div>
          </div>
        </div>

        <!-- SECTION 2: Pricing Information -->
        <div class="bg-white rounded-xl p-4 shadow-lg border border-white/60">
          <h3 class="mb-3 text-lg font-semibold text-green-600 flex items-center gap-2">
            💰 Pricing Information ({{ page.props.currency || "" }})
          </h3>
          <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
            <div class="p-3 bg-white rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">Purchase Price</p>
              <p class="text-base font-bold text-green-600">
                {{ formatPrice(fetchedBatchPrice || product?.purchase_price) }}
              </p>
              <p v-if="fetchedBatchPrice" class="text-xs text-gray-500 mt-1">
                (From goods received note)
              </p>
            </div>
            <div class="p-3 bg-white rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">Wholesale Price</p>
              <p class="text-base font-bold text-blue-600">
                {{ formatPrice(product?.wholesale_price) }}
              </p>
            </div>
            <div class="p-3 bg-white rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">Retail Price</p>
              <p class="text-base font-bold text-amber-600">
                {{ formatPrice(product?.retail_price) }}
              </p>
            </div>
            <div class="p-3 bg-white rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">Discount</p>
              <p class="text-sm font-medium text-gray-800">
                {{ product?.discount?.name || "No Discount" }}

                {{ product?.discount?.value }}
                {{ product?.discount?.type === 0 ? "%" : page.props.currency || "" }}
              </p>
            </div>
            <div class="p-3 bg-white rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">Tax</p>
              <p class="text-sm font-medium text-gray-800">
                {{ product?.tax?.name || "No Tax" }}
              </p>
            </div>
          </div>
        </div>

        <!-- SECTION 3: Inventory & Units -->
        <div class="bg-white rounded-xl p-4 shadow-lg border border-white/60">
          <h3 class="mb-3 text-lg font-semibold text-orange-600 flex items-center gap-2">
            📦 Inventory & Units
          </h3>
          <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
            <div class="p-3 bg-white rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">
                Shop Quantity
                <span
                  v-if="unitLabel(product?.sales_unit, product?.sales_unit_id)"
                  class="text-green-600"
                >
                  ({{ unitLabel(product?.sales_unit, product?.sales_unit_id) }})
                </span>
              </p>
              <p class="text-lg font-bold text-amber-600">
                {{ displayValue(product?.shop_quantity_in_sales_unit, "0") }}
                <span
                  v-if="isLow(product?.shop_quantity_in_sales_unit, product?.shop_low_stock_margin)"
                  class="ml-2 text-sm text-red-500"
                  >⚠️ Low</span
                >
              </p>
            </div>
            <div class="p-3 bg-white rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">
                Shop Low Stock Margin
                <span
                  v-if="unitLabel(product?.sales_unit, product?.sales_unit_id)"
                  class="text-green-600"
                >
                  ({{ unitLabel(product?.sales_unit, product?.sales_unit_id) }})
                </span>
              </p>
              <p class="text-sm font-medium text-gray-800">
                {{ displayValue(product?.shop_low_stock_margin, "Not Set") }}
              </p>
            </div>
          </div>

          <div
            class="grid grid-cols-1 gap-3 md:grid-cols-3 p-3 bg-white/50 rounded-lg border border-gray-200 mt-3"
          >
            <div class="p-3 bg-white rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">
                Store Quantity
                <span
                  v-if="unitLabel(product?.purchase_unit, product?.purchase_unit_id)"
                  class="text-blue-600"
                >
                  ({{ unitLabel(product?.purchase_unit, product?.purchase_unit_id) }})
                </span>
              </p>
              <p class="text-lg font-bold text-blue-600">
                {{ displayValue(product?.store_quantity_in_purchase_unit, "N/A") }}
                <span
                  v-if="isLow(product?.store_quantity_in_purchase_unit, product?.store_low_stock_margin)"
                  class="ml-2 text-sm text-red-500"
                  >⚠️ Low</span
                >
              </p>
            </div>
            <div class="p-3 bg-white rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">
                Store Quantity 
                <span
                  v-if="unitLabel(product?.transfer_unit, product?.transfer_unit_id)"
                  class="text-blue-600"
                >
                  ({{ unitLabel(product?.transfer_unit, product?.transfer_unit_id) }})
                </span>
              </p>
              <p class="text-lg font-bold text-blue-600">
                {{ displayValue(product?.loose_bundles, "N/A") }}
                <span
                  v-if="isLow(product?.loose_bundles, product?.store_low_stock_margin)"
                  class="ml-2 text-sm text-red-500"
                  >⚠️ Low</span
                >
              </p>
            </div>
            <div class="p-3 bg-white rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">
                Store quantity
                <span
                  v-if="unitLabel(product?.sales_unit, product?.sales_unit_id)"
                  class="text-green-600"
                >
                  ({{ unitLabel(product?.sales_unit, product?.sales_unit_id) }})
                </span>
              </p>
              <p class="text-lg font-bold text-green-600">
                {{ displayValue(product?.store_quantity_in_sale_unit, "0") }}
              </p>
            </div>
            <div class="p-3 bg-white rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">
                Store Low Stock Margin
                <span
                  v-if="unitLabel(product?.purchase_unit, product?.purchase_unit_id)"
                  class="text-blue-600"
                >
                  ({{ unitLabel(product?.purchase_unit, product?.purchase_unit_id) }})
                </span>
              </p>
              <p class="text-sm font-medium text-gray-800">
                {{ displayValue(product?.store_low_stock_margin, "Not Set") }}
              </p>
            </div>
          </div>

          <div
            class="grid grid-cols-1 md:grid-cols-3 gap-3 p-3 bg-white/50 rounded-lg border border-gray-200 mt-3"
          >
            <div class="p-3 bg-white/70 rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">Purchase Unit</p>
              <p class="text-sm font-medium text-gray-800">
                {{
                  product?.purchase_unit?.name ||
                  displayValue(product?.purchase_unit_id, "N/A")
                }}
              </p>
            </div>
            <div class="p-3 bg-white/70 rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">Sales Unit</p>
              <p class="text-sm font-medium text-gray-800">
                {{
                  product?.sales_unit?.name || displayValue(product?.sales_unit_id, "N/A")
                }}
              </p>
            </div>
            <div class="p-3 bg-white/70 rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">Transfer Unit</p>
              <p class="text-sm font-medium text-gray-800">
                {{
                  product?.transfer_unit?.name ||
                  displayValue(product?.transfer_unit_id, "N/A")
                }}
              </p>
            </div>
          </div>
        </div>

        <!-- SECTION 4: Unit Conversion Rates -->
        <div class="bg-white rounded-xl p-4 shadow-lg border border-white/60">
          <h3 class="mb-3 text-lg font-semibold text-purple-600 flex items-center gap-2">
            🔄 Unit Conversion Rates
          </h3>
          <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <div class="p-3 bg-white rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">Purchase → Transfer Rate</p>
              <p class="text-sm font-medium text-gray-800">
                {{ displayValue(product?.purchase_to_transfer_rate, "0") }}
              </p>
            </div>
            <div class="p-3 bg-white rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">Transfer → Sales Rate</p>
              <p class="text-sm font-medium text-gray-800">
                {{ displayValue(product?.transfer_to_sales_rate, "0") }}
              </p>
            </div>
          </div>
        </div>

        <!-- SECTION 5: Additional Information -->
        <div class="bg-white rounded-xl p-4 shadow-lg border border-white/60">
          <h3 class="mb-3 text-lg font-semibold text-indigo-600 flex items-center gap-2">
            ⚙️ Additional Information
          </h3>
          <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <div class="p-3 bg-white rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">Return Product Allowed</p>
              <p class="text-sm font-medium">
                <span
                  :class="product?.return_product ? 'text-green-600' : 'text-red-600'"
                >
                  {{ product?.return_product ? "Yes" : "No" }}
                </span>
              </p>
            </div>
            <div class="p-3 bg-white rounded-lg border border-gray-200">
              <p class="text-xs text-gray-600">Created At</p>
              <p class="text-sm font-medium text-gray-800">
                {{ formatDate(product?.created_at) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Footer with Close Button -->
      <!-- <div class="flex justify-end gap-3 pt-4 mt-4 border-t border-gray-300">
        <button
          @click="closeModal"
          class="px-8 py-2.5 rounded-full font-semibold text-sm bg-gray-500 text-white hover:bg-gray-600 transition-all duration-200"
        >
          Close
        </button>
      </div> -->
    </div>
  </Modal>
</template>

<script setup>
/**
 * Product View Modal Component Script
 *
 * Handles product details display and barcode printing functionality
 * Uses JsBarcode library for generating CODE128 barcodes
 */

import { ref, watch, nextTick, computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import JsBarcode from "jsbarcode";
import Modal from "@/Components/Modal.vue";

/**
 * Component Props
 * @property {Boolean} open - Controls modal visibility
 * @property {Object} product - Product data to display
 */
const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  product: {
    type: Object,
    default: null,
  },
});

/**
 * Component Emits
 * @event update:open - Emitted when modal needs to close
 */
const emit = defineEmits(["update:open"]);

/**
 * Reactive State Variables
 *
 * barcodeElement: Reference to SVG element for barcode rendering
 * barcodeQuantity: Number of barcode labels to print (1-100)
 */
const barcodeElement = ref(null);
const barcodeQuantity = ref(1);

// expose Inertia page props for currency display
const page = usePage();

/**
 * Close Modal Handler
 * Emits event to parent component to update modal state
 */
const closeModal = () => {
  emit("update:open", false);
};

/**
 * Format Price for Display
 * Converts numeric price to fixed 2 decimal format
 *
 * @param {Number} price - Raw price value
 * @returns {String} Formatted price (e.g., "123.45")
 */
const formatPrice = (price) => {
  if (!price) return "0.00";
  return parseFloat(price).toFixed(2);
};

/**
 * Format Date for Display
 * Converts date string to readable format
 *
 * @param {String} date - Date string from database
 * @returns {String} Formatted date (e.g., "December 2, 2025")
 */
const formatDate = (date) => {
  if (!date) return "N/A";
  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

/**
 * Generate Barcode
 * Uses JsBarcode to render barcode in SVG element
 * Called when modal opens or product changes
 */
const generateBarcode = () => {
  nextTick(() => {
    if (barcodeElement.value && props.product?.barcode) {
      try {
        JsBarcode(barcodeElement.value, props.product.barcode, {
          format: "CODE128",
          width: 2,
          height: 80,
          displayValue: false,
        });
      } catch (error) {
        console.error("Barcode generation error:", error);
      }
    }
  });
};

/**
 * Print Barcode Labels
 * Generates multiple barcode labels and opens print dialog
 * Each label includes: barcode, product name, and retail price
 * Label size: 35mm x 22mm (standard label size)
 *
 * Process:
 * 1. Validate product has barcode
 * 2. Clamp quantity between 1-100
 * 3. Generate HTML for all labels
 * 4. Open new window with formatted print layout
 * 5. Use JsBarcode to render all barcodes
 * 6. Trigger print dialog
 */
const printBarcode = () => {
  // Validate barcode exists
  if (!props.product?.barcode) {
    alert("No barcode available for this product");
    return;
  }

  // Clamp quantity between 1 and 100
  const quantity = Math.min(Math.max(barcodeQuantity.value || 1, 1), 100);

  // Generate HTML for all barcode labels
  const currencyLabel = page.props.currencySymbol || page.props.currency || "";
  let barcodesHTML = "";
  for (let i = 0; i < quantity; i++) {
    barcodesHTML += `
      <div class="barcode-item">
        <svg id="printBarcode${i}"></svg>
        <p class="barcode-number">${props.product?.barcode || ""}</p>
        <p class="product-name">${props.product?.name || ""}</p>
        <p class="product-price">${currencyLabel} ${formatPrice(
      props.product?.retail_price
    )}</p>
      </div>
    `;
  }

  // Open new print window
  const printWindow = window.open("", "", "width=800,height=600");

  // Generate complete HTML document for printing
  const barcodeHTML = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Print Barcodes - ${props.product?.name || "Product"}</title>
      <style>
        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
        }
        body {
          margin: 10px;
          font-family: Arial, sans-serif;
        }
        .barcodes-container {
          display: flex;
          flex-wrap: wrap;
          gap: 5mm;
          padding: 5mm;
        }
        .barcode-item {
          width: 35mm;
          height: 22mm;
          text-align: center;
          padding: 1mm;
          border: 1px solid #ddd;
          page-break-inside: avoid;
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-items: center;
          overflow: hidden;
        }
        .barcode-item svg {
          max-width: 32mm;
          max-height: 12mm;
        }
        .barcode-item p {
          margin: 0;
          line-height: 1.2;
        }
        .product-name {
          font-size: 7px;
          font-weight: bold;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
          max-width: 32mm;
          margin-top: 1mm;
        }
        .barcode-number {
          font-size: 7px;
          font-weight: bold;
          margin-top: 0.5mm;
        }
        .product-price {
          font-size: 8px;
          font-weight: bold;
          margin-top: 0.5mm;
        }
        @media print {
          body {
            margin: 0;
          }
          .barcode-item {
            border: 1px solid #ccc;
          }
        }
      </style>
    </head>
    <body>
      <div class="barcodes-container">
        ${barcodesHTML}
      </div>
      <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
      <script>
        window.onload = function() {
          for (let i = 0; i < ${quantity}; i++) {
            try {
              JsBarcode("#printBarcode" + i, "${props.product?.barcode || ""}", {
                format: "CODE128",
                width: 1.5,
                height: 30,
                displayValue: false,
                margin: 0
              });
            } catch (e) {
              console.error('Barcode generation error:', e);
            }
          }
          setTimeout(function() {
            window.print();
          }, 500);
        };
      <\/script>
    </body>
    </html>
  `;
  printWindow.document.write(barcodeHTML);
  printWindow.document.close();
};

/**
 * Watch for Modal Open State Changes
 * When modal opens, generate barcode and reset quantity to 1
 */
watch(
  () => props.open,
  (newVal) => {
    if (newVal) {
      generateBarcode();
      barcodeQuantity.value = 1; // Reset quantity when modal opens
    }
  }
);

/**
 * Watch for Product Changes
 * Regenerate barcode when product data changes (if modal is open)
 */
watch(
  () => props.product,
  () => {
    if (props.open) {
      generateBarcode();
    }
  }
);

const unitLabel = (unitObj, id) => unitObj?.name || (id ? `#${id}` : null);
const displayValue = (value, fallback = "N/A") =>
  value === null || value === undefined || value === "" ? fallback : value;
const isLow = (qty, margin) =>
  qty !== undefined && margin !== undefined && Number(qty) <= Number(margin);

const storeQtyInPurchase = computed(() => {
  const qty = Number(props.product?.store_quantity_in_purchase_unit);
  const pt = Number(props.product?.purchase_to_transfer_rate) || 0;
  const ts = Number(props.product?.transfer_to_sales_rate) || 0;
  if (!qty || !pt || !ts) return null;
  return (qty / (pt * ts)).toFixed(2);
});

/**
 * Fetch purchase price from goods_received_notes_products table
 * based on product_id and batch_number
 */
const fetchedBatchPrice = ref(null);

const fetchPurchasePriceByBatch = async (productId, batchNumber) => {
  if (!productId || !batchNumber) {
    fetchedBatchPrice.value = null;
    return;
  }

  try {
    const response = await fetch('/products/pricing-by-batch', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
      },
      body: JSON.stringify({
        product_id: productId,
        batch_number: batchNumber,
      }),
    });

    if (response.ok) {
      const data = await response.json();
      if (data.success && data.purchase_price) {
        fetchedBatchPrice.value = parseFloat(data.purchase_price).toFixed(2);
      } else {
        fetchedBatchPrice.value = null;
      }
    } else {
      fetchedBatchPrice.value = null;
    }
  } catch (error) {
    console.error('Error fetching purchase price:', error);
    fetchedBatchPrice.value = null;
  }
};

// Watch for modal open and fetch purchase price if product has batch
const watchOpen = watch(() => props.open, (newVal) => {
  if (newVal && props.product && props.product.current_batch) {
    fetchPurchasePriceByBatch(props.product.id, props.product.current_batch.batch_number);
  }
});
</script>
