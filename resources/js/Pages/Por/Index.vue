<template>
  <AppLayout title="Purchase Order Requests">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-white">Purchase Order Requests</h1>
        <button
          @click="openCreateModal"
          class="px-6 py-2 text-white bg-blue-600 rounded hover:bg-blue-700"
        >
          Add New POR
        </button>
      </div>

      <!-- Flash Message -->
      <!-- <div
        v-if="$page.props.flash.success"
        class="p-4 mb-6 text-white bg-green-600 rounded-lg"
      >
        {{ $page.props.flash.success }}
      </div> -->

      <div class="overflow-hidden bg-black border-4 border-blue-600 rounded-lg">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-white">
            <thead class="bg-blue-600">
              <tr>
                <th class="px-6 py-3">Order Number</th>
                <th class="px-6 py-3">Date</th>
                <th class="px-6 py-3">User</th> 
                <th class="px-6 py-3 text-center">Status</th>
                <th class="px-6 py-3 text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="por in pors.data"
                :key="por.id"
                class="border-b border-gray-700 hover:bg-gray-900"
              >
                <td class="px-6 py-4">
                  <span class="font-semibold">{{ por.order_number }}</span>
                </td>
                <td class="px-6 py-4">{{ formatDate(por.order_date) }}</td>
                <td class="px-6 py-4">{{ por.user?.name || 'N/A' }}</td>
               
                <td class="px-6 py-4 text-center">
                  <span :class="getStatusClass(por.status)">
                    {{ por.status.toUpperCase() }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <button
                    @click="openViewModal(por)"
                    class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600"
                  >
                    View
                  </button>
                </td>
              </tr>
              <tr v-if="!pors.data || pors.data.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-gray-400">
                  No Purchase Order Requests found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="pors.links && pors.links.length > 3">
          <div class="text-sm text-gray-400">
            Showing {{ pors.from }} to {{ pors.to }} of {{ pors.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              v-for="link in pors.links"
              :key="link.label"
              @click="link.url ? router.visit(link.url) : null"
              :disabled="!link.url"
              :class="[
                'px-3 py-1 rounded',
                link.active
                  ? 'bg-blue-600 text-white'
                  : link.url
                  ? 'bg-gray-700 text-white hover:bg-gray-600'
                  : 'bg-gray-800 text-gray-500 cursor-not-allowed'
              ]"
              v-html="link.label"
            ></button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Modal -->
    <PorCreateModal 
      v-model:open="isCreateModalOpen"
      :products="products"
      :measurement-units="measurementUnits"
      :users="users"
      :orderNumber="orderNumber"
    />

    <!-- View Modal -->
    <PorViewModel
      v-model:open="isViewModalOpen"
      :por="selectedPor"
      v-if="selectedPor"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import PorCreateModal from './Components/PorCreateModal.vue';
import PorViewModel from './Components/PorViewModel.vue';

defineProps({
    pors: Object,
    products: Array,
    measurementUnits: Array,
    users: Array,
    orderNumber: String
});

const isCreateModalOpen = ref(false);
const isViewModalOpen = ref(false);
const selectedPor = ref(null);

const openCreateModal = () => {
    isCreateModalOpen.value = true;
};

const openViewModal = (por) => {
    selectedPor.value = por;
    isViewModalOpen.value = true;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

const formatNumber = (number) => {
    return parseFloat(number).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const getStatusClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-500 text-white px-3 py-1 rounded',
        'approved': 'bg-green-500 text-white px-3 py-1 rounded',
        'rejected': 'bg-red-500 text-white px-3 py-1 rounded',
        'completed': 'bg-blue-500 text-white px-3 py-1 rounded'
    };
    return classes[status] || 'bg-gray-500 text-white px-3 py-1 rounded';
};
</script>

<style scoped>
/* Tailwind CSS handles all styling */
</style>