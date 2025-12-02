
<!-- resources/js/Components/QuickAddModal.vue -->
<template>
  <Modal :show="show" @close="close" max-width="sm">
    <div class="p-6 bg-gray-900 text-white">
      <!-- Dynamic Modal Title based on type -->
      <h2 class="text-xl font-bold mb-6">
        Add New {{ type === 'unit' ? 'Measurement Unit' : type.charAt(0).toUpperCase() + type.slice(1) }}
      </h2>

      <form @submit.prevent="submit">
        <!-- Name Field (Required for all types) -->
        <div class="mb-4">
          <label class="block text-sm font-medium mb-2">
            Name <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.name"
            type="text"
            required
            autofocus
            class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
            placeholder="e.g. Carton, Liter"
          />
        </div>

        <!-- Symbol Field (Only for measurement units) -->
        <div v-if="type === 'unit'" class="mb-4">
          <label class="block text-sm font-medium mb-2">
            Symbol <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.symbol"
            type="text"
            required
            class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
            placeholder="e.g. ctn, L, kg"
          />
        </div>

        <!-- Status Field (Only for measurement units) -->
        <div v-if="type === 'unit'" class="mb-6">
          <label class="block text-sm font-medium mb-2">Status</label>
          <div class="flex gap-6">
            <label class="flex items-center cursor-pointer">
              <input type="radio" v-model="form.status" value="1" class="mr-2" />
              <span>Active</span>
            </label>
            <label class="flex items-center cursor-pointer">
              <input type="radio" v-model="form.status" value="0" class="mr-2" />
              <span>Inactive</span>
            </label>
          </div>
        </div>

        <!-- Form Action Buttons -->
        <div class="flex justify-end space-x-3">
          <button type="button" @click="close" class="px-6 py-2.5 bg-gray-600 rounded hover:bg-gray-700">
            Cancel
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-2.5 bg-blue-600 rounded hover:bg-blue-700 disabled:opacity-50"
          >
            {{ form.processing ? 'Adding...' : 'Add' }}
          </button>
        </div>
      </form>
    </div>
  </Modal>
</template>

<script setup>
/**
 * Quick Add Modal Component Script
 * 
 * Handles rapid creation of supporting data without page navigation
 * Dynamically adapts form fields based on data type
 */

import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'

/**
 * Component Props
 * @property {Boolean} show - Modal visibility state
 * @property {String} type - Data type: 'brand', 'category', 'type', or 'unit'
 * @property {String} routeName - Laravel route name for POST submission (e.g., 'brands.store')
 */
const props = defineProps({
  show: Boolean,
  type: String,
  routeName: String,
})

/**
 * Component Emits
 * @event close - Emitted when modal is closed
 * @event created - Emitted with newly created item data
 */
const emit = defineEmits(['close', 'created'])

/**
 * Inertia Form Instance
 * Handles form data and submission state
 */
const form = useForm({
  name: '',
  symbol: '',
  status: '1',
})

/**
 * Submit Form Handler
 * Posts data to appropriate route and returns new item to parent
 * Removes symbol field for non-unit types to avoid validation errors
 */
const submit = () => {
  // For non-unit types, remove symbol to avoid validation errors
  if (props.type !== 'unit') {
    delete form.symbol
    form.status = '1'
  }

  form.post(route(props.routeName), {
    preserveScroll: true,
    onSuccess: (page) => {
      // Extract newly created item from response props
      const newItem =
        page.props.newUnit ||
        page.props.newBrand ||
        page.props.newCategory ||
        page.props.newType

      // Pass new item back to parent component
      emit('created', newItem)
      close()
    },
    onError: (errors) => {
      console.error('Error:', errors)
    },
  })
}

/**
 * Close Modal Handler
 * Resets form and emits close event
 */
const close = () => {
  form.reset()
  form.clearErrors()
  emit('close')
}
</script>
