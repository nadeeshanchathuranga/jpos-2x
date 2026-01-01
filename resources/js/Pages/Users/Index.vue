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
          <h1 class="text-3xl font-bold text-white">Users</h1>
        </div>
        <button
          @click="openCreateModal"
          class="px-6 py-2 text-white bg-accent rounded hover:bg-accent"
        >
          Add User
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
                <th class="px-6 py-3">User Type</th>
                <th class="px-6 py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(user, index) in users.data"
                :key="user.id"
                class="border-b border-gray-700 hover:bg-gray-900"
              >
                <td class="px-6 py-4">
                  {{ (users.current_page - 1) * users.per_page + index + 1 }}
                </td>
                <td class="px-6 py-4">{{ user.name }}</td>
                <td class="px-6 py-4">{{ user.email }}</td>
                <td class="px-6 py-4">
                  <span
                    :class="{
                      'bg-purple-500 text-white px-3 py-1 rounded': user.role == 0,
                      'bg-blue-500 text-white px-3 py-1 rounded': user.role == 1,
                      'bg-green-500 text-white px-3 py-1 rounded': user.role == 2,
                      'bg-cyan-500 text-white px-3 py-1 rounded': user.role == 3
                    }"
                  >
                    {{ getUserType(user.role) }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <button
                    @click="openEditModal(user)"
                    class="px-4 py-2 mr-2 text-white bg-accent rounded hover:bg-accent"
                  >
                    Edit
                  </button>
                  <button
                    @click="openDeleteModal(user)"
                    class="px-4 py-2 text-white bg-red-500 rounded hover:bg-red-600"
                  >
                    Delete
                  </button>
                </td>
              </tr>
              <tr v-if="!users.data || users.data.length === 0">
                <td colspan="5" class="px-6 py-4 text-center text-gray-400">
                  No users found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="users.links">
          <div class="text-sm text-gray-400">
            Showing {{ users.from }} to {{ users.to }} of {{ users.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              v-for="link in users.links"
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
    <UserCreateModal v-model:open="isCreateModalOpen" />

    <!-- Edit Modal -->
    <UserEditModal
      v-model:open="isEditModalOpen"
      :user="selectedUser"
      v-if="selectedUser"
    />

    <!-- Delete Modal -->
    <UserDeleteModal
      v-model:open="isDeleteModalOpen"
      :user="selectedUserForDelete"
      v-if="selectedUserForDelete"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import UserCreateModal from "./Components/UserCreateModal.vue";
import UserEditModal from "./Components/UserEditModal.vue";
import UserDeleteModal from "./Components/UserDeleteModal.vue";

defineProps({
  users: {
    type: Object,
    required: true,
  },
});

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedUser = ref(null);
const selectedUserForDelete = ref(null);

const getUserType = (type) => {
  const types = {
    0: 'Admin',
    1: 'Manager',
    2: 'Cashier',
    3: 'Stock Keeper'
  };
  return types[type] || 'Unknown';
};

const openCreateModal = () => {
  isCreateModalOpen.value = true;
};

const openEditModal = (user) => {
  selectedUser.value = user;
  isEditModalOpen.value = true;
};

const openDeleteModal = (user) => {
  selectedUserForDelete.value = user;
  isDeleteModalOpen.value = true;
};
</script>
