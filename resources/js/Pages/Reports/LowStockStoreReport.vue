<template>
  <Head title="Low Stock Store Report" />

  <AuthenticatedLayout>
    <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-6">
      <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div>
            <div class="flex items-center gap-4 mb-2">
              <button
                @click="$inertia.visit(route('dashboard'))"
                class="px-4 py-2 bg-accent hover:bg-accent text-white rounded-lg transition flex items-center gap-2"
              >
                Back
              </button>
              <h1 class="text-3xl font-bold text-white">🏬 Store Low Stock Report</h1>
            </div>
            <p class="text-gray-400">Products in store that are at or below configured low-stock margins.</p>
          </div>

          <div class="flex items-center gap-2 bg-gray-800 rounded-lg p-3 shadow-lg">
            <input
              type="date"
              v-model="startDate"
              class="px-3 py-1.5 bg-gray-700 text-white text-sm rounded focus:ring-2 focus:ring-blue-500"
            />
            <span class="text-gray-400">to</span>
            <input
              type="date"
              v-model="endDate"
              class="px-3 py-1.5 bg-gray-700 text-white text-sm rounded focus:ring-2 focus:ring-blue-500"
            />

            <button @click="filterReports" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded transition">Apply</button>
            <button @click="resetFilter" class="px-4 py-1.5 bg-gray-600 hover:bg-gray-700 text-white text-sm font-semibold rounded transition">Reset</button>
          </div>
        </div>

        <!-- Summary Card -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <div class="bg-gradient-to-br from-green-600 to-green-700 rounded-lg p-6 shadow-lg">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-green-100 text-sm mb-1">Total Low Stock Items</p>
                <h2 class="text-3xl font-bold text-white">{{ products.length }}</h2>
              </div>
              <div class="text-4xl">🏬</div>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="bg-gray-800 rounded-lg p-6 shadow-lg mb-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-white">Store Low Stock Details</h3>
            <div class="flex gap-2">
              <button
                @click="exportPdf"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition flex items-center gap-2"
              >
                📄 Export PDF
              </button>
              <button
                @click="exportCsv"
                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition flex items-center gap-2"
              >
                📊 Export CSV
              </button>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-700">
                <tr>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">#</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">Item Name</th>
                  <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Store Qty</th>
                  <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Low Stock Margin</th>
                  <th class="px-4 py-3 text-center text-sm font-semibold text-gray-300">Sales Unit</th>
                  <th class="px-4 py-3 text-center text-sm font-semibold text-gray-300">Symbol</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-700 text-gray-300">
                <tr v-for="(p, idx) in products" :key="p.id">
                  <td class="px-4 py-3">{{ idx + 1 }}</td>
                  <td class="px-4 py-3">{{ p.name }}</td>
                  <td class="px-4 py-3 text-right font-semibold">{{ p.store_quantity }}</td>
                  <td class="px-4 py-3 text-right">{{ p.store_low_stock_margin }}</td>
                  <td class="px-4 py-3 text-center">{{ p.sales_unit }}</td>
                  <td class="px-4 py-3 text-center">{{ p.symbol }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="products.length === 0" class="text-center text-gray-400 py-8">No low stock products found in store.</div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { logActivity } from '@/composables/useActivityLog';

const props = defineProps({
  products: { type: Array, default: () => [] },
  startDate: String,
  endDate: String,
});

const startDate = ref(props.startDate || '');
const endDate = ref(props.endDate || '');

const exportPdfUrl = computed(() => route('reports.export.low-stock-store.pdf', { start_date: startDate.value, end_date: endDate.value }));
const exportCsvUrl = computed(() => route('reports.export.low-stock-store.csv', { start_date: startDate.value, end_date: endDate.value }));

const exportPdf = async () => {
  await logActivity('create', 'low_stock_store_report', { action: 'export_pdf', total: props.products.length, start_date: startDate.value, end_date: endDate.value });
  window.location.href = exportPdfUrl.value;
};

const exportCsv = async () => {
  await logActivity('create', 'low_stock_store_report', { action: 'export_csv', total: props.products.length, start_date: startDate.value, end_date: endDate.value });
  window.location.href = exportCsvUrl.value;
};

const filterReports = () => {
  const params = {};
  if (startDate.value) params.start_date = startDate.value;
  if (endDate.value) params.end_date = endDate.value;
  router.get(route('reports.low-stock-store'), params, { preserveState: true, preserveScroll: true });
};

const resetFilter = () => {
  startDate.value = '';
  endDate.value = '';
  router.get(route('reports.low-stock-store'), {}, { preserveState: false, preserveScroll: false });
};
</script>
