<template>
  <AppLayout>
    <!-- Main Container -->
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 p-6">
      <!-- Header Section with Navigation and Actions -->
      <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
          <!-- Back to Dashboard Button -->
          <button
            @click="$inertia.visit(route('dashboard'))"
            class="px-6 py-2.5 rounded-[5px] font-medium text-sm bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 hover:border-gray-300 transition-all duration-200"
          >
            ← Back
          </button>
          <h1 class="text-4xl font-bold text-gray-800">Store Inventory</h1>
        </div>
        <!-- Add New Adjustment Button -->
        <button
          @click="openCreateModal"
          class="px-6 py-2.5 rounded-[5px] font-medium text-sm bg-blue-600 text-white hover:bg-blue-700 hover:scale-105 transition-all duration-300"
        >
          + Add Adjustment
        </button>
      </div>

      <!-- Filters Section -->
      <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <!-- Product Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Product</label>
            <select
              v-model="filters.product_id"
              @change="applyFilters"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Products</option>
              <option v-for="product in products" :key="product.id" :value="product.id">
                {{ product.name }} ({{ product.barcode || 'No Barcode' }})
              </option>
            </select>
          </div>

          <!-- Transaction Type Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Type</label>
            <select
              v-model="filters.transaction_type"
              @change="applyFilters"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Types</option>
              <option value="adjustment">Adjustment</option>
              <option value="physical_count">Physical Count</option>
              <option value="damage">Damage</option>
              <option value="expired">Expired</option>
              <option value="return">Return</option>
              <option value="transfer_in">Transfer In</option>
              <option value="transfer_out">Transfer Out</option>
            </select>
          </div>

          <!-- Date From Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
            <input
              type="date"
              v-model="filters.date_from"
              @change="applyFilters"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- Date To Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
            <input
              type="date"
              v-model="filters.date_to"
              @change="applyFilters"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              v-model="filters.status"
              @change="applyFilters"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">All Status</option>
              <option value="pending">Pending</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Inventory Records Table Container -->
      <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <table class="w-full text-left border-collapse">
          <!-- Table Header -->
          <thead>
            <tr class="border-b-2 border-blue-600">
              <th class="px-4 py-3 text-blue-600 font-semibold text-sm">#</th>
              <th class="px-4 py-3 text-blue-600 font-semibold text-sm">Reference No</th>
              <th class="px-4 py-3 text-blue-600 font-semibold text-sm">Product</th>
              <th class="px-4 py-3 text-blue-600 font-semibold text-sm">Transaction Type</th>
              <th class="px-4 py-3 text-blue-600 font-semibold text-sm text-right">Qty Before</th>
              <th class="px-4 py-3 text-blue-600 font-semibold text-sm text-right">Change</th>
              <th class="px-4 py-3 text-blue-600 font-semibold text-sm text-right">Qty After</th>
              <th class="px-4 py-3 text-blue-600 font-semibold text-sm">Date</th>
              <th class="px-4 py-3 text-blue-600 font-semibold text-sm text-center">Status</th>
              <th class="px-4 py-3 text-blue-600 font-semibold text-sm text-center">Actions</th>
            </tr>
          </thead>
          <!-- Table Body - Inventory Rows -->
          <tbody>
            <tr
              v-for="(record, index) in inventoryRecords.data"
              :key="record.id"
              class="border-b border-gray-200 hover:bg-blue-50/50 transition-colors duration-200"
            >
              <!-- Sequential ID -->
              <td class="px-4 py-4">
                <span
                  class="inline-flex items-center justify-center w-8 h-8 rounded-[10px] bg-blue-100 text-blue-700 font-bold text-sm"
                >
                  {{ (inventoryRecords.current_page - 1) * inventoryRecords.per_page + index + 1 }}
                </span>
              </td>
              
              <!-- Reference Number -->
              <td class="px-4 py-4">
                <div class="font-semibold text-gray-900">{{ record.reference_no }}</div>
                <div class="text-xs text-gray-600" v-if="record.user">
                  By: {{ record.user.name }}
                </div>
              </td>

              <!-- Product -->
              <td class="px-4 py-4">
                <div class="font-medium text-gray-900">{{ record.product?.name }}</div>
                <div class="text-xs text-gray-600" v-if="record.product?.barcode">Barcode: {{ record.product.barcode }}</div>
              </td>

              <!-- Transaction Type -->
              <td class="px-4 py-4">
                <span
                  class="px-3 py-1 rounded-full text-xs font-medium"
                  :class="getTransactionTypeBadgeClass(record.transaction_type)"
                >
                  {{ formatTransactionType(record.transaction_type) }}
                </span>
              </td>

              <!-- Quantity Before -->
              <td class="px-4 py-4 text-right font-medium text-gray-700">
                {{ Number(record.quantity_before).toFixed(2) }} {{ record.product?.purchase_unit?.symbol }}
              </td>

              <!-- Quantity Change -->
              <td class="px-4 py-4 text-right">
                <span
                  :class="Number(record.quantity_change) >= 0 ? 'text-green-600' : 'text-red-600'"
                  class="font-semibold"
                >
                  {{ Number(record.quantity_change) >= 0 ? '+' : '' }}{{ Number(record.quantity_change).toFixed(2) }}
                </span>
              </td>

              <!-- Quantity After -->
              <td class="px-4 py-4 text-right font-medium text-gray-900">
                {{ Number(record.quantity_after).toFixed(2) }} {{ record.product?.purchase_unit?.symbol }}
              </td>

              <!-- Date -->
              <td class="px-4 py-4 text-sm text-gray-700">
                {{ formatDate(record.transaction_date) }}
              </td>

              <!-- Status -->
              <td class="px-4 py-4 text-center">
                <span
                  class="px-3 py-1 rounded-full text-xs font-medium"
                  :class="getStatusBadgeClass(record.status)"
                >
                  {{ record.status }}
                </span>
              </td>

              <!-- Actions -->
              <td class="px-4 py-4 text-center">
                <div class="flex items-center justify-center gap-2">
                  <button
                    @click="viewRecord(record)"
                    class="p-2 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors"
                    title="View Details"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                      />
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                      />
                    </svg>
                  </button>
                  <button
                    v-if="record.status === 'completed'"
                    @click="deleteRecord(record.id)"
                    class="p-2 rounded-lg text-red-600 hover:bg-red-50 transition-colors"
                    title="Delete"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                      />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div class="mt-6 flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ inventoryRecords.from }} to {{ inventoryRecords.to }} of
            {{ inventoryRecords.total }} records
          </div>
          <div class="flex gap-2">
            <button
              v-for="link in inventoryRecords.links"
              :key="link.label"
              @click="link.url ? $inertia.visit(link.url) : null"
              :disabled="!link.url"
              :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition-all',
                link.active
                  ? 'bg-blue-600 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200',
                !link.url ? 'opacity-50 cursor-not-allowed' : '',
              ]"
              v-html="link.label"
            ></button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Adjustment Modal -->
    <CreateAdjustmentModal
      :show="showCreateModal"
      :products="products"
      @close="closeCreateModal"
      @created="handleCreated"
    />

    <!-- View Record Modal -->
    <ViewRecordModal
      :show="showViewModal"
      :record="selectedRecord"
      @close="closeViewModal"
    />
  </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import CreateAdjustmentModal from "./Components/CreateAdjustmentModal.vue";
