<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
  href: {
    type: String,
    required: true,
  },
  active: {
    type: Boolean,
  },
});

const classes = computed(() =>
  props.active
    ? "inline-flex items-center px-4 py-2 border-b-4 border-indigo-500 text-base font-semibold text-gray-900 bg-gradient-to-b from-blue-50 to-transparent focus:outline-none transition-all duration-200"
    : "inline-flex items-center px-4 py-2 border-b-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-900 hover:border-indigo-300 hover:bg-gray-50 focus:outline-none focus:text-gray-900 transition-all duration-200"
);
</script>

<template>
  <Link :href="href" :class="classes">
    <slot />
  </Link>
</template>
