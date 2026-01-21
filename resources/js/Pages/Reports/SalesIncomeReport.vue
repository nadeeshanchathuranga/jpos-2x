<template>
  <Head title="Sales Income Report" />

  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 p-6">
      <div>
        <!-- Header with Date Filter -->
        <div
          class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
        >
          <div>
            <div class="flex items-center gap-4">
              <!-- Back to Dashboard Button -->
              <button
                @click="goToReportsTab"
                class="px-6 py-2.5 rounded-[5px] font-medium text-sm bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 hover:border-gray-300 transition-all duration-200"
              >
                ← Back
              </button>
              <h1 class="text-3xl font-bold text-gray-800">Sales Income Report</h1>
            </div>
          </div>

          <!-- Compact Date Filter -->
          <div
            class="flex items-center gap-2 bg-white rounded-[5px] p-3 shadow-sm border border-gray-200"
          >
            <input
              type="date"
              v-model="startDate"
              class="px-3 py-1.5 bg-white text-gray-700 text-sm border border-gray-300 rounded-[5px] focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
            <span class="text-gray-500">to</span>
            <input
              type="date"
              v-model="endDate"
              class="px-3 py-1.5 bg-white text-gray-700 text-sm border border-gray-300 rounded-[5px] focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
            <button
              @click="filterReports"
              class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-[5px] transition-all duration-200 hover:scale-105 shadow-sm"
            >
              Apply
            </button>
            <button
              @click="resetFilter"
              class="px-4 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-semibold rounded-[5px] transition-all duration-200 hover:scale-105"
            >
              Reset
            </button>
          </div>
        </div>

        <div class="flex gap-6">
          <!-- Sales Income Transactions Table -->
          <div
            class="flex-1 bg-white rounded-2xl p-6 shadow-sm border border-gray-200 mb-6"
          >
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-xl font-semibold text-gray-800">
                Sales Income Transactions
              </h3>
              <div class="flex gap-2">
                <button
                  @click="exportPdf"
                  class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-[5px] transition-all duration-200 hover:scale-105 flex items-center gap-2 shadow-sm"
                >
                  📄 Export PDF
                </button>
                <button
                  @click="exportExcel"
                  class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-[5px] transition-all duration-200 hover:scale-105 flex items-center gap-2 shadow-sm"
                >
                    Export Excel
                </button>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="border-b-2 border-blue-600">
                    <th class="px-4 py-3 text-left text-sm font-semibold text-blue-600">
                      Invoice No
                    </th>
                    <th class="px-4 py-3 text-center text-sm font-semibold text-blue-600">
                      Income Date
                    </th>
                    <th class="px-4 py-3 text-right text-sm font-semibold text-blue-600">
                      Amount
                    </th>
                    <th class="px-4 py-3 text-center text-sm font-semibold text-blue-600">
                      Type
                    </th>
                    <th class="px-4 py-3 text-center text-sm font-semibold text-blue-600">
                      Payment Type
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <!-- Cash Payments -->
                  <tr>
                    <td colspan="5" class="bg-green-50 text-green-700 font-bold px-4 py-2">Cash Payments</td>
                  </tr>
                  <tr
                    v-for="income in salesIncomeList.data.filter(i => i.payment_type === 0)"
                    :key="'cash-' + income.id"
                    class="text-gray-700 hover:bg-blue-50/50 transition-colors duration-200"
                  >
                    <td class="px-4 py-3 font-medium">
                      {{ income.invoice_no || "N/A" }}
                    </td>
                    <td class="px-4 py-3 text-center">
                      {{ formatDate(income.income_date) }}
                    </td>
                    <td
                      class="px-4 py-3 text-right font-semibold"
                      :class="income.is_return ? 'text-red-600' : 'text-green-600'"
                    >
                      {{ page.props.currency || "" }} {{ income.amount }}
                    </td>
                    <td class="px-4 py-3 text-center">
                      <span
                        class="px-3 py-1 rounded-[5px] text-white text-sm font-medium"
                        :class="income.is_return ? 'bg-red-600' : 'bg-green-600'"
                      >
                        {{ income.type }}
                      </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                      <span
                        class="px-3 py-1 rounded-[5px] text-white text-sm font-medium"
                        :class="getPaymentTypeColor(income.payment_type)"
                      >
                        {{ income.payment_type_name }}
                      </span>
                    </td>
                  </tr>
                  <!-- Card Payments -->
                  <tr>
                    <td colspan="5" class="bg-blue-50 text-blue-700 font-bold px-4 py-2">Card Payments</td>
                  </tr>
                  <tr
                    v-for="income in salesIncomeList.data.filter(i => i.payment_type === 1)"
                    :key="'card-' + income.id"
                    class="text-gray-700 hover:bg-blue-50/50 transition-colors duration-200"
                  >
                    <td class="px-4 py-3 font-medium">
                      {{ income.invoice_no || "N/A" }}
                    </td>
                    <td class="px-4 py-3 text-center">
                      {{ formatDate(income.income_date) }}
                    </td>
                    <td
                      class="px-4 py-3 text-right font-semibold"
                      :class="income.is_return ? 'text-red-600' : 'text-green-600'"
                    >
                      {{ page.props.currency || "" }} {{ income.amount }}
                    </td>
                    <td class="px-4 py-3 text-center">
                      <span
                        class="px-3 py-1 rounded-[5px] text-white text-sm font-medium"
                        :class="income.is_return ? 'bg-red-600' : 'bg-green-600'"
                      >
                        {{ income.type }}
                      </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                      <span
                        class="px-3 py-1 rounded-[5px] text-white text-sm font-medium"
                        :class="getPaymentTypeColor(income.payment_type)"
                      >
                        {{ income.payment_type_name }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div
              v-if="!salesIncomeList.data || salesIncomeList.data.length === 0"
              class="text-center text-gray-500 py-8"
            >
              No sales income data for selected date range
            </div>

            <!-- Pagination -->
            <div
              v-if="salesIncomeList.data?.length > 0"
              class="mt-6 flex justify-between items-center"
            >
              <div class="text-sm text-gray-600">
                Showing {{ salesIncomeList.from }} to {{ salesIncomeList.to }} of
                {{ salesIncomeList.total }} transactions
              </div>
              <div class="flex gap-2">
                <template v-for="(link, index) in salesIncomeList.links" :key="index">
                  <a
                    v-if="link.url"
                    :href="link.url"
                    @click.prevent="
                      router.visit(link.url, {
                        preserveState: true,
                        preserveScroll: true,
                      })
                    "
                    :class="[
                      'px-3 py-2 text-sm rounded-[5px] transition-all duration-200 border',
                      link.active
                        ? 'bg-blue-600 text-white font-semibold border-blue-600 hover:bg-blue-700'
                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:scale-105',
                    ]"
                    v-html="link.label"
                  ></a>
                  <span
                    v-else
                    :class="[
                      'px-3 py-2 text-sm rounded-[5px] border',
                      'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed',
                    ]"
                    v-html="link.label"
                  ></span>
                </template>
              </div>
            </div>
          </div>

          <!-- Summary in Corner -->
          <div
            v-if="salesIncomeList.data?.length > 0"
            class="w-80 bg-white rounded-2xl p-6 shadow-sm border border-gray-200 mb-6 sticky top-6 self-start"
          >
            <h3
              class="text-lg font-semibold text-gray-800 mb-4 border-b-2 border-blue-600 pb-2"
            >
              Summary
            </h3>
            <div class="space-y-4">
              <div
                class="flex justify-between items-center pb-3 border-b border-gray-200"
              >
                <span class="text-sm font-medium text-gray-600">Total Income:</span>
                <span class="text-xl font-bold text-green-600"
                  >{{ page.props.currency || "" }} {{ totalIncome }}</span
                >
              </div>
              <div
                class="flex justify-between items-center pb-3 border-b border-gray-200"
              >
                <span class="text-sm font-medium text-gray-600">Total Returns:</span>
                <span class="text-xl font-bold text-red-600"
                  >{{ page.props.currency || "" }} {{ totalReturns }}</span
                >
              </div>
              <div class="flex justify-between items-center pt-2">
                <span class="text-base font-bold text-gray-800">Net Income:</span>
                <span class="text-2xl font-bold text-blue-600"
                  >{{ page.props.currency || "" }} {{ netIncome }}</span
                >
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { logActivity } from "@/composables/useActivityLog";
import { useDashboardNavigation } from "@/composables/useDashboardNavigation";