import ViewRecordModal from "./Components/ViewRecordModal.vue";

export default {
  components: {
    AppLayout,
    CreateAdjustmentModal,
    ViewRecordModal,
  },
  props: {
    inventoryRecords: Object,
    products: Array,
    filters: Object,
  },
  data() {
    return {
      showCreateModal: false,
      showViewModal: false,
      selectedRecord: null,
      filters: this.filters || {},
    };
  },
  methods: {
    openCreateModal() {
      this.showCreateModal = true;
    },
    closeCreateModal() {
      this.showCreateModal = false;
    },
    handleCreated() {
      this.closeCreateModal();
      this.$inertia.reload();
    },
    viewRecord(record) {
      this.selectedRecord = record;
      this.showViewModal = true;
    },
    closeViewModal() {
      this.showViewModal = false;
      this.selectedRecord = null;
    },
    deleteRecord(id) {
      if (confirm("Are you sure you want to delete this inventory record? This will reverse the inventory change.")) {
        this.$inertia.delete(route("store-inventory.destroy", id));
      }
    },
    applyFilters() {
      this.$inertia.get(route("store-inventory.index"), this.filters, {
        preserveState: true,
        preserveScroll: true,
      });
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
      });
    },
    formatTransactionType(type) {
      const types = {
        adjustment: "Adjustment",
        physical_count: "Physical Count",
        damage: "Damage",
        expired: "Expired",
        return: "Return",
        transfer_in: "Transfer In",
        transfer_out: "Transfer Out",
      };
      return types[type] || type;
    },
    getTransactionTypeBadgeClass(type) {
      const classes = {
        adjustment: "bg-blue-100 text-blue-800",
        physical_count: "bg-purple-100 text-purple-800",
        damage: "bg-red-100 text-red-800",
        expired: "bg-orange-100 text-orange-800",
        return: "bg-green-100 text-green-800",
        transfer_in: "bg-emerald-100 text-emerald-800",
        transfer_out: "bg-yellow-100 text-yellow-800",
      };
      return classes[type] || "bg-gray-100 text-gray-800";
    },
    getStatusBadgeClass(status) {
      const classes = {
        pending: "bg-yellow-100 text-yellow-800",
        completed: "bg-green-100 text-green-800",
        cancelled: "bg-red-100 text-red-800",
      };
      return classes[status] || "bg-gray-100 text-gray-800";
    },
  },
};
</script>
