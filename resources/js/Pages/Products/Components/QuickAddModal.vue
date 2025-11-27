<!-- Resources/js/Components/QuickAddModal.vue -->
<template>
  <Modal :show="show" @close="close" max-width="sm">
    <div class="p-6 bg-gray-900 text-white">
      <h2 class="text-xl font-bold mb-4">
        Add New {{ type.charAt(0).toUpperCase() + type.slice(1) }}
      </h2>

      <form @submit.prevent="submit">
        <div class="mb-4">
          <label class="block text-sm font-medium mb-2">
            {{ type.charAt(0).toUpperCase() + type.slice(1) }} Name
            <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.name"
            type="text"
            required
            autofocus
            class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
            :placeholder="'Enter ' + type + ' name'"
          />
          <div v-if="form.errors.name" class="text-sm text-red-500 mt-1">
            {{ form.errors.name }}
          </div>
        </div>

        <div class="flex justify-end space-x-3">
          <button
            type="button"
            @click="close"
            class="px-5 py-2 bg-gray-600 rounded hover:bg-gray-700 transition"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-5 py-2 bg-blue-600 rounded hover:bg-blue-700 disabled:opacity-50 transition"
          >
            {{ form.processing ? 'Adding...' : 'Add ' + type.charAt(0).toUpperCase() + type.slice(1) }}
          </button>
        </div>
        </form>
        </div>
     </Modal>
    </template>

    <script setup>
    import { ref } from 'vue';
    import { useForm } from '@inertiajs/vue3';
    import Modal from '@/Components/Modal.vue';

    const props = defineProps({
      show: Boolean,
      type: {
        type: String,
        required: true, // 'brand', 'category', or 'type'
      },
      routeName: String, 
    });

    const emit = defineEmits(['close', 'created']);
    
    const form = useForm({
      name: '',
      status: 1,
    });
    
    const submit = () => {
      form.post(route(props.routeName), {
        onSuccess: (page) => {
          form.reset();
    
          // Extract the newly created item from Inertia response
          const newItem = page.props.flash?.newItem 
                       || page.props.newBrand 
                       || page.props.newCategory 
                       || page.props.newType;
    
          // Emit both events
          emit('created', newItem);  // ← Pass the actual object
          emit('close');
        },
        onError: (errors) => {
          console.error(errors);
        },
      });
    };

    const close = () => {
      form.reset();
      form.clearErrors();
      emit('close');
    };
    </script>