const { goToReportsTab } = useDashboardNavigation();

const page = usePage();

const props = defineProps({
  salesIncomeList: Object,
  totalIncome: String,
  totalReturns: String,
  netIncome: String,
  startDate: String,
  endDate: String,
  totalCash: {
    type: String,
    default: '0.00',
  },
  totalCard: {
    type: String,
    default: '0.00',
  },
});


const startDate = ref(props.startDate);
const endDate = ref(props.endDate);

const filterReports = () => {
  router.get(
    route("reports.sales-income"),
    {
      start_date: startDate.value,
      end_date: endDate.value,
    },
    {
      preserveState: true,
      preserveScroll: true,
    }
  );
};

const resetFilter = () => {
  router.get(
    route("reports.sales-income"),
    {},
    {
      preserveState: false,
      preserveScroll: false,
    }
  );
};



const exportPdf = async () => {
  await logActivity("create", "sales_income_report", {
    action: "export_pdf",
    start_date: startDate.value,
    end_date: endDate.value,
  });
  window.location.href = route("reports.export.sales-income.pdf", {
    start_date: startDate.value,
    end_date: endDate.value,
    currency: page.props.currency || "",
  });
};

const exportExcel = async () => {
  await logActivity("create", "sales_income_report", {
    action: "export_excel",
    start_date: startDate.value,
    end_date: endDate.value,
  });
  window.location.href = route("reports.export.sales-income.excel", {
    start_date: startDate.value,
    end_date: endDate.value,
    currency: page.props.currency || "",
  });
};

// Add missing helpers for template rendering
const getPaymentTypeColor = (type) => {
  const colors = {
    0: "bg-green-600",
    1: "bg-blue-600",
    2: "bg-orange-600",
  };
  return colors[type] || "bg-gray-600";
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};
</script>
