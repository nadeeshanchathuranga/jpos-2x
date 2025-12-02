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
          <h1 class="text-3xl font-bold text-white">Suppliers</h1>
        </div>
        <button
          @click="openCreateModal"
          class="px-6 py-2 text-white bg-accent rounded hover:bg-accent"
        >
          Add Supplier
        </button>
      </div>

      <div class="overflow-hidden bg-dark border-4 border-accent rounded-lg">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-white">
            <thead class="bg-accent">
              <tr>
                <th class="px-6 py-3">ID</th>
                <th class="px-6 py-3">Name</th>
                <th class="px-6 py-3">Email</th>
                <th class="px-6 py-3">Phone</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(supplier, index) in suppliers.data"
                :key="supplier.id"
                class="border-b border-gray-700 hover:bg-gray-900"
              >
                <td class="px-6 py-4">
                  {{ (suppliers.current_page - 1) * suppliers.per_page + index + 1 }}
                </td>
                <td class="px-6 py-4">{{ supplier.name }}</td>
                <td class="px-6 py-4">{{ supplier.email || '-' }}</td>
                <td class="px-6 py-4">{{ supplier.phone || '-' }}</td>
                <td class="px-6 py-4">
                  <span
                    :class="{
                      'bg-red-500 text-white px-3 py-1 rounded': supplier.status == 0,
                      'bg-green-500 text-white px-3 py-1 rounded': supplier.status == 1,
                      'bg-blue-500 text-white px-3 py-1 rounded': supplier.status == 2
                    }"
                  >
                    {{ supplier.status == 1 ? 'Active' : supplier.status == 0 ? 'Inactive' : 'Default' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <button
                    @click="openEditModal(supplier)"
                    :disabled="supplier.status == 2"
                    :class="[
                      'px-4 py-2 mr-2 text-white rounded',
                      supplier.status == 2
                        ? 'bg-gray-500 cursor-not-allowed opacity-50'
                        : 'bg-accent hover:bg-accent'
                    ]"
                  >
                    Edit
                  </button>
                  <button
                    @click="openDeleteModal(supplier)"
                    :disabled="supplier.status == 2 || supplier.status == 0"
                    :class="[
                      'px-4 py-2 text-white rounded',
                      supplier.status == 2 || supplier.status == 0
                        ? 'bg-gray-500 cursor-not-allowed opacity-50'
                        : 'bg-red-500 hover:bg-red-600'
                    ]"
                  >
                    Delete
                  </button>
                </td>
              </tr>
              <tr v-if="!suppliers.data || suppliers.data.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-gray-400">
                  No suppliers found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="suppliers.links">
          <div class="text-sm text-gray-400">
            Showing {{ suppliers.from }} to {{ suppliers.to }} of {{ suppliers.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              v-for="link in suppliers.links"
              :key="link.label"
              @click="link.url ? router.visit(link.url) : null"
              :disabled="!link.url"
              :class="[
                'px-3 py-1 rounded',
                link.active
                  ? 'bg-accent text-white'
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
    <SupplierCreateModal v-model:open="isCreateModalOpen" />

    <!-- Edit Modal -->
    <SupplierEditModal
      v-model:open="isEditModalOpen"
      :supplier="selectedSupplier"
      v-if="selectedSupplier"
    />

    <!-- Delete Modal -->
    <SupplierDeleteModal
      v-model:open="isDeleteModalOpen"
      :supplier="selectedSupplierForDelete"
      v-if="selectedSupplierForDelete"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import SupplierCreateModal from "./Components/SupplierCreateModal.vue";
import SupplierEditModal from "./Components/SupplierEditModal.vue";
import SupplierDeleteModal from "./Components/SupplierDeleteModal.vue";

defineProps({
  suppliers: {
    type: Object,
    required: true,
  },
});

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedSupplier = ref(null);
const selectedSupplierForDelete = ref(null);

const openCreateModal = () => {
  isCreateModalOpen.value = true;
};

const openEditModal = (supplier) => {
  selectedSupplier.value = supplier;
  isEditModalOpen.value = true;
};

const openDeleteModal = (supplier) => {
  selectedSupplierForDelete.value = supplier;
  isDeleteModalOpen.value = true;
};
</script>
