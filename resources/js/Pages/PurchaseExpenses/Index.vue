
<template>
  <AppLayout>
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
          <button
            @click="$inertia.visit(route('dashboard'))"
            class="px-4 py-2 text-white bg-accent rounded hover:bg-accent"
          >
            Back
          </button>
          <h1 class="text-3xl font-bold text-white">Expenses</h1>
        </div>
        <button
          @click="openCreateModal"
          class="px-6 py-2 text-white bg-accent rounded hover:bg-accent"
        >
          Add Expense
        </button>
      </div>

      <div class="overflow-hidden bg-dark border-4 border-accent rounded-lg">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-white">
            <thead class="bg-accent">
              <tr>
                <th class="px-6 py-3">ID</th>
                <th class="px-6 py-3">Date</th>
                <th class="px-6 py-3">Supplier</th>
                <th class="px-6 py-3">Amount</th>
                <th class="px-6 py-3">Payment Type</th>
                <th class="px-6 py-3">Reference</th>
                <th class="px-6 py-3">Added By</th>
                <th class="px-6 py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(expense, index) in expenses.data"
                :key="expense.id"
                class="border-b border-gray-700 hover:bg-gray-900"
              >
                <td class="px-6 py-4">
                  {{ (expenses.current_page - 1) * expenses.per_page + index + 1 }}
                </td>
                <td class="px-6 py-4">{{ formatDate(expense.expense_date) }}</td>
                <td class="px-6 py-4">{{ expense.supplier ? `${expense.supplier.id} - ${expense.supplier.name}` : '-' }}</td>
                <td class="px-6 py-4">Rs. {{ formatAmount(expense.amount) }}</td>
                <td class="px-6 py-4">
                  <span
                    :class="{
                      'bg-green-500 text-white px-3 py-1 rounded': expense.payment_type == 0,
                      'bg-blue-500 text-white px-3 py-1 rounded': expense.payment_type == 1,
                      'bg-yellow-500 text-white px-3 py-1 rounded': expense.payment_type == 2,
                      'bg-purple-500 text-white px-3 py-1 rounded': expense.payment_type == 3
                    }"
                  >
                    {{ getPaymentTypeName(expense.payment_type) }}
                  </span>
                </td>
                <td class="px-6 py-4">{{ expense.reference || '-' }}</td>
                <td class="px-6 py-4">{{ expense.user?.name || '-' }}</td>
                <td class="px-6 py-4">
                  <button
                    @click="openEditModal(expense)"
                    class="px-4 py-2 text-white bg-accent rounded hover:bg-accent"
                  >
                    Edit
                  </button>
                </td>
              </tr>
              <tr v-if="!expenses.data || expenses.data.length === 0">
                <td colspan="8" class="px-6 py-4 text-center text-gray-400">
                  No expenses found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="expenses.links">
          <div class="text-sm text-gray-400">
            Showing {{ expenses.from }} to {{ expenses.to }} of {{ expenses.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              v-for="link in expenses.links"
              :key="link.label"
              @click="link.url ? $inertia.visit(link.url) : null"
              :disabled="!link.url"
              :class="[
                'px-4 py-2 text-sm font-medium rounded',
                link.active
                  ? 'bg-accent text-white'
                  : 'bg-gray-700 text-gray-300 hover:bg-gray-600',
                !link.url ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
              ]"
              v-html="link.label"
            ></button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Modal -->
    <PurchaseExpenseCreateModal
      :show="showCreateModal"
      :suppliers="suppliers"
      :supplierData="supplierData"
      @close="closeCreateModal"
      @supplier-change="handleSupplierChange"
    />

    <!-- Edit Modal -->
    <PurchaseExpenseEditModal
      :show="showEditModal"
      :expense="selectedExpense"
      @close="closeEditModal"
    />
  </AppLayout>
</template>

<script setup>
/**
 * Expenses Index Component Script
 * 
 * Manages expense records with CRUD operations and supplier financial tracking
 * Includes modal-based create/edit/delete operations
 */

import { ref } from 'vue';
import axios from 'axios';
import PurchaseExpenseCreateModal from './Components/PurchaseExpenseCreateModal.vue';
import PurchaseExpenseEditModal from './Components/PurchaseExpenseEditModal.vue';

/**
 * Component Props
 * @property {Object} expenses - Paginated expense records from backend
 * @property {Array} suppliers - List of suppliers for dropdown selection
 */
const props = defineProps({
  expenses: {
    type: Object,
    required: true,
  },
  suppliers: {
    type: Array,
    default: () => [],
  },
});

/**
 * Reactive State Variables
 * 
 * Modal visibility states for Create/Edit/Delete operations
 * selectedExpense: Currently selected expense for edit/delete
 * supplierData: Financial summary for selected supplier (total, paid, balance)
 */
const showCreateModal = ref(false);
const showEditModal = ref(false);
const selectedExpense = ref(null);
const supplierData = ref({
  total_amount: 0,
  paid: 0,
  balance: 0,
});

/**
 * Open Create Expense Modal
 */
const openCreateModal = () => {
  showCreateModal.value = true;
};

/**
 * Close Create Modal and Reset Supplier Data
 * Ensures supplier financial data is cleared when modal closes
 */
const closeCreateModal = () => {
  showCreateModal.value = false;
  // Reset supplier data when closing
  supplierData.value = {
    total_amount: 0,
    paid: 0,
    balance: 0,
  };
};

/**
 * Handle Supplier Selection Change
 * Fetches supplier financial summary (total, paid, balance) via AJAX
 * 
 * @param {number} supplierId - Selected supplier ID
 */
const handleSupplierChange = async (supplierId) => {
  try {
    const response = await axios.get(route('purchase-expenses.supplier-data'), {
      params: { supplier_id: supplierId }
    });
    
    supplierData.value = response.data;
  } catch (error) {
    console.error('Error fetching supplier data:', error);
  }
};

/**
 * Open Edit Modal with Selected Expense
 * 
 * @param {Object} expense - Expense record to edit
 */
const openEditModal = (expense) => {
  selectedExpense.value = expense;
  showEditModal.value = true;
};

/**
 * Close Edit Modal and Clear Selection
 */
const closeEditModal = () => {
  showEditModal.value = false;
  selectedExpense.value = null;
};

/**
 * Format Date for Display
 * Converts date string to readable format (e.g., "Jan 15, 2025")
 * 
 * @param {string} date - Date string from database
 * @returns {string} Formatted date or '-' if no date
 */
const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

/**
 * Format Currency Amount
 * Adds thousand separators and 2 decimal places
 * 
 * @param {number} amount - Raw amount from database
 * @returns {string} Formatted amount (e.g., "1,234.56")
 */
const formatAmount = (amount) => {
  if (!amount) return '0.00';
  return parseFloat(amount).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
};

/**
 * Get Payment Type Display Name
 * Maps numeric payment type to readable name
 * 
 * @param {number} type - Payment type ID (0=Cash, 1=Card, 2=Credit, 3=Cheque)
 * @returns {string} Payment type name
 */
const getPaymentTypeName = (type) => {
  const types = {
    0: 'Cash',
    1: 'Card',
    2: 'Credit',
    3: 'Cheque'
  };
  return types[type] || 'Unknown';
};
</script>
