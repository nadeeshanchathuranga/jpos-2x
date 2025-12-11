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
          <h1 class="text-3xl font-bold text-white">Categories</h1>
        </div>
        <button
          @click="openCreateModal"
          class="px-6 py-2 text-white bg-accent rounded hover:bg-accent"
        >
          Add Category
        </button>
      </div>

      <div class="overflow-hidden bg-dark border-4 border-accent rounded-lg">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-white">
            <thead class="bg-accent">
              <tr>
                <th class="px-6 py-3">ID</th>
                <th class="px-6 py-3">Category Name</th>
                <th class="px-6 py-3">Parent Category</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(category, index) in categories.data"
                :key="category.id"
                class="border-b border-gray-700 hover:bg-gray-900"
              >
                <td class="px-6 py-4">
                  {{ (categories.current_page - 1) * categories.per_page + index + 1 }}
                </td>
                <td class="px-6 py-4">{{ category.name }}</td>
                <td class="px-6 py-4">
                  {{ category.parent?.name || '-' }}
                </td>
                <td class="px-6 py-4">
                  <span
                    :class="{
                      'bg-red-500 text-white px-3 py-1 rounded': category.status == 0,
                      'bg-green-500 text-white px-3 py-1 rounded': category.status == 1,
                      'bg-blue-500 text-white px-3 py-1 rounded': category.status == 2
                    }"
                  >
                    {{ category.status == 1 ? 'Active' : category.status == 0 ? 'Inactive' : 'Default' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <button
                    @click="openEditModal(category)"
                    :disabled="category.status == 2"
                    :class="[
                      'px-4 py-2 text-white rounded',
                      category.status == 2
                        ? 'bg-gray-500 cursor-not-allowed opacity-50'
                        : 'bg-accent hover:bg-accent'
                    ]"
                  >
                    Edit
                  </button>
                </td>
              </tr>
              <tr v-if="!categories.data || categories.data.length === 0">
                <td colspan="5" class="px-6 py-4 text-center text-gray-400">
                  No categories found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="categories.links">
          <div class="text-sm text-gray-400">
            Showing {{ categories.from }} to {{ categories.to }} of {{ categories.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              v-for="link in categories.links"
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
    <CategoryCreateModal v-model:open="isCreateModalOpen" :categories="categories.data" />

    <!-- Edit Modal -->
    <CategoryEditModal
      v-model:open="isEditModalOpen"
      :category="selectedCategory"
      :categories="categories.data"
      v-if="selectedCategory"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import CategoryCreateModal from "./Components/CategoryCreateModal.vue";
import CategoryEditModal from "./Components/CategoryEditModal.vue";

defineProps({
  categories: {
    type: Object,
    required: true,
  },
});

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const selectedCategory = ref(null);

const openCreateModal = () => {
  isCreateModalOpen.value = true;
};

const openEditModal = (category) => {
  selectedCategory.value = category;
  isEditModalOpen.value = true;
};
</script>
