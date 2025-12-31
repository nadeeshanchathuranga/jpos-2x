<template>
    <Modal :show="open" @close="closeModal" max-width="4xl">
        <div class="p-6 bg-gray-50">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-blue-600">Purchase Order Request Details</h2>
                <button
                    type="button"
                    @click="closeModal"
                    class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-200 rounded-full transition-all duration-200"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <div v-if="por">
                <!-- Order Information -->
                <div class="mb-4 bg-white rounded-xl p-4 shadow-sm border border-gray-200">
                    <h3 class="mb-3 text-lg font-semibold text-blue-600 flex items-center gap-2">
                        📋 Order Information
                    </h3>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div class="p-3 bg-white rounded-lg border border-gray-200">
                            <label class="block mb-1 text-xs font-medium text-gray-500">Order Number</label>
                            <p class="text-base font-semibold text-gray-900">{{ por.order_number }}</p>
                        </div>
                        <div class="p-3 bg-white rounded-lg border border-gray-200">
                            <label class="block mb-1 text-xs font-medium text-gray-500">Order Date</label>
                            <p class="text-base font-semibold text-gray-900">{{ formatDate(por.order_date) }}</p>
                        </div>
                        <div class="p-3 bg-white rounded-lg border border-gray-200">
                            <label class="block mb-1 text-xs font-medium text-gray-500">User</label>
                            <p class="text-base font-semibold text-gray-900">{{ por.user?.name || 'N/A' }}</p>
                        </div>
                        <div class="p-3 bg-white rounded-lg border border-gray-200">
                            <label class="block mb-1 text-xs font-medium text-gray-500">Status</label>
                            <span :class="getStatusClass(por.status)">
                                {{ por.status.toUpperCase() }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Products -->
                <div class="mb-4 bg-white rounded-xl p-4 shadow-sm border border-gray-200">
                    <h3 class="mb-3 text-lg font-semibold text-blue-600 flex items-center gap-2">
                        📦 Products
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b-2 border-blue-600">
                                    <th class="px-4 py-3 text-blue-600 font-semibold text-sm">Product</th>
                                    <th class="px-4 py-3 text-blue-600 font-semibold text-sm">Quantity</th>
                                    <th class="px-4 py-3 text-blue-600 font-semibold text-sm">Unit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in por.por_products" :key="item.id" class="border-b border-gray-200 hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-4 py-3 text-gray-900 font-medium">{{ item.product?.name || 'N/A' }}</td>
                                    <td class="px-4 py-3 text-gray-800">{{ item.requested_quantity }}</td>
                                    <td class="px-4 py-3 text-gray-800">
                                        {{ getMeasurementUnitSymbol(item) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-4 mt-6">
                    <button type="button" @click="closeModal"
                        class="px-6 py-2.5 rounded-full font-medium text-sm bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 hover:border-gray-300 transition-all duration-200">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    por: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['update:open']);

const closeModal = () => {
    emit('update:open', false);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

const formatNumber = (number) => {
    return parseFloat(number || 0).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const getStatusClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-500 text-white px-4 py-1.5 rounded-full font-medium text-xs',
        'approved': 'bg-green-500 text-white px-4 py-1.5 rounded-full font-medium text-xs',
        'rejected': 'bg-red-500 text-white px-4 py-1.5 rounded-full font-medium text-xs',
        'completed': 'bg-blue-500 text-white px-4 py-1.5 rounded-full font-medium text-xs',
        'active': 'bg-green-500 text-white px-4 py-1.5 rounded-full font-medium text-xs'
    };
    return classes[status] || 'bg-gray-500 text-white px-4 py-1.5 rounded-full font-medium text-xs';
};

const getMeasurementUnitSymbol = (item) => {
    // Prefer the purchaseUnit relation loaded from backend
    if (item.product?.purchaseUnit?.symbol) {
        return item.product.purchaseUnit.symbol;
    }
    // Fallback to a snake_case key if the serializer used it
    if (item.product?.purchase_unit?.symbol) {
        return item.product.purchase_unit.symbol;
    }
    return 'N/A';
};
</script>
