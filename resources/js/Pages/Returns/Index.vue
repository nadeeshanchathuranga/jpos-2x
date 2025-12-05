<template>
    <Head title="Product Returns" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-secondary p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-4">
                            <button
                                @click="$inertia.visit(route('dashboard'))"
                                class="px-4 py-2 text-white bg-accent rounded hover:bg-accent/80 transition"
                            >
                                Back
                            </button>
                            <div>
                                <h1 class="text-3xl font-bold text-white mb-1">🔄 Product Returns</h1>
                                <p class="text-gray-300">Manage all product returns and process refunds</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-300">Total Returns</div>
                            <div class="text-2xl font-bold text-amber-400">{{ returns.total || 0 }}</div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="mb-6">
                        <div class="flex space-x-1 bg-gray-800 p-1 rounded-lg w-fit">
                            <button
                                @click="activeTab = 'returns'"
                                :class="[
                                    'px-4 py-2 rounded-md text-sm font-medium transition-colors',
                                    activeTab === 'returns' 
                                        ? 'bg-amber-600 text-white' 
                                        : 'text-gray-400 hover:text-white'
                                ]"
                            >
                                Returns List ({{ returns.total || 0 }})
                            </button>
                            <button
                                @click="activeTab = 'create'"
                                :class="[
                                    'px-4 py-2 rounded-md text-sm font-medium transition-colors',
                                    activeTab === 'create' 
                                        ? 'bg-amber-600 text-white' 
                                        : 'text-gray-400 hover:text-white'
                                ]"
                            >
                                Create Return ({{ salesProducts?.total || 0 }} products available)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Returns List Tab -->
                <div v-if="activeTab === 'returns'">
                    <ReturnFilters
                        :filters="filters"
                        :status-options="statusOptions"
                        @apply-filters="applyFilters"
                        @clear-filters="clearFilters"
                        @update:search="(val) => filters.search = val"
                        @update:status="(val) => filters.status = val"
                        @update:date_from="(val) => filters.date_from = val"
                        @update:date_to="(val) => filters.date_to = val"
                    />
                    
                    <ReturnsListTable
                        :returns="returns"
                        @view-return="viewReturn"
                        @update-status="updateStatus"
                        @navigate="(url) => $inertia.visit(url)"
                    />
                </div>

                <!-- Create Return Tab -->
                <div v-if="activeTab === 'create'">
                    <!-- Sales Products Filters -->
                    <div class="bg-gray-800 rounded-lg p-6 shadow-lg mb-6">
                        <h3 class="text-lg font-semibold text-white mb-4">🔍 Find Sales Products Available for Return</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Search</label>
                                <input
                                    v-model="salesFilters.sales_search"
                                    type="text"
                                    placeholder="Customer, Sale No, Product..."
                                    class="w-full px-3 py-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">From Date</label>
                                <input
                                    v-model="salesFilters.sales_date_from"
                                    type="date"
                                    class="w-full px-3 py-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">To Date</label>
                                <input
                                    v-model="salesFilters.sales_date_to"
                                    type="date"
                                    class="w-full px-3 py-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500"
                                />
                            </div>
                            <div class="flex items-end">
                                <button
                                    @click="applySalesFilters"
                                    class="w-full px-6 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition"
                                >
                                    Search Products
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Products for Return -->
                    <div v-if="selectedProducts.length > 0" class="bg-green-800 rounded-lg p-6 shadow-lg mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-white">Selected Products for Return ({{ selectedProducts.length }})</h3>
                            <div class="flex gap-2">
                                <button
                                    @click="clearSelection"
                                    class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition"
                                >
                                    Clear All
                                </button>
                                <button
                                    @click="createReturn"
                                    :disabled="processing"
                                    class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition disabled:opacity-50"
                                >
                                    {{ processing ? 'Creating...' : 'Create Return' }}
                                </button>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-white text-sm">
                                <thead class="bg-green-700">
                                    <tr>
                                        <th class="px-3 py-2">Product</th>
                                        <th class="px-3 py-2">Sale Info</th>
                                        <th class="px-3 py-2 text-center">Sold Qty</th>
                                        <th class="px-3 py-2 text-center">Return Qty</th>
                                        <th class="px-3 py-2 text-center">Unit Price</th>
                                        <th class="px-3 py-2 text-center">Refund Amount1</th>
                                        <th class="px-3 py-2 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="product in selectedProducts" :key="product.id" class="border-b border-green-700">
                                        <td class="px-3 py-2">
                                            <div class="font-medium">{{ product.product_name }}</div>
                                            <div class="text-xs text-gray-300">{{ product.product_barcode }}</div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="text-xs">{{ product.sale_no }} - {{ product.sale_date_formatted }}</div>
                                            <div class="text-xs text-gray-300">{{ product.customer_name }}</div>
                                        </td>
                                        <td class="px-3 py-2 text-center">{{ product.quantity_sold }}</td>
                                        <td class="px-3 py-2 text-center">
                                            <input
                                                v-model.number="product.return_quantity"
                                                type="number"
                                                min="1"
                                                :max="product.quantity_sold"
                                                class="w-20 px-2 py-1 bg-gray-700 text-white border border-gray-600 rounded text-center"
                                            />
                                        </td>
                                        <td class="px-3 py-2 text-center">${{ product.formatted_price }}</td>
                                        <td class="px-3 py-2 text-center font-semibold text-green-300">
                                            ${{ ((product.return_quantity || 0) * parseFloat(product.price || 0)).toFixed(2) }}
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <button
                                                @click="removeFromSelection(product.id)"
                                                class="px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 transition"
                                            >
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Available Sales Products -->
                    <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                        <div class="p-4 bg-gray-900">
                            <h3 class="text-lg font-semibold text-white">Available Products for Return</h3>
                            <p class="text-sm text-gray-400">Select products from recent sales to create a return</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-white">
                                <thead class="bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 font-semibold">Product</th>
                                        <th class="px-4 py-3 font-semibold">Sale No</th>
                                        <th class="px-4 py-3 font-semibold">Customer</th>
                                        <th class="px-4 py-3 font-semibold text-center">Qty Sold</th>
                                        <th class="px-4 py-3 font-semibold text-center">Unit Price</th>
                                        <th class="px-4 py-3 font-semibold text-center">Total</th>
                                        <th class="px-4 py-3 font-semibold text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="product in salesProducts?.data"
                                        :key="product.id"
                                        class="border-b border-gray-700 hover:bg-gray-700/50 transition"
                                    >
                                        <td class="px-4 py-3">
                                            <div class="font-medium">{{ product.product_name }}</div>
                                            <div class="text-sm text-gray-400">{{ product.product_barcode }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-amber-400">{{ product.sale_no }}</div>
                                            <div class="text-sm text-gray-400">{{ product.sale_date_formatted }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium">{{ product.customer_name }}</div>
                                            <div class="text-sm text-gray-400">{{ product.customer_phone || '' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-center">{{ product.quantity_sold }}</td>
                                        <td class="px-4 py-3 text-center">${{ product.formatted_price }}</td>
                                        <td class="px-4 py-3 text-center font-semibold">${{ product.formatted_total }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <button
                                                v-if="!isProductSelected(product.id)"
                                                @click="addToSelection(product)"
                                                class="px-3 py-1 bg-amber-600 text-white text-sm rounded hover:bg-amber-700 transition"
                                            >
                                                + Add
                                            </button>
                                            <span v-else class="px-3 py-1 bg-green-600 text-white text-sm rounded">
                                                Selected
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="!salesProducts?.data || salesProducts.data.length === 0">
                                        <td colspan="8" class="px-6 py-8 text-center text-gray-400">
                                            <div class="text-6xl mb-4">🛒</div>
                                            <div class="text-xl font-semibold mb-2">No returnable products found</div>
                                            <div class="text-sm">No sales products available for return. Try adjusting your search filters.</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Sales Products Pagination -->
                        <div class="flex items-center justify-between px-6 py-4 bg-gray-900" v-if="salesProducts?.links && salesProducts.data?.length > 0">
                            <div class="text-sm text-gray-400">
                                Showing {{ salesProducts.from }} to {{ salesProducts.to }} of {{ salesProducts.total }} products
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    v-for="link in salesProducts.links"
                                    :key="link.label"
                                    @click="link.url ? $inertia.visit(link.url) : null"
                                    :disabled="!link.url"
                                    :class="[
                                        'px-3 py-1 text-sm rounded',
                                        link.active
                                            ? 'bg-amber-600 text-white'
                                            : link.url
                                            ? 'bg-gray-700 text-gray-300 hover:bg-gray-600'
                                            : 'bg-gray-800 text-gray-500 cursor-not-allowed'
                                    ]"
                                    v-html="link.label"
                                ></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Return Details Modal -->
        <ReturnDetailsModal
            :open="showDetailsModal"
            :return-data="selectedReturn"
            @close="closeDetailsModal"
            @update-status="updateStatus"
        />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, watch, computed, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ReturnFilters from './Components/ReturnFilters.vue'
import ReturnsListTable from './Components/ReturnsListTable.vue'
import ReturnDetailsModal from './Components/ReturnDetailsModal.vue'

// Props
const props = defineProps({
    returns: Object,
    salesProducts: Object,
    filters: Object,
})

// State
const activeTab = ref('returns')
const showDetailsModal = ref(false)
const selectedReturn = ref(null)
const selectedProducts = ref([])
const processing = ref(false)

// Filters
const filters = reactive({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
})

const salesFilters = reactive({
    sales_search: '',
    sales_date_from: '',
    sales_date_to: '',
})

const statusOptions = [
    { value: 0, label: 'Pending' },
    { value: 1, label: 'Approved' },
    { value: 2, label: 'Rejected' },
]

// Methods
const applyFilters = () => {
    router.get(route('return.index'), filters, { preserveState: true })
}

const clearFilters = () => {
    filters.search = ''
    filters.status = ''
    filters.date_from = ''
    filters.date_to = ''
    applyFilters()
}

const applySalesFilters = () => {
    router.get(route('return.index'), {
        ...filters,
        ...salesFilters,
        tab: 'create'
    }, { preserveState: true })
}

const isProductSelected = (productId) => {
    return selectedProducts.value.some(p => p.id === productId)
}

const addToSelection = (product) => {
    if (!isProductSelected(product.id)) {
        selectedProducts.value.push({
            ...product,
            return_quantity: 1
        })
    }
}

const removeFromSelection = (productId) => {
    selectedProducts.value = selectedProducts.value.filter(p => p.id !== productId)
}

const clearSelection = () => {
    selectedProducts.value = []
}

const createReturn = () => {
    if (selectedProducts.value.length === 0) {
        alert('Please select at least one product to return.')
        return
    }

    const invalidProducts = selectedProducts.value.filter(product => 
        !product.return_quantity || 
        product.return_quantity < 1 || 
        product.return_quantity > product.quantity_sold
    )

    if (invalidProducts.length > 0) {
        alert('Please enter valid return quantities for all selected products.')
        return
    }

    processing.value = true

    const formData = {
        selected_products: selectedProducts.value.map(product => ({
            sales_product_id: product.id,
            return_quantity: product.return_quantity
        }))
    }

    router.post(route('return.create-from-sales'), formData, {
        onSuccess: () => {
            selectedProducts.value = []
            activeTab.value = 'returns'
            processing.value = false
        },
        onError: (errors) => {
            console.error('Return creation errors:', errors)
            processing.value = false
        }
    })
}

const viewReturn = (returnItem) => {
    selectedReturn.value = returnItem
    showDetailsModal.value = true
}

const closeDetailsModal = () => {
    showDetailsModal.value = false
    selectedReturn.value = null
}

const updateStatus = (returnItem, status) => {
    router.patch(route('return.update-status', returnItem.id), {
        status: status
    }, {
        preserveScroll: true,
        onSuccess: () => {
            if (showDetailsModal.value && selectedReturn.value?.id === returnItem.id) {
                selectedReturn.value.status = status
                selectedReturn.value.status_text = status === 1 ? 'Approved' : 'Rejected'
                selectedReturn.value.status_color = status === 1 ? 'green' : 'red'
            }
        }
    })
}

// Debounced search
let searchTimeout
watch(() => filters.search, () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        applyFilters()
    }, 300)
})

let salesSearchTimeout
watch(() => salesFilters.sales_search, () => {
    clearTimeout(salesSearchTimeout)
    salesSearchTimeout = setTimeout(() => {
        applySalesFilters()
    }, 300)
})

onMounted(() => {
    console.log('Returns page loaded', { 
        returns: props.returns, 
        salesProducts: props.salesProducts,
        filters: props.filters 
    })
})
</script>
