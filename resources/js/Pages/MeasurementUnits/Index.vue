<template>
  <AppLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
          <button
            @click="$inertia.visit(route('dashboard'))"
            class="px-4 py-2 text-white bg-accent rounded"
          >
            Back
          </button>
          <h1 class="text-3xl font-bold text-white">Measurement Units</h1>
        </div>

        <button
          @click="openCreateModal"
          class="px-6 py-2 text-white bg-accent rounded"
        >
          Add Measurement Unit
        </button>
      </div>

      <!-- Table -->
      <div class="overflow-hidden bg-dark border-4 border-accent rounded-lg">
        <table class="w-full text-left text-white">
          <thead class="bg-accent">
            <tr>
              <th class="px-6 py-3">#</th>
              <th class="px-6 py-3">Unit Name</th>
              <th class="px-6 py-3">Symbol</th>
              <th class="px-6 py-3">Status</th>
              <th class="px-6 py-3">Action</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="(unit, index) in normalizedUnits"
              :key="unit.id"
              class="border-b border-gray-700 hover:bg-gray-900"
            >
              <td class="px-6 py-4">
                {{ (measurementUnits.current_page - 1) * measurementUnits.per_page + index + 1 }}
              </td>

              <td class="px-6 py-4">{{ unit.name }}</td>
              <td class="px-6 py-4">{{ unit.symbol }}</td>

              <!-- STATUS -->
              <td class="px-6 py-4">
                <span
                  :class="[
                    'px-3 py-1 rounded text-white',
                    statusMap[unit.status].class
                  ]"
                >
                  {{ statusMap[unit.status].text }}
                </span>
              </td>

              <!-- ACTION -->
              <td class="px-6 py-4">
                <button
                  @click="openEditModal(unit)"
                  :disabled="unit.status === 2"
                  :class="[
                    'px-4 py-2 rounded text-white',
                    unit.status === 2
                      ? 'bg-gray-500 cursor-not-allowed opacity-50'
                      : 'bg-accent'
                  ]"
                >
                  Edit
                </button>
              </td>
            </tr>

            <tr v-if="!normalizedUnits.length">
              <td colspan="5" class="text-center py-4 text-gray-400">
                No measurement units found
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div
          class="flex items-center justify-between px-6 py-4 bg-gray-900"
          v-if="measurementUnits.links"
        >
          <span class="text-gray-400 text-sm">
            Showing {{ measurementUnits.from }} to {{ measurementUnits.to }}
            of {{ measurementUnits.total }}
          </span>

          <div class="flex gap-2">
            <button
              v-for="link in measurementUnits.links"
              :key="link.label"
              @click="link.url && router.visit(link.url)"
              :disabled="!link.url"
              v-html="link.label"
              :class="[
                'px-3 py-1 rounded',
                link.active
                  ? 'bg-accent text-white'
                  : link.url
                  ? 'bg-gray-700 text-white'
                  : 'bg-gray-800 text-gray-500 cursor-not-allowed'
              ]"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <MeasurementUnitCreateModal v-model:open="isCreateModalOpen" />
    <MeasurementUnitEditModal
      v-if="selectedUnit"
      v-model:open="isEditModalOpen"
      :unit="selectedUnit"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed, toRefs } from "vue";
import { router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import MeasurementUnitCreateModal from "./Components/MeasurementUnitCreateModal.vue";
import MeasurementUnitEditModal from "./Components/MeasurementUnitEditModal.vue";

const props = defineProps({
  measurementUnits: Object,
});

const { measurementUnits } = toRefs(props);

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const selectedUnit = ref(null);

/* STATUS MAP */
const statusMap = {
  0: { text: "Inactive", class: "bg-red-500" },
  1: { text: "Active", class: "bg-green-500" },
  2: { text: "Default", class: "bg-blue-500" },
};

/* Normalize status */
const normalizedUnits = computed(() => {
  const data = measurementUnits.value?.data || [];
  return data.map(u => ({
    ...u,
    status: Number(u.status) || 0,
  }));
});

const openCreateModal = () => {
  isCreateModalOpen.value = true;
};

const openEditModal = (unit) => {
  selectedUnit.value = unit;
  isEditModalOpen.value = true;
};
</script>
