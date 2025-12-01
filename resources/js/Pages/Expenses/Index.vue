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
                <th class="px-6 py-3">Title</th>
                <th class="px-6 py-3">Amount</th>
                <th class="px-6 py-3">Payment Type</th>
                <th class="px-6 py-3">Added By</th>
                <th class="px-6 py-3">Remark</th>
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
                <td class="px-6 py-4">{{ expense.title }}</td>
                <td class="px-6 py-4">Rs. {{ formatAmount(expense.amount) }}</td>
                <td class="px-6 py-4">
                  <span
                    :class="{
                      'bg-green-500 text-white px-3 py-1 rounded': expense.payment_type == 0,
                      'bg-blue-500 text-white px-3 py-1 rounded': expense.payment_type == 1,
                      'bg-yellow-500 text-white px-3 py-1 rounded': expense.payment_type == 2
                    }"
                  >
                    {{ getPaymentTypeName(expense.payment_type) }}
                  </span>
                </td>
                <td class="px-6 py-4">{{ expense.user?.name || '-' }}</td>
                <td class="px-6 py-4">{{ expense.remark || '-' }}</td>
                <td class="px-6 py-4">
                  <button
                    @click="openEditModal(expense)"
                    class="px-4 py-2 mr-2 text-white bg-accent rounded hover:bg-accent"
                  >
                    Edit
                  </button>
                  <button
                    @click="openDeleteModal(expense)"
                    class="px-4 py-2 text-white bg-red-500 rounded hover:bg-red-600"
                  >
                    Delete
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
    <ExpenseCreateModal
      :show="showCreateModal"
      :suppliers="suppliers"
      @close="closeCreateModal"
    />

    <!-- Edit Modal -->
    <ExpenseEditModal
      :show="showEditModal"
      :expense="selectedExpense"
      @close="closeEditModal"
    />

    <!-- Delete Modal -->
    <ExpenseDeleteModal
      :show="showDeleteModal"
      :expense="selectedExpense"
      @close="closeDeleteModal"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import ExpenseCreateModal from './Components/ExpenseCreateModal.vue';
import ExpenseEditModal from './Components/ExpenseEditModal.vue';
import ExpenseDeleteModal from './Components/ExpenseDeleteModal.vue';

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

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedExpense = ref(null);

const openCreateModal = () => {
  showCreateModal.value = true;
};

const closeCreateModal = () => {
  showCreateModal.value = false;
};

const openEditModal = (expense) => {
  selectedExpense.value = expense;
  showEditModal.value = true;
};

const closeEditModal = () => {
  showEditModal.value = false;
  selectedExpense.value = null;
};

const openDeleteModal = (expense) => {
  selectedExpense.value = expense;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  showDeleteModal.value = false;
  selectedExpense.value = null;
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatAmount = (amount) => {
  if (!amount) return '0.00';
  return parseFloat(amount).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
};

const getPaymentTypeName = (type) => {
  const types = {
    0: 'Cash',
    1: 'Card',
    2: 'Credit'
  };
  return types[type] || 'Unknown';
};
</script>
