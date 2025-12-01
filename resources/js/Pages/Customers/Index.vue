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
          <h1 class="text-3xl font-bold text-white">Customers</h1>
        </div>
        <button
          @click="openCreateModal"
          class="px-6 py-2 text-white bg-accent rounded hover:bg-accent"
        >
          Add Customer
        </button>
      </div>

      <div class="overflow-hidden bg-black border-4 border-blue-600 rounded-lg">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-white">
            <thead class="bg-blue-600">
              <tr>
                <th class="px-6 py-3">ID</th>
                <th class="px-6 py-3">Customer Name</th>
                <th class="px-6 py-3">Email</th>
                <th class="px-6 py-3">Contact</th>
                <th class="px-6 py-3">Credit Limit</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(customer, index) in customers.data"
                :key="customer.id"
                class="border-b border-gray-700 hover:bg-gray-900"
              >
                <td class="px-6 py-4">
                  {{ (customers.current_page - 1) * customers.per_page + index + 1 }}
                </td>
                <td class="px-6 py-4">{{ customer.name }}</td>
                <td class="px-6 py-4">{{ customer.email || '-' }}</td>
                <td class="px-6 py-4">{{ customer.contact || '-' }}</td>
                <td class="px-6 py-4">{{ customer.credit_limit || '0.00' }}</td>
                <td class="px-6 py-4">
                  <span
                    :class="{
                      'bg-red-500 text-white px-3 py-1 rounded': customer.status == 0,
                      'bg-green-500 text-white px-3 py-1 rounded': customer.status == 1,
                      'bg-blue-500 text-white px-3 py-1 rounded': customer.status == 2
                    }"
                  >
                    {{ customer.status == 1 ? 'Active' : customer.status == 0 ? 'Inactive' : 'Default' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <button
                    @click="openEditModal(customer)"
                    :disabled="customer.status == 2"
                    :class="[
                      'px-4 py-2 mr-2 text-white rounded',
                      customer.status == 2
                        ? 'bg-gray-500 cursor-not-allowed opacity-50'
                        : 'bg-accent hover:bg-accent'
                    ]"
                  >
                    Edit
                  </button>
                  <button
                    @click="openDeleteModal(customer)"
                    :disabled="customer.status == 2 || customer.status == 0"
                    :class="[
                      'px-4 py-2 text-white rounded',
                      customer.status == 2 || customer.status == 0
                        ? 'bg-gray-500 cursor-not-allowed opacity-50'
                        : 'bg-red-500 hover:bg-red-600'
                    ]"
                  >
                    Delete
                  </button>
                </td>
              </tr>
              <tr v-if="!customers.data || customers.data.length === 0">
                <td colspan="7" class="px-6 py-4 text-center text-gray-400">
                  No customers found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="customers.links">
          <div class="text-sm text-gray-400">
            Showing {{ customers.from }} to {{ customers.to }} of {{ customers.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              v-for="link in customers.links"
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
    <CustomerCreateModal v-model:open="isCreateModalOpen" />

    <!-- Edit Modal -->
    <CustomerEditModal
      v-model:open="isEditModalOpen"
      :customer="selectedCustomer"
      v-if="selectedCustomer"
    />

    <!-- Delete Modal -->
    <CustomerDeleteModal
      v-model:open="isDeleteModalOpen"
      :customer="selectedCustomerForDelete"
      v-if="selectedCustomerForDelete"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import CustomerCreateModal from "./Components/CustomerCreateModal.vue";
import CustomerEditModal from "./Components/CustomerEditModal.vue";
import CustomerDeleteModal from "./Components/CustomerDeleteModal.vue";

defineProps({
  customers: {
    type: Object,
    required: true,
  },
});

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedCustomer = ref(null);
const selectedCustomerForDelete = ref(null);

const openCreateModal = () => {
  isCreateModalOpen.value = true;
};

const openEditModal = (customer) => {
  selectedCustomer.value = customer;
  isEditModalOpen.value = true;
};

const openDeleteModal = (customer) => {
  selectedCustomerForDelete.value = customer;
  isDeleteModalOpen.value = true;
};
</script>
