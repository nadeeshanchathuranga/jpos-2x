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
              class="bg-black border-4 border-red-600 rounded-[20px] shadow-xl max-w-md w-full p-6 text-center"
            >
              <!-- Modal Title -->
              <DialogTitle class="text-xl font-bold text-white">
                Delete Brand
              </DialogTitle>

              <!-- Warning Icon -->
              <div class="flex justify-center mt-4">
                <div class="flex items-center justify-center w-16 h-16 bg-red-600 rounded-full">
                  <svg
                    class="w-8 h-8 text-white"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                    />
                  </svg>
                </div>
              </div>

              <!-- Confirmation Message -->
              <div class="mt-6 text-gray-300">
                <p class="text-lg">Are you sure you want to delete this brand?</p>
                <p class="mt-2 text-sm font-semibold text-white">
                  Brand: <span class="text-red-500">{{ brand.name }}</span>
                </p>
                <p class="mt-2 text-sm text-gray-400">
                  This action cannot be undone.
                </p>
              </div>

              <!-- Modal Buttons -->
              <div class="flex justify-center mt-6 space-x-4">
                <button
                  @click="confirmDelete"
                  class="px-6 py-2 text-white bg-red-600 rounded hover:bg-red-700"
                  type="button"
                >
                  Delete
                </button>
                <button
                  @click="closeModal"
                  class="px-6 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400"
                  type="button"
                >
                  Cancel
                </button>
              </div>
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
  import { router } from "@inertiajs/vue3";

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

  const confirmDelete = () => {
    router.delete(`/brands/${props.brand.id}`, {
      onSuccess: () => {
        emit("update:open", false);
      },
    });
  };

  const closeModal = () => {
    emit("update:open", false);
  };
  </script>
