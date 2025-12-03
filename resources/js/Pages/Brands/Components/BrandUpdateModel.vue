<template>
    <TransitionRoot as="template" :show="open">
      <Dialog class="relative z-10" @close="$emit('update:open', false)">
        <!-- Modal Overlay -->
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div
            class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
          />
        </TransitionChild>

        <!-- Modal Content -->
        <div class="fixed inset-0 z-10 flex items-center justify-center">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
          <DialogPanel
            class="bg-black border-4 border-blue-600 rounded-[20px] shadow-xl max-w-md w-full p-6 text-center"
          >
              <!-- Modal Title -->
              <DialogTitle class="text-xl font-bold text-white">
                Edit Brand
              </DialogTitle>
              <form @submit.prevent="submit">
                <!-- Modal Form -->
                <div class="mt-6 space-y-4 text-left">
                  <div>
                    <label class="block text-sm font-medium text-gray-300">
                      Brand Name:
                    </label>
                    <input
                      v-model="form.name"
                      type="text"
                      id="name"
                      required
                      class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    />
                    <span v-if="form.errors.name" class="mt-4 text-red-500">
                      {{ form.errors.name }}
                    </span>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-300">
                      Status:
                    </label>
                  <select
  v-model="form.status"
  id="status"
  required
  class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
>
  <option value="1">Active</option>
  <option value="0">Inactive</option>
</select>

                    <span v-if="form.errors.status" class="mt-4 text-red-500">
                      {{ form.errors.status }}
                    </span>
                  </div>

                  <!-- Modal Buttons -->
                  <div class="mt-6 space-x-4">
                    <button
                      class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700"
                      type="submit"  
                    >
                      Update
                    </button>
                    <button
                      @click="closeModal"
                      class="px-4 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400"
                      type="button"
                    >
                      Cancel
                    </button>
                  </div>
                </div>
              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </Dialog>
    </TransitionRoot>
  </template>

  <script setup>
  import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
  } from "@headlessui/vue";
  import { watch } from "vue";
  import { useForm } from "@inertiajs/vue3";

  const emit = defineEmits(["update:open"]);

  const props = defineProps({
    open: {
      type: Boolean,
      required: true,
    },
    brand: {
      type: Object,
      required: true,
    },
  });

  const form = useForm({
    name: props.brand.name || "",
    status: props.brand.status == 1 ? "1" : "0",
  });

  // Watch for brand changes
  watch(
    () => props.brand,
    (newValue) => {
      if (newValue) {
        form.name = newValue.name || "";
        form.status = newValue.status == 1 ? "1" : "0";
      }
    }
  );

  // Submit form
  const submit = () => {
    form.put(`/brands/${props.brand.id}`, {
      onSuccess: () => {
        emit("update:open", false);
      },
    });
  };

  const closeModal = () => {
    emit('update:open', false);
  };
  </script>
