<template>
    <Head title="Edit Quotation" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Success/Error Messages -->
                <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-900 border border-green-700 rounded-lg text-green-100">
                    ✅ {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4 p-4 bg-red-900 border border-red-700 rounded-lg text-red-100">
                    ❌ {{ $page.props.flash.error }}
                </div>
                <!-- Header -->
                <div class="mb-6 flex justify-between items-center">
                    <div>
                        <div class="flex items-center gap-4 mb-2">
                            <button
                                @click="$inertia.visit(route('quotations.index'))"
                                class="px-4 py-2 bg-accent hover:bg-accent text-white rounded-lg transition flex items-center gap-2"
                            >
                                ← Back
                            </button>
                            <h1 class="text-3xl font-bold text-white">📝 Edit Quotation</h1>
                            <Link
                                :href="route('quotation.edit')"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2"
                            >
                                📋 All Quotations
                            </Link>
                        </div>
                        <p class="text-gray-400">Select a quotation to edit (F9: Update | F8: Clear | ESC: Focus Barcode)</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-400">Selected Quotation</div>
                        <div class="text-2xl font-bold text-blue-400">{{ form.quotation_no || 'None Selected' }}</div>
                    </div>
                </div>

                <!-- Quotation Selector -->
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-lg p-4 shadow-lg mb-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-end">
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-indigo-100 mb-2">📋 Select Quotation to Edit</label>
                            <select
                                v-model="selectedQuotationId"
                                @change="loadQuotation"
                                :disabled="isLoading"
                                class="w-full px-4 py-3 bg-white text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-300 font-semibold disabled:opacity-50"
                            >
                                <option value="">-- Select a Quotation --</option>
                                <option v-for="q in quotations" :key="q.id" :value="q.id">
                                    {{ q.quotation_no }} - {{ q.customer?.name || 'Walk-in' }} - ({{ page.props.currency || 'Rs.' }}) {{ parseFloat(q.total_amount).toFixed(2) }} - {{ q.quotation_date }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <button
                                v-if="selectedQuotationId"
                                @click="loadQuotation"
                                :disabled="isLoading"
                                class="w-full px-4 py-3 bg-white hover:bg-indigo-50 text-indigo-700 font-semibold rounded-lg transition disabled:opacity-50"
                            >
                                <span v-if="isLoading">⏳ Loading...</span>
                                <span v-else>🔄 Reload Data</span>
                            </button>
                        </div>
                    </div>

                    <!-- Loading Indicator -->
                    <div v-if="isLoading" class="mt-4 flex items-center justify-center text-white">
                        <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Loading quotation data...
                    </div>
                </div>

                <!-- Top Row - All Controls -->
                <div v-if="form.quotation_no" class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Barcode Scanner -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg p-4 shadow-lg">
                        <label class="block text-sm font-medium text-blue-100 mb-2">🔍 Scan Barcode</label>
                        <div class="flex gap-2">
                            <input
                                ref="barcodeField"
                                type="text"
                                v-model="barcodeInput"
                                @keyup.enter="addByBarcode"
                                placeholder="Scan barcode..."
                                class="flex-1 px-3 py-2 bg-white text-gray-900 rounded-lg focus:ring-2 focus:ring-blue-300 font-mono"
                            />
                            <button
                                @click="addByBarcode"
                                type="button"
                                class="px-4 bg-white hover:bg-blue-50 text-blue-700 font-semibold rounded-lg transition"
                            >
                                Add
                            </button>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="bg-gray-800 rounded-lg p-4 shadow-lg">
                        <label class="block text-sm font-medium text-gray-300 mb-2">👤 Customer & Date</label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <select v-model="form.customer_id"
                                    class="w-full px-3 py-2 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 text-sm pr-10"
                                    title="Select Customer">
                                    <option value="">Walk-in Customer</option>
                                    <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                        {{ customer.name }}
                                    </option>
                                </select>
                            </div>
                            <input type="date" v-model="form.quotation_date" class="px-3 py-2 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 text-sm" />
                        </div>
                    </div>

                    <!-- Customer Type / Price -->
                    <div class="bg-gray-800 rounded-lg p-4 shadow-lg">
                        <label class="block text-sm font-medium text-gray-300 mb-2">💰 Price Type</label>
                        <div class="flex gap-2">
                            <label class="flex-1 flex items-center justify-center gap-2 cursor-pointer bg-gray-700 px-3 py-2 rounded-lg hover:bg-gray-600 transition text-sm"
                                :class="{ 'ring-2 ring-blue-500 bg-gray-600': form.customer_type === 'retail' }">
                                <input
                                    type="radio"
                                    v-model="form.customer_type"
                                    value="retail"
                                    @change="updateCartPrices"
                                    class="w-4 h-4 text-blue-600"
                                />
                                <span class="text-white font-medium">🏪 Retail</span>
                            </label>
                            <label class="flex-1 flex items-center justify-center gap-2 cursor-pointer bg-gray-700 px-3 py-2 rounded-lg hover:bg-gray-600 transition text-sm"
                                :class="{ 'ring-2 ring-green-500 bg-gray-600': form.customer_type === 'wholesale' }">
                                <input
                                    type="radio"
                                    v-model="form.customer_type"
                                    value="wholesale"
                                    @change="updateCartPrices"
                                    class="w-4 h-4 text-green-600"
                                />
                                <span class="text-white font-medium">🏭 Wholesale</span>
                            </label>
                        </div>
                    </div>

                    <!-- Add Products Manually -->
                    <div class="bg-gray-800 rounded-lg p-4 shadow-lg">
                        <label class="block text-sm font-medium text-gray-300 mb-2">➕ Add Products</label>
                        <button @click="openProductModal" type="button" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                            🔍 Browse Products
                        </button>
                    </div>
                </div>

                <!-- No Quotation Selected Message -->
                <div v-if="!form.quotation_no" class="bg-gray-800 rounded-lg p-12 shadow-lg text-center">
                    <div class="text-6xl mb-4">📋</div>
                    <h3 class="text-2xl font-bold text-white mb-2">Select a Quotation</h3>
                    <p class="text-gray-400">Please select a quotation from the dropdown above to start editing.</p>
                </div>

                <div v-if="form.quotation_no" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Side - Cart -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Cart Items -->
                        <div class="bg-gray-800 rounded-lg p-6 shadow-lg">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-white">Cart Items ({{ form.items.length }})</h3>
                                <button
                                    v-if="form.items.length > 0"
                                    @click="clearCart"
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg transition"
                                >
                                    Clear Cart (F8)
                                </button>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">Product</th>
                                            <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Price</th>
                                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-300">Qty</th>
                                            <th class="px-4 py-3 text-right text-sm font-semibold text-gray-300">Total</th>
                                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-300">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-700">
                                        <tr v-for="(item, index) in form.items" :key="index" class="text-gray-300 hover:bg-gray-750">
                                            <td class="px-4 py-3">{{ item.product_name }}</td>
                                            <td class="px-4 py-3 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <span class="text-sm text-gray-400">({{ page.props.currency || 'Rs.' }})</span>
                                                    <input
                                                        type="number"
                                                        :value="item.price"
                                                        @input="form.items[index].price = parseFloat($event.target.value) || 0"
                                                        step="0.01"
                                                        min="0"
                                                        class="w-24 px-2 py-1 bg-gray-700 text-white rounded font-semibold focus:ring-2 focus:ring-blue-500 text-right"
                                                    />
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <button
                                                        @click="updateQuantity(index, item.quantity - 1)"
                                                        class="w-7 h-7 bg-gray-600 hover:bg-gray-500 rounded text-white font-bold"
                                                    >
                                                        -
                                                    </button>
                                                    <input
                                                        type="number"
                                                        :value="item.quantity"
                                                        @input="updateQuantity(index, parseInt($event.target.value) || 1)"
                                                        class="w-16 text-center font-semibold bg-gray-700 text-white rounded px-2 py-1"
                                                        min="1"
                                                    />
                                                    <button
                                                        @click="updateQuantity(index, item.quantity + 1)"
                                                        class="w-7 h-7 bg-gray-600 hover:bg-gray-500 rounded text-white font-bold"
                                                    >
                                                        +
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-right font-semibold text-green-400">({{ page.props.currency || 'Rs.' }}) {{ (item.price * item.quantity).toFixed(2) }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <button @click="removeItem(index)" class="text-red-500 hover:text-red-400 text-xl">
                                                    🗑️
                                                </button>
                                            </td>
                                        </tr>
                                        <tr v-if="form.items.length === 0">
                                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                                No items in cart - Scan barcode or select product to add
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Bill Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-gray-800 rounded-lg p-6 shadow-lg sticky top-6">
                            <h3 class="text-lg font-semibold text-white mb-6">Bill Summary</h3>

                            <!-- Calculations -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Discount ({{ page.props.currency || 'Rs.' }})</label>
                                    <input
                                        type="number"
                                        v-model.number="form.discount"
                                        min="0"
                                        :max="totalAmount"
                                        class="w-full px-4 py-2 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500"
                                        placeholder="0.00"
                                    />
                                </div>

                                <div class="pt-4 border-t-2 border-gray-700">
                                    <div class="flex justify-between text-gray-300 text-lg mb-4">
                                        <span>Subtotal:</span>
                                        <span class="font-semibold">({{ page.props.currency || 'Rs.' }}) {{ totalAmount.toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between text-gray-300 text-lg mb-4">
                                        <span>Discount:</span>
                                        <span class="font-semibold text-red-400">-({{ page.props.currency || 'Rs.' }}) {{ (Number(form.discount) || 0).toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between text-gray-300 text-lg font-bold border-t border-gray-700 pt-2">
                                        <span>Net Total:</span>
                                        <span class="text-blue-400">({{ page.props.currency || 'Rs.' }}) {{ netAmount.toFixed(2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Buttons Section -->
                            <div class="mt-6 space-y-3">
                                <button
                                    @click="updateQuotation"
                                    :disabled="form.items.length === 0 || form.processing"
                                    class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-600 disabled:cursor-not-allowed text-white font-bold py-4 px-4 rounded-lg transition text-lg shadow-lg"
                                >
                                    <span v-if="form.processing">⏳ Updating...</span>
                                    <span v-else>💾 Update Quotation (F9)</span>
                                </button>
                                <button
                                    @click="printQuotation"
                                    :disabled="form.items.length === 0"
                                    class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-600 disabled:cursor-not-allowed text-white font-bold py-3 px-4 rounded-lg transition shadow-lg"
                                >
                                    🖨️ Print Preview
                                </button>
                                <button
                                    @click="deleteQuotation"
                                    :disabled="form.processing"
                                    class="w-full bg-red-600 hover:bg-red-700 disabled:bg-gray-600 disabled:cursor-not-allowed text-white font-bold py-3 px-4 rounded-lg transition shadow-lg"
                                >
                                    🗑️ Delete Quotation
                                </button>
                            </div>

                            <!-- Quick Actions -->
                            <div class="mt-4 text-xs text-gray-400 text-center">
                                <p>Keyboard Shortcuts:</p>
                                <p>F9: Update Quotation | F8: Clear Cart | ESC: Focus Barcode</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Selection Modal -->
        <div v-if="showProductModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-gray-800 rounded-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden shadow-2xl">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-purple-600 to-purple-700 p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-white">🔍 Browse Products</h2>
                            <p class="text-purple-200 text-sm mt-1">Click products to add to cart • {{ form.items.length }} items in cart</p>
                        </div>
                        <button @click="closeProductModal" class="px-6 py-2 bg-white hover:bg-gray-100 text-purple-700 font-semibold rounded-lg transition">Done</button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="p-6 bg-gray-750 border-b border-gray-700">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Brand</label>
                            <select v-model="productFilters.brand_id" @change="filterProducts" class="w-full px-4 py-2 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="">All Brands</option>
                                <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Category</label>
                            <select v-model="productFilters.category_id" @change="filterProducts" class="w-full px-4 py-2 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="">All Categories</option>


                    <option
  v-for="category in categories"
  :key="category.id"
  :value="category.id"
>
  {{ category.hierarchy_string ? category.hierarchy_string + ' → ' + category.name : category.name }}

  </option>

                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Type</label>
                            <select v-model="productFilters.type_id" @change="filterProducts" class="w-full px-4 py-2 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="">All Types</option>
                                <option v-for="type in types" :key="type.id" :value="type.id">{{ type.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Discount</label>
                            <select v-model="productFilters.discount_id" @change="filterProducts" class="w-full px-4 py-2 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="">All Discounts</option>
                                <option v-for="discount in discounts" :key="discount.id" :value="discount.id">{{ discount.name }}</option>
                            </select>
                        </div>
                    </div>
                    <button @click="clearFilters" class="mt-3 px-4 py-2 bg-gray-600 hover:bg-gray-500 text-white text-sm rounded-lg transition">
                        Clear Filters
                    </button>
                </div>

                <!-- Products Grid -->
                <div class="p-6 overflow-y-auto max-h-[calc(90vh-280px)]">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div
                            v-for="product in paginatedProducts"
                            :key="product.id"
                            class="bg-gray-700 rounded-lg overflow-hidden transition-all relative"
                            :class="{
                                'ring-2 ring-green-500': isProductInCart(product.id)
                            }"
                        >
                            <!-- Added to Cart Badge -->
                            <div v-if="isProductInCart(product.id)" class="absolute top-2 right-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full z-10 flex items-center gap-1">
                                ✓ {{ getProductCartQuantity(product.id) }}
                            </div>
                            <div class="aspect-square bg-gray-600 flex items-center justify-center overflow-hidden">
                                <img
                                    v-if="product.image"
                                    :src="'/storage/' + product.image"
                                    :alt="product.name"
                                    class="w-full h-full object-cover"
                                    @error="$event.target.src='/storage/products/default.png'"
                                />
                                <span v-else class="text-6xl">📦</span>
                            </div>
                            <div class="p-3">
                                <h3 class="text-white font-semibold text-sm mb-2 truncate" :title="product.name">{{ product.name }}</h3>
                                <div class="space-y-1 text-xs text-gray-300">
                                    <div class="flex justify-between">
                                        <span>Retail:</span>
                                        <span class="font-semibold text-green-400">({{ page.props.currency || 'Rs.' }}) {{ parseFloat(product.retail_price).toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Wholesale:</span>
                                        <span class="font-semibold text-blue-400">({{ page.props.currency || 'Rs.' }}) {{ parseFloat(product.wholesale_price).toFixed(2) }}</span>
                                    </div>
                                </div>

                                <!-- Quantity Input -->
                                <div class="mt-3 pt-3 border-t border-gray-600">
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="number"
                                            v-model.number="productQuantities[product.id]"
                                            min="1"
                                            class="flex-1 px-2 py-1 bg-gray-600 text-white text-center rounded text-sm focus:ring-2 focus:ring-purple-500"
                                            @click.stop
                                        />
                                        <button
                                            @click.stop="selectProductFromModal(product)"
                                            class="px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded transition"
                                        >
                                            Add
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- No products found -->
                        <div v-if="filteredProducts.length === 0" class="col-span-4 text-center py-12 text-gray-400">
                            No products found matching your filters.
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="totalPages > 1" class="flex justify-center items-center gap-4 mt-6">
                        <button
                            @click="prevPage"
                            :disabled="currentPage === 1"
                            class="px-4 py-2 bg-gray-600 hover:bg-gray-500 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg transition"
                        >
                            ← Previous
                        </button>
                        <span class="text-gray-300">Page {{ currentPage }} of {{ totalPages }}</span>
                        <button
                            @click="nextPage"
                            :disabled="currentPage === totalPages"
                            class="px-4 py-2 bg-gray-600 hover:bg-gray-500 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg transition"
                        >
                            Next →
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, usePage, Link } from '@inertiajs/vue3';
const page = usePage();
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';


const props = defineProps({
    quotation: Object,
    quotations: Array,
    customers: Array,
    products: Array,
    brands: Array,
    categories: Array,
    types: Array,
    discounts: Array,
    billSetting: Object,
    currencySymbol: Object,
});

// Selected quotation ID for dropdown
const selectedQuotationId = ref(props.quotation?.id || '');
const isLoading = ref(false);
const autoLoadTriggered = ref(false);

const form = useForm({
    quotation_no: props.quotation?.quotation_no || '',
    customer_id: props.quotation?.customer_id || '',
    customer_type: props.quotation?.customer_type || 'retail',
    quotation_date: props.quotation?.quotation_date || new Date().toISOString().split('T')[0],
    items: props.quotation?.items || [],
    discount: props.quotation?.discount || 0,
});

// Load quotation data when selection changes - fetch via API and populate fields
const loadQuotation = async () => {
    if (!selectedQuotationId.value) {
        // Reset form if no quotation selected
        form.quotation_no = '';
        form.customer_id = '';
        form.customer_type = 'retail';
        form.quotation_date = new Date().toISOString().split('T')[0];
        form.items = [];
        form.discount = 0;
        return;
    }

    isLoading.value = true;

    try {
        // Use Inertia router to navigate with data reload
        router.visit(route('quotations.edit', selectedQuotationId.value), {
            preserveState: false,
            preserveScroll: true,
            onSuccess: () => {
                isLoading.value = false;
            },
            onError: () => {
                isLoading.value = false;
                alert('Failed to load quotation data');
            }
        });
    } catch (error) {
        isLoading.value = false;
        console.error('Error loading quotation:', error);
        alert('Failed to load quotation data');
    }
};

// Watch for quotation prop changes (when navigating to a new quotation)
watch(() => props.quotation, (newQuotation) => {
    if (newQuotation) {
        selectedQuotationId.value = newQuotation.id;
        form.quotation_no = newQuotation.quotation_no;
        form.customer_id = newQuotation.customer_id || '';
        form.customer_type = newQuotation.customer_type || 'retail';
        form.quotation_date = newQuotation.quotation_date || new Date().toISOString().split('T')[0];
        form.items = newQuotation.items ? [...newQuotation.items] : [];
        form.discount = newQuotation.discount || 0;
    }
}, { immediate: true, deep: true });

const barcodeInput = ref('');
const barcodeField = ref(null);
const showProductModal = ref(false);

// Product modal filters and pagination
const productFilters = ref({
    brand_id: '',
    category_id: '',
    type_id: '',
    discount_id: '',
});

const filteredProducts = ref([]);
const currentPage = ref(1);
const itemsPerPage = ref(8);
const productQuantities = ref({});

// Calculations
const totalAmount = computed(() => {
    return form.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const netAmount = computed(() => {
    return totalAmount.value - (Number(form.discount) || 0);
});

// Product modal pagination computed properties
const paginatedProducts = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return filteredProducts.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(filteredProducts.value.length / itemsPerPage.value);
});

// Get current price based on customer type
const getCurrentPrice = (product) => {
    return form.customer_type === 'wholesale' ? product.wholesale_price : product.retail_price;
};

// Add product by barcode
const addByBarcode = () => {
    if (!barcodeInput.value.trim()) return;

    const product = props.products.find(p => p.barcode === barcodeInput.value.trim());

    if (product) {
        const existingIndex = form.items.findIndex(item => item.product_id === product.id);
        const price = getCurrentPrice(product);

        if (existingIndex !== -1) {
            form.items[existingIndex].quantity += 1;
        } else {
            form.items.push({
                product_id: product.id,
                product_name: product.name,
                price: parseFloat(price),
                quantity: 1,
            });
        }

        barcodeInput.value = '';
        barcodeField.value?.focus();
    } else {
        alert('Product not found with barcode: ' + barcodeInput.value);
        barcodeInput.value = '';
        barcodeField.value?.focus();
    }
};

// Remove item from cart
const removeItem = (index) => {
    form.items.splice(index, 1);
    barcodeField.value?.focus();
};

// Update quantity in cart
const updateQuantity = (index, newQty) => {
    if (newQty > 0) {
        form.items[index].quantity = newQty;
    } else {
        removeItem(index);
    }
};

// Clear cart
const clearCart = () => {
    if (confirm('Are you sure you want to clear the cart?')) {
        form.items = [];
        form.discount = 0;
        barcodeField.value?.focus();
    }
};

// Product modal methods
const openProductModal = () => {
    showProductModal.value = true;
    filterProducts();
    // Initialize all product quantities to 1
    props.products.forEach(product => {
        if (!productQuantities.value[product.id]) {
            productQuantities.value[product.id] = 1;
        }
    });
};

const closeProductModal = () => {
    showProductModal.value = false;
    barcodeField.value?.focus();
};

const filterProducts = () => {
    let filtered = [...props.products];

    if (productFilters.value.brand_id) {
        filtered = filtered.filter(p => p.brand_id == productFilters.value.brand_id);
    }
    if (productFilters.value.category_id) {
        filtered = filtered.filter(p => p.category_id == productFilters.value.category_id);
    }
    if (productFilters.value.type_id) {
        filtered = filtered.filter(p => p.type_id == productFilters.value.type_id);
    }
    if (productFilters.value.discount_id) {
        filtered = filtered.filter(p => p.discount_id == productFilters.value.discount_id);
    }

    filteredProducts.value = filtered;
    currentPage.value = 1;
};

const clearFilters = () => {
    productFilters.value = {
        brand_id: '',
        category_id: '',
        type_id: '',
        discount_id: '',
    };
    filterProducts();
};

const selectProductFromModal = (product) => {
    const quantity = productQuantities.value[product.id] || 1;

    if (quantity <= 0) {
        alert('Please enter a valid quantity');
        return;
    }

    const existingIndex = form.items.findIndex(item => item.product_id === product.id);
    const price = getCurrentPrice(product);

    if (existingIndex !== -1) {
        form.items[existingIndex].quantity += quantity;
    } else {
        form.items.push({
            product_id: product.id,
            product_name: product.name,
            price: parseFloat(price),
            quantity: quantity,
        });
    }

    productQuantities.value[product.id] = 1;
};

// Check if product is in cart
const isProductInCart = (productId) => {
    return form.items.some(item => item.product_id === productId);
};

// Get product quantity in cart
const getProductCartQuantity = (productId) => {
    const item = form.items.find(item => item.product_id === productId);
    return item ? item.quantity : 0;
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

const updateCartPrices = () => {
    form.items.forEach(item => {
        const product = props.products.find(p => p.id === item.product_id);
        if (product) {
            item.price = parseFloat(getCurrentPrice(product));
        }
    });
};

// Print quotation after update
const printQuotationAfterUpdate = () => {
    try {
        const printWindow = window.open('', '_blank', 'width=302,height=600');
        if (!printWindow) {
            console.warn('Print window blocked. Print unavailable.');
            return;
        }

        const billSetting = props.billSetting || {};
        const rawSize = (billSetting.print_size || '80mm').toString();
        const width = rawSize.includes('58') ? '58mm' : '80mm';
        const customerName = props.customers.find(c => c.id === form.customer_id)?.name || 'Walk-in Customer';
        const currency = page.props.currency || 'Rs.';

        const quotationContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Quotation - ${form.quotation_no}</title>
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { font-family: Arial, sans-serif; font-size: 12px; }
                    .quotation-container { width: ${width}; padding: 10px; margin: 0 auto; }
                    .header { text-align: center; margin-bottom: 10px; }
                    .header h1 { font-size: 16px; font-weight: bold; margin-bottom: 2px; }
                    .document-type { text-align: center; font-size: 14px; font-weight: bold; margin: 8px 0; border: 1px solid #000; padding: 3px; }
                    .company-info { text-align: center; font-size: 10px; margin-bottom: 8px; }
                    .info-section { margin: 8px 0; font-size: 11px; }
                    .info-row { display: flex; justify-content: space-between; margin-bottom: 3px; }
                    .divider { border-bottom: 1px dashed #000; margin: 8px 0; }
                    table { width: 100%; font-size: 11px; border-collapse: collapse; }
                    th, td { padding: 4px 2px; text-align: left; }
                    th { border-bottom: 1px solid #000; font-weight: bold; }
                    .text-right { text-align: right; }
                    .text-center { text-align: center; }
                    .totals { margin-top: 10px; font-size: 11px; }
                    .total-row { display: flex; justify-content: space-between; margin-bottom: 3px; }
                    .total-row.grand { font-size: 14px; font-weight: bold; border-top: 1px solid #000; padding-top: 5px; margin-top: 5px; }
                    .footer { text-align: center; font-size: 10px; margin-top: 15px; padding-top: 10px; border-top: 1px dashed #000; }
                    @media print {
                        body { margin: 0; padding: 0; }
                        .quotation-container { width: 100%; }
                    }
                </style>
            </head>
            <body>
                <div class="quotation-container">
                    <div class="header">
                        <h1>${billSetting.company_name || 'QUOTATION'}</h1>
                    </div>
                    <div class="company-info">
                        ${billSetting.address || ''}<br>
                        ${billSetting.phone ? 'Tel: ' + billSetting.phone : ''}
                    </div>
                    <div class="document-type">QUOTATION (Updated)</div>

                    <div class="info-section">
                        <div class="info-row">
                            <span><strong>No:</strong></span>
                            <span>${form.quotation_no}</span>
                        </div>
                        <div class="info-row">
                            <span><strong>Date:</strong></span>
                            <span>${form.quotation_date}</span>
                        </div>
                        <div class="info-row">
                            <span><strong>Customer:</strong></span>
                            <span>${customerName}</span>
                        </div>
                        <div class="info-row">
                            <span><strong>Type:</strong></span>
                            <span>${form.customer_type === 'wholesale' ? 'Wholesale' : 'Retail'}</span>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <table>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Price</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${form.items.map(item => `
                                <tr>
                                    <td>${item.product_name}</td>
                                    <td class="text-center">${item.quantity}</td>
                                    <td class="text-right">${parseFloat(item.price).toFixed(2)}</td>
                                    <td class="text-right">${(item.price * item.quantity).toFixed(2)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>

                    <div class="divider"></div>

                    <div class="totals">
                        <div class="total-row">
                            <span>Subtotal:</span>
                            <span>${currency} ${totalAmount.value.toFixed(2)}</span>
                        </div>
                        <div class="total-row">
                            <span>Discount:</span>
                            <span>- ${currency} ${(Number(form.discount) || 0).toFixed(2)}</span>
                        </div>
                        <div class="total-row grand">
                            <span>TOTAL:</span>
                            <span>${currency} ${netAmount.value.toFixed(2)}</span>
                        </div>
                    </div>

                    <div class="footer">
                        <p>${billSetting.footer_description || 'Thank you for your business!'}</p>
                        <p style="margin-top: 5px; font-size: 9px;">This is a quotation, not a tax invoice.</p>
                    </div>
                </div>

                <script>
                    window.onload = function() {
                        setTimeout(function() {
                            window.print();
                        }, 300);
                    };
                    window.onafterprint = function() {
                        setTimeout(function() {
                            window.close();
                        }, 200);
                    };
                <\/script>
            </body>
            </html>
        `;

        printWindow.document.write(quotationContent);
        printWindow.document.close();
    } catch (error) {
        console.error('Print error:', error);
    }
};

// Print quotation (without updating)
const printQuotation = () => {
    if (form.items.length === 0) {
        alert('Please add items to cart');
        return;
    }
    printQuotationAfterUpdate();
};

// Delete quotation
const deleteQuotation = () => {
    if (!selectedQuotationId.value) {
        alert('No quotation selected');
        return;
    }

    if (confirm('Are you sure you want to delete this quotation? This action cannot be undone.')) {
        router.delete(route('quotations.destroy', selectedQuotationId.value), {
            onSuccess: () => {
                alert('Quotation deleted successfully!');
                router.visit(route('quotations.index'));
            },
            onError: (errors) => {
                console.error('Delete error:', errors);
                alert('Failed to delete quotation');
            }
        });
    }
};

// Update quotation
const updateQuotation = async () => {
    if (form.items.length === 0) {
        alert('Please add items to cart');
        return;
    }

    if (!selectedQuotationId.value) {
        alert('No quotation selected');
        return;
    }

    form.put(route('quotations.update', selectedQuotationId.value), {
        preserveScroll: true,
        onSuccess: () => {
            // Print quotation after successful update
            printQuotationAfterUpdate();
            alert('Quotation updated successfully!');
        },
        onError: (errors) => {
            console.error('Update error:', errors);
            let errorMsg = 'Update failed. Please try again.';
            if (errors.items) errorMsg = errors.items;
            alert(errorMsg);
        }
    });
};

// Keyboard shortcuts
const handleKeyboard = (event) => {
    // F9 - Update quotation
    if (event.key === 'F9') {
        event.preventDefault();
        updateQuotation();
    }
    // F8 - Clear cart
    if (event.key === 'F8') {
        event.preventDefault();
        clearCart();
    }
    // ESC - Focus barcode
    if (event.key === 'Escape') {
        event.preventDefault();
        barcodeField.value?.focus();
    }
};

// Auto-load quotation if passed from index page
onMounted(() => {
    window.addEventListener('keydown', handleKeyboard);
    filterProducts();

    // Initialize product quantities
    props.products.forEach(product => {
        productQuantities.value[product.id] = 1;
    });

    // Auto-load if quotation is already in props (from route)
    if (props.quotation?.id && !autoLoadTriggered.value) {
        autoLoadTriggered.value = true;
        // Focus barcode field when quotation is loaded
        setTimeout(() => {
            barcodeField.value?.focus();
        }, 300);
    }
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyboard);
});
</script>
