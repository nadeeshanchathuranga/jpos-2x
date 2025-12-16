<template>
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-75">
        <div class="relative w-full max-w-4xl p-6 mx-4 my-8 bg-black border-4 border-blue-600 rounded-lg max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-white">Purchase Order Request Details</h2>
                <button @click="closeModal" class="text-white hover:text-gray-300">
                    <i class="text-2xl fas fa-times"></i>
                </button>
            </div>

            <div v-if="por">
                <!-- Order Information -->
                <div class="mb-6 overflow-hidden border-2 border-blue-500 rounded-lg">
                    <div class="px-6 py-3 bg-blue-600">
                        <h5 class="font-bold text-white">Order Information</h5>
                    </div>
                    <div class="p-6 bg-gray-900">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="block mb-1 text-sm font-medium text-gray-400">Order Number</label>
                                <p class="text-lg font-semibold text-white">{{ por.order_number }}</p>
                            </div>
                            <div>
                                <label class="block mb-1 text-sm font-medium text-gray-400">Order Date</label>
                                <p class="text-lg font-semibold text-white">{{ formatDate(por.order_date) }}</p>
                            </div>
                            <div>
                                <label class="block mb-1 text-sm font-medium text-gray-400">User</label>
                                <p class="text-lg font-semibold text-white">{{ por.user?.name || 'N/A' }}</p>
                            </div>
                            
                            <div>
                                <label class="block mb-1 text-sm font-medium text-gray-400">Status</label>
                                <span :class="getStatusClass(por.status)">
                                    {{ por.status.toUpperCase() }}
                                </span>
                            </div>
                            
                        </div>
                    </div>
                </div>

                <!-- Products -->
                <div class="overflow-hidden border-2 border-blue-500 rounded-lg">
                    <div class="px-6 py-3 bg-blue-600">
                        <h5 class="font-bold text-white">Products</h5>
                    </div>
                    <div class="p-6 bg-gray-900">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-white">
                                <thead class="bg-gray-800">
                                    <tr>
                                        <th class="px-4 py-2">Product</th>
                                        <th class="px-4 py-2">Quantity</th>
                                        <th class="px-4 py-2">Unit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in por.por_products" :key="item.id" class="border-b border-gray-700">
                                        <td class="px-4 py-3">{{ item.product?.name || 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ item.requested_quantity }}</td>
                                        <td class="px-4 py-3">
                                            {{ getMeasurementUnitSymbol(item) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-4 mt-6">
                    <button type="button" @click="closeModal"
                        class="px-6 py-2 text-white bg-gray-600 rounded hover:bg-gray-700">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
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
        'pending': 'bg-yellow-500 text-white px-3 py-1 rounded',
        'approved': 'bg-green-500 text-white px-3 py-1 rounded',
        'rejected': 'bg-red-500 text-white px-3 py-1 rounded',
        'completed': 'bg-blue-500 text-white px-3 py-1 rounded'
    };
    return classes[status] || 'bg-gray-500 text-white px-3 py-1 rounded';
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
