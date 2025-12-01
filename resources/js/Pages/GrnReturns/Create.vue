<template>
  <AppLayout >
    <div class="p-6">
      <h1 class="text-2xl font-bold text-white mb-4">Create GRN Return</h1>

      <form @submit.prevent="submit" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-gray-300 mb-1">GRN</label>
            <select v-model="form.grn_id" class="w-full p-2 bg-gray-800 text-white rounded">
              <option value="">Select GRN</option>
              <option v-for="g in grns" :key="g.id" :value="g.id">{{ g.grn_no }} - {{ g.supplier?.name || 'N/A' }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm text-gray-300 mb-1">Date</label>
            <input type="date" v-model="form.date" class="w-full p-2 bg-gray-800 text-white rounded" />
          </div>
        </div>

        <div>
          <label class="block text-sm text-gray-300 mb-1">Products</label>
          <div v-for="(p, idx) in form.products" :key="idx" class="flex space-x-2 mb-2">
            <select v-model="p.product_id" class="flex-1 p-2 bg-gray-800 text-white rounded">
              <option value="">Select product</option>
              <option v-for="prod in products" :key="prod.id" :value="prod.id">{{ prod.name }}</option>
            </select>
            <input type="number" v-model.number="p.qty" min="1" placeholder="Qty" class="w-24 p-2 bg-gray-800 text-white rounded" />
            <input v-model="p.remarks" placeholder="Remarks" class="flex-1 p-2 bg-gray-800 text-white rounded" />
            <button type="button" @click="removeProduct(idx)" class="px-3 py-1 bg-red-600 text-white rounded">Remove</button>
          </div>

          <button type="button" @click="addProduct" class="px-4 py-2 bg-green-600 text-white rounded">Add Product</button>
        </div>

        <div class="pt-4">
          <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded">Save Return</button>
          <inertia-link :href="route('grn-returns.index')" class="ml-3 px-6 py-2 bg-gray-700 text-white rounded">Cancel</inertia-link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
  grns: Array,
  products: Array,
  user: Object,
});

const form = reactive({
  grn_id: '',
  date: new Date().toISOString().substr(0,10),
  user_id: '',
  products: [],
});

// If backend provided current user, set it
if (user && user.id) form.user_id = user.id;

const addProduct = () => {
  form.products.push({ product_id: '', qty: 1, remarks: '' });
};

const removeProduct = (idx) => {
  form.products.splice(idx, 1);
};

const submit = () => {
  router.post(route('grn-returns.store'), form);
};
</script>

<style scoped></style>
