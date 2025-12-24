<template>
    <Head title="Product Returns" />

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
                    <h1 class="text-3xl font-bold text-white">Product Returns</h1>
                </div>
                <button
                    @click="openCreateModal"
                    class="px-6 py-2 text-white bg-accent rounded hover:bg-accent"
                >
                    Add New Sales Return
                </button>
            </div>

            <div class="overflow-hidden bg-dark border-4 border-accent rounded-lg">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-white">
                        <thead class="bg-accent">
                            <tr>
                                <th class="px-6 py-3">Return No</th>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Customer</th>
                                <th class="px-6 py-3 text-center">Return Type</th>
                                <th class="px-6 py-3 text-center">Products</th>
                                <th class="px-6 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="returnItem in returns.data"
                                :key="returnItem.id"
                                class="border-b border-gray-700 hover:bg-gray-900"
                            >
                                <td class="px-6 py-4">
                                    <span class="font-semibold">{{ returnItem.return_no || `RET-${returnItem.id}` }}</span>
                                </td>
                                <td class="px-6 py-4">{{ returnItem.return_date_formatted || formatDate(returnItem.return_date) || 'N/A' }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-medium">{{ returnItem.customer_name || returnItem.customer?.name || 'Walk-in Customer' }}</div>
                                    <div class="text-sm text-gray-400">{{ returnItem.customer_phone || returnItem.customer?.contact || '' }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span 
                                        :class="[
                                            'px-3 py-1 rounded text-xs font-semibold',
                                            (returnItem.return_type === 1 || !returnItem.return_type) ? 'bg-blue-600 text-white' : 'bg-green-600 text-white'
                                        ]"
                                    >
                                        {{ returnItem.return_type === 2 ? '💰 Cash Refund' : '🔄 Product Return' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 bg-blue-600 text-white rounded text-sm">
                                        {{ returnItem.products_count || returnItem.return_products?.length || 0 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button
                                        @click="openViewModal(returnItem)"
                                        class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600 mr-2"
                                    >
                                        View
                                    </button>
                                    <a
                                        :href="route('return.export.bill.pdf', returnItem.id)"
                                        class="px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700"
                                        target="_blank"
                                    >
                                        Print Bill
                                    </a>
                                </td>
                            </tr>
                            <tr v-if="!returns.data || returns.data.length === 0">
                                <td colspan="6" class="px-6 py-4 text-center text-gray-400">

                                    No Product Returns found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="returns.links && returns.links.length > 3">
                    <div class="text-sm text-gray-400">
                        Showing {{ returns.from }} to {{ returns.to }} of {{ returns.total }} results
                    </div>
                    <div class="flex space-x-2">
                        <button
                            v-for="link in returns.links"
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
        <ReturnCreateModal
            v-model:open="isCreateModalOpen"
            :sales-products="salesProducts"
            :shop-products="shopProducts"
            @success="handleSuccess"
        />

        <!-- View Modal -->
        <ReturnDetailsModal
            :open="showDetailsModal"
            :return-data="selectedReturn"
            @close="closeDetailsModal"
            @update-status="updateStatus"
        />


    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import ReturnDetailsModal from './Components/ReturnDetailsModal.vue'
import ReturnCreateModal from './Components/ReturnCreateModal.vue'
import { logActivity } from '@/composables/useActivityLog'


const props = defineProps({
    returns: Object,
    salesProducts: Object,
    shopProducts: Array,
    filters: Object,
})

const isCreateModalOpen = ref(false)
const showDetailsModal = ref(false)
const selectedReturn = ref(null)

const openCreateModal = () => {
    isCreateModalOpen.value = true
}

const openViewModal = async (returnItem) => {
    selectedReturn.value = returnItem
    showDetailsModal.value = true
    await logActivity('view', 'product_returns', {
        return_id: returnItem.id,
        invoice_number: returnItem.invoice_number
    })
}

const closeDetailsModal = () => {
    showDetailsModal.value = false
    selectedReturn.value = null
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    })
}

const getStatusClass = (status) => {
    const classes = {
        '0': 'bg-yellow-500 text-white px-3 py-1 rounded',
        '1': 'bg-green-500 text-white px-3 py-1 rounded',
        '2': 'bg-red-500 text-white px-3 py-1 rounded'
    }
    return classes[status] || 'bg-gray-500 text-white px-3 py-1 rounded'
}

const updateStatus = (returnItem, newStatus) => {
    router.patch(route('return.update-status', returnItem.id), { 
        status: parseInt(newStatus) 
    }, {
        onSuccess: () => {
            if (showDetailsModal.value && selectedReturn.value?.id === returnItem.id) {
                selectedReturn.value.status = parseInt(newStatus)
            }
        },
        onError: () => {
            // Error occurred
        }
    })
}

const handleSuccess = () => {
    isCreateModalOpen.value = false
    router.reload()
}
</script>
