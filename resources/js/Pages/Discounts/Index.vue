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
          <h1 class="text-3xl font-bold text-white">Discounts</h1>
        </div>
        <button
          @click="openCreateModal"
          class="px-6 py-2 text-white bg-accent rounded hover:bg-accent"
        >
          Add Discount
        </button>
      </div>

      <div class="overflow-hidden bg-dark border-4 border-accent rounded-lg">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-white">
            <thead class="bg-accent">
              <tr>
                <th class="px-6 py-3">ID</th>
                <th class="px-6 py-3">Name</th>
                <th class="px-6 py-3">Type</th>
                <th class="px-6 py-3">Value</th>
                <th class="px-6 py-3">Start Date</th>
                <th class="px-6 py-3">End Date</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(discount, index) in discounts.data"
                :key="discount.id"
                class="border-b border-gray-700 hover:bg-gray-900"
              >
                <td class="px-6 py-4">
                  {{ (discounts.current_page - 1) * discounts.per_page + index + 1 }}
                </td>
                <td class="px-6 py-4">{{ discount.name }}</td>
                <td class="px-6 py-4">
                  <span class="px-2 py-1 text-xs rounded" :class="discount.type == 0 ? 'bg-purple-500' : 'bg-orange-500'">
                    {{ discount.type == 0 ? 'Percentage' : 'Fixed' }}
                  </span>
                </td>
                <td class="px-6 py-4">{{ discount.type == 0 ? discount.value + '%' : (page.props.currency || '') + ' ' + discount.value }}</td>
                <td class="px-6 py-4">{{ discount.start_date || '-' }}</td>
                <td class="px-6 py-4">{{ discount.end_date || '-' }}</td>
                <td class="px-6 py-4">
                  <span
                    :class="{
                      'bg-red-500 text-white px-3 py-1 rounded': discount.status == 0,
                      'bg-green-500 text-white px-3 py-1 rounded': discount.status == 1,
                      'bg-blue-500 text-white px-3 py-1 rounded': discount.status == 2
                    }"
                  >
                    {{ discount.status == 1 ? 'Active' : discount.status == 0 ? 'Inactive' : 'Default' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <button
                    @click="openEditModal(discount)"
                    :disabled="discount.status == 2"
                    :class="[
                      'px-4 py-2 mr-2 text-white rounded',
                      discount.status == 2
                        ? 'bg-gray-500 cursor-not-allowed opacity-50'
                        : 'bg-accent hover:bg-accent'
                    ]"
                  >
                    Edit
                  </button>
                </td>
              </tr>
              <tr v-if="!discounts.data || discounts.data.length === 0">
                <td colspan="8" class="px-6 py-4 text-center text-gray-400">
                  No discounts found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="discounts.links">
          <div class="text-sm text-gray-400">
            Showing {{ discounts.from }} to {{ discounts.to }} of {{ discounts.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              v-for="link in discounts.links"
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
    <DiscountCreateModal v-model:open="isCreateModalOpen" />

    <!-- Edit Modal -->
    <DiscountEditModal
      v-model:open="isEditModalOpen"
      :discount="selectedDiscount"
      v-if="selectedDiscount"
    />


  </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import DiscountCreateModal from "./Components/DiscountCreateModal.vue";
import DiscountEditModal from "./Components/DiscountEditModal.vue";
import { logActivity } from '@/composables/useActivityLog';
const page = usePage();


defineProps({
  discounts: {
    type: Object,
    required: true,
  },
});

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const selectedDiscount = ref(null);

const openCreateModal = () => {
  isCreateModalOpen.value = true;
};

const openEditModal = async (discount) => {
  selectedDiscount.value = discount;
  isEditModalOpen.value = true;
  await logActivity('edit', 'discounts', {
    discount_id: discount.id,
    discount_name: discount.name
  });
};

</script>
