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
          <h1 class="text-3xl font-bold text-white">Measurement Units</h1>
        </div>
        <button
          @click="openCreateModal"
          class="px-6 py-2 text-white bg-accent rounded hover:bg-accent"
        >
          Add Measurement Unit
        </button>
      </div>

      <div class="overflow-hidden bg-black border-4 border-blue-600 rounded-lg">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-white">
            <thead class="bg-blue-600">
              <tr>
                <th class="px-6 py-3">ID</th>
                <th class="px-6 py-3">Unit Name</th>
                <th class="px-6 py-3">Symbol</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(unit, index) in measurementUnits.data"
                :key="unit.id"
                class="border-b border-gray-700 hover:bg-gray-900"
              >
                <td class="px-6 py-4">
                  {{ (measurementUnits.current_page - 1) * measurementUnits.per_page + index + 1 }}
                </td>
                <td class="px-6 py-4">{{ unit.name }}</td>
                <td class="px-6 py-4">{{ unit.symbol }}</td>
                <td class="px-6 py-4">
                  <span
                    :class="{
                      'bg-red-500 text-white px-3 py-1 rounded': unit.status == 0,
                      'bg-green-500 text-white px-3 py-1 rounded': unit.status == 1,
                      'bg-blue-500 text-white px-3 py-1 rounded': unit.status == 2
                    }"
                  >
                    {{ unit.status == 1 ? 'Active' : unit.status == 0 ? 'Inactive' : 'Default' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <button
                    @click="openEditModal(unit)"
                    :disabled="unit.status == 2"
                    :class="[
                      'px-4 py-2 mr-2 text-white rounded',
                      unit.status == 2
                        ? 'bg-gray-500 cursor-not-allowed opacity-50'
                        : 'bg-accent hover:bg-accent'
                    ]"
                  >
                    Edit
                  </button>
                  <button
                    @click="openDeleteModal(unit)"
                    :disabled="unit.status == 2 || unit.status == 0"
                    :class="[
                      'px-4 py-2 text-white rounded',
                      unit.status == 2 || unit.status == 0
                        ? 'bg-gray-500 cursor-not-allowed opacity-50'
                        : 'bg-red-500 hover:bg-red-600'
                    ]"
                  >
                    Delete
                  </button>
                </td>
              </tr>
              <tr v-if="!measurementUnits.data || measurementUnits.data.length === 0">
                <td colspan="5" class="px-6 py-4 text-center text-gray-400">
                  No measurement units found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="measurementUnits.links">
          <div class="text-sm text-gray-400">
            Showing {{ measurementUnits.from }} to {{ measurementUnits.to }} of {{ measurementUnits.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              v-for="link in measurementUnits.links"
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
    <MeasurementUnitCreateModal v-model:open="isCreateModalOpen" />

    <!-- Edit Modal -->
    <MeasurementUnitEditModal
      v-model:open="isEditModalOpen"
      :unit="selectedUnit"
      v-if="selectedUnit"
    />

    <!-- Delete Modal -->
    <MeasurementUnitDeleteModal
      v-model:open="isDeleteModalOpen"
      :unit="selectedUnitForDelete"
      v-if="selectedUnitForDelete"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import MeasurementUnitCreateModal from "./Components/MeasurementUnitCreateModal.vue";
import MeasurementUnitEditModal from "./Components/MeasurementUnitEditModal.vue";
import MeasurementUnitDeleteModal from "./Components/MeasurementUnitDeleteModal.vue";

defineProps({
  measurementUnits: {
    type: Object,
    required: true,
  },
});

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedUnit = ref(null);
const selectedUnitForDelete = ref(null);

const openCreateModal = () => {
  isCreateModalOpen.value = true;
};

const openEditModal = (unit) => {
  selectedUnit.value = unit;
  isEditModalOpen.value = true;
};

const openDeleteModal = (unit) => {
  selectedUnitForDelete.value = unit;
  isDeleteModalOpen.value = true;
};
</script>
