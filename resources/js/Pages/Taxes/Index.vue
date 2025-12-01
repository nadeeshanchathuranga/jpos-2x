<template>
  <AppLayout>
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-white">Taxes</h1>
        <button
          @click="openCreateModal"
          class="px-6 py-2 text-white bg-accent rounded hover:bg-accent"
        >
          Add Tax
        </button>
      </div>

      <div class="overflow-hidden bg-black border-4 border-blue-600 rounded-lg">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-white">
            <thead class="bg-blue-600">
              <tr>
                <th class="px-6 py-3">ID</th>
                <th class="px-6 py-3">Tax Name</th>
                <th class="px-6 py-3">Percentage</th>
                <th class="px-6 py-3">Type</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(tax, index) in taxes.data"
                :key="tax.id"
                class="border-b border-gray-700 hover:bg-gray-900"
              >
                <td class="px-6 py-4">
                  {{ (taxes.current_page - 1) * taxes.per_page + index + 1 }}
                </td>
                <td class="px-6 py-4">{{ tax.name }}</td>
                <td class="px-6 py-4">{{ tax.percentage }}%</td>
                <td class="px-6 py-4">
                  <span class="px-2 py-1 text-xs rounded" :class="tax.type == 0 ? 'bg-purple-500' : 'bg-orange-500'">
                    {{ tax.type == 0 ? 'Inclusive' : 'Exclusive' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span
                    :class="{
                      'bg-red-500 text-white px-3 py-1 rounded': tax.status == 0,
                      'bg-green-500 text-white px-3 py-1 rounded': tax.status == 1,
                      'bg-blue-500 text-white px-3 py-1 rounded': tax.status == 2
                    }"
                  >
                    {{ tax.status == 1 ? 'Active' : tax.status == 0 ? 'Inactive' : 'Default' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <button
                    @click="openEditModal(tax)"
                    :disabled="tax.status == 2"
                    :class="[
                      'px-4 py-2 mr-2 text-white rounded',
                      tax.status == 2
                        ? 'bg-gray-500 cursor-not-allowed opacity-50'
                        : 'bg-accent hover:bg-accent'
                    ]"
                  >
                    Edit
                  </button>
                  <button
                    @click="openDeleteModal(tax)"
                    :disabled="tax.status == 2 || tax.status == 0"
                    :class="[
                      'px-4 py-2 text-white rounded',
                      tax.status == 2 || tax.status == 0
                        ? 'bg-gray-500 cursor-not-allowed opacity-50'
                        : 'bg-red-500 hover:bg-red-600'
                    ]"
                  >
                    Delete
                  </button>
                </td>
              </tr>
              <tr v-if="!taxes.data || taxes.data.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-gray-400">
                  No taxes found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="taxes.links">
          <div class="text-sm text-gray-400">
            Showing {{ taxes.from }} to {{ taxes.to }} of {{ taxes.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              v-for="link in taxes.links"
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
    <TaxCreateModal v-model:open="isCreateModalOpen" />

    <!-- Edit Modal -->
    <TaxEditModal
      v-model:open="isEditModalOpen"
      :tax="selectedTax"
      v-if="selectedTax"
    />

    <!-- Delete Modal -->
    <TaxDeleteModal
      v-model:open="isDeleteModalOpen"
      :tax="selectedTaxForDelete"
      v-if="selectedTaxForDelete"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import TaxCreateModal from "./Components/TaxCreateModal.vue";
import TaxEditModal from "./Components/TaxEditModal.vue";
import TaxDeleteModal from "./Components/TaxDeleteModal.vue";

defineProps({
  taxes: {
    type: Object,
    required: true,
  },
});

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedTax = ref(null);
const selectedTaxForDelete = ref(null);

const openCreateModal = () => {
  isCreateModalOpen.value = true;
};

const openEditModal = (tax) => {
  selectedTax.value = tax;
  isEditModalOpen.value = true;
};

const openDeleteModal = (tax) => {
  selectedTaxForDelete.value = tax;
  isDeleteModalOpen.value = true;
};
</script>
