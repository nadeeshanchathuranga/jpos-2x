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
          <h1 class="text-3xl font-bold text-white">Brands</h1>
        </div>
        <button
          @click="openCreateModal"
          class="px-6 py-2 text-white bg-accent rounded hover:bg-accent"
        >
          Add Brand
        </button>
      </div>

      <div class="overflow-hidden bg-dark border-4 border-accent rounded-lg">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-white">
            <thead class="bg-accent">
              <tr>
                <th class="px-6 py-3">ID</th>
                <th class="px-6 py-3">Brand Name</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(brand, index) in brands.data"
                :key="brand.id"
                class="border-b border-gray-700 hover:bg-gray-900"
              >
                <td class="px-6 py-4">
                  {{ (brands.current_page - 1) * brands.per_page + index + 1 }}
                </td>
                <td class="px-6 py-4">{{ brand.name }}</td>
                <td class="px-6 py-4">
                 
                  <span
                    :class="{
                      'bg-red-500 text-white px-3 py-1 rounded': brand.status == 0,
                      'bg-green-500 text-white px-3 py-1 rounded': brand.status == 1,   
                      'bg-blue-500 text-white px-3 py-1 rounded': brand.status == 2
                    }"
                  >
                    {{ brand.status == 1 ? 'Active' : brand.status == 0 ? 'Inactive' : 'Default' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <button
                    @click="openEditModal(brand)"
                    :disabled="brand.status == 2"
                    :class="[
                      'px-4 py-2 text-white rounded',
                      brand.status == 2
                        ? 'bg-gray-500 cursor-not-allowed opacity-50'
                        : 'bg-accent hover:bg-accent'
                    ]"
                  >
                    Edit
                  </button>
                </td>
              </tr>
              <tr v-if="!brands.data || brands.data.length === 0">
                <td colspan="4" class="px-6 py-4 text-center text-gray-400">
                  No brands found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="brands.links">
          <div class="text-sm text-gray-400">
            Showing {{ brands.from }} to {{ brands.to }} of {{ brands.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              v-for="link in brands.links"
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
    <BrandCreateModel v-model:open="isCreateModalOpen" />

    <!-- Edit Modal -->
    <BrandEditModel
      v-model:open="isEditModalOpen"
      :brand="selectedBrand"
      v-if="selectedBrand"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { logActivity } from "@/composables/useActivityLog";
import BrandCreateModel from "./Components/BrandCreateModel.vue";
import BrandEditModel from "./Components/BrandUpdateModel.vue";

defineProps({
  brands: {
    type: Object,
    required: true,
  },
});

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const selectedBrand = ref(null);

const openCreateModal = () => {
  isCreateModalOpen.value = true;
};

const openEditModal = async (brand) => {
  selectedBrand.value = brand;
  isEditModalOpen.value = true;

  // Log edit activity
  await logActivity('edit', 'brands', {
    brand_id: brand.id,
    brand_name: brand.name,
    status: brand.status,
  });
};
</script>

<style scoped>
/* Add any additional styles here */
</style>