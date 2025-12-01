<template>
    <Head title="New Sale" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-6 flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">💳 New Sale / Bill</h1>
                        <p class="text-gray-400">Create new invoice (F9: Complete | F8: Clear | ESC: Focus Barcode)</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-400">Invoice No.</div>
                        <div class="text-2xl font-bold text-blue-400">{{ invoice_no }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Side - Product Selection & Cart -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Barcode Scanner - Quick Add -->
                        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg p-6 shadow-lg">
                            <div class="flex items-center gap-4">
                                <div class="text-4xl">🔍</div>
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-blue-100 mb-2">Scan Barcode / Quick Add</label>
                                    <div class="flex gap-2">
                                        <input 
                                            ref="barcodeField"
                                            type="text" 
                                            v-model="barcodeInput" 
                                            @keyup.enter="addByBarcode"
                                            placeholder="Scan or enter barcode and press Enter..."
                                            class="flex-1 px-4 py-3 bg-white text-gray-900 rounded-lg focus:ring-2 focus:ring-blue-300 text-lg font-mono"
                                            autofocus
                                        />
                                        <button 
                                            @click="addByBarcode" 
                                            type="button" 
                                            class="px-6 bg-white hover:bg-blue-50 text-blue-700 font-semibold rounded-lg transition"
                                        >
                                            Add
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customer & Date Selection -->
                        <div class="bg-gray-800 rounded-lg p-6 shadow-lg">
                            <h3 class="text-lg font-semibold text-white mb-4">Customer Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Customer</label>
                                    <select v-model="form.customer_id" class="w-full px-4 py-2 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                        
                                        <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                            {{ customer.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Sale Date</label>
                                    <input type="date" v-model="form.sale_date" class="w-full px-4 py-2 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500" />
                                </div>
                            </div>
                        </div>

                        <!-- Product Selection -->
                        <div class="bg-gray-800 rounded-lg p-6 shadow-lg">
                            <h3 class="text-lg font-semibold text-white mb-4">Add Products Manually</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Product</label>
                                    <select v-model="selectedProduct" class="w-full px-4 py-2 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <option :value="null">Select Product</option>
                                        <option v-for="product in products" :key="product.id" :value="product">
                                            {{ product.name }} - Rs. {{ product.retail_price }} (Stock: {{ product.stock_quantity }})
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Quantity</label>
                                    <input type="number" v-model.number="selectedQuantity" min="1" class="w-full px-4 py-2 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500" />
                                </div>
                            </div>
                            <button @click="addToCart" type="button" class="mt-4 w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                                Add to Cart
                            </button>
                        </div>

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
                                            <td class="px-4 py-3 text-right">Rs. {{ item.price.toFixed(2) }}</td>
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
                                            <td class="px-4 py-3 text-right font-semibold text-green-400">Rs. {{ (item.price * item.quantity).toFixed(2) }}</td>
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
                                <div class="flex justify-between text-gray-300 text-lg">
                                    <span>Total Amount:</span>
                                    <span class="font-semibold">Rs. {{ totalAmount.toFixed(2) }}</span>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Discount (Rs.)</label>
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
                                    <div class="flex justify-between text-white text-xl font-bold">
                                        <span>Net Amount:</span>
                                        <span class="text-green-400">Rs. {{ netAmount.toFixed(2) }}</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Payment Type</label>
                                    <select v-model.number="form.payment_type" class="w-full px-4 py-2 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <option :value="0">💵 Cash</option>
                                        <option :value="1">💳 Card</option>
                                        <option :value="2">📝 Credit</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Paid Amount (Rs.)</label>
                                    <input 
                                        type="number" 
                                        v-model.number="form.paid_amount" 
                                        min="0" 
                                        class="w-full px-4 py-2 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500"
                                        placeholder="0.00"
                                    />
                                </div>

                                <div class="pt-4 border-t border-gray-700">
                                    <div class="flex justify-between text-lg font-semibold" :class="{ 'text-red-400': balance > 0, 'text-green-400': balance <= 0 }">
                                        <span>{{ balance > 0 ? 'Balance Due:' : 'Change:' }}</span>
                                        <span>Rs. {{ Math.abs(balance).toFixed(2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button 
                                @click="submitSale" 
                                :disabled="form.items.length === 0 || form.processing" 
                                class="mt-6 w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-600 disabled:cursor-not-allowed text-white font-bold py-4 px-4 rounded-lg transition text-lg shadow-lg"
                            >
                                <span v-if="form.processing">⏳ Processing...</span>
                                <span v-else>✅ Complete Sale (F9)</span>
                            </button>

                            <!-- Quick Actions -->
                            <div class="mt-4 text-xs text-gray-400 text-center">
                                <p>Keyboard Shortcuts:</p>
                                <p>F9: Complete Sale | F8: Clear Cart | ESC: Focus Barcode</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <div v-if="showSuccessModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl transform transition-all">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-black mb-4">
                        <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Payment Successful!</h2>
                    <p class="text-gray-600 mb-6">Order Payment is Successful!</p>
                    <p class="text-sm text-gray-500 mb-6">Invoice: <span class="font-semibold">{{ completedInvoice }}</span></p>
                    
                    <div class="flex gap-3 justify-center">
                        <button 
                            @click="printReceipt" 
                            class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition shadow-lg"
                        >
                            PRINT RECEIPT
                        </button>
                        <button 
                            @click="closeModal" 
                            class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition shadow-lg"
                        >
                            CLOSE
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Print Receipt (Hidden) -->
        <div id="printReceipt" class="hidden">
            <div class="p-8 max-w-sm mx-auto" style="font-family: monospace;">
                <div class="text-center mb-4">
                    <h1 class="text-2xl font-bold">RECEIPT</h1>
                    <p class="text-sm">Invoice: {{ completedInvoice }}</p>
                    <p class="text-sm">Date: {{ completedSaleDate }}</p>
                </div>
                <hr class="my-2 border-black">
                <div class="mb-4">
                    <p class="text-sm"><strong>Customer:</strong> {{ completedCustomer || 'Walk-in' }}</p>
                    <p class="text-sm"><strong>Payment:</strong> {{ getPaymentTypeText(completedPaymentType) }}</p>
                </div>
                <hr class="my-2 border-black">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-black">
                            <th class="text-left py-1">Item</th>
                            <th class="text-center py-1">Qty</th>
                            <th class="text-right py-1">Price</th>
                            <th class="text-right py-1">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in completedItems" :key="index" class="border-b">
                            <td class="py-1">{{ item.product_name }}</td>
                            <td class="text-center py-1">{{ item.quantity }}</td>
                            <td class="text-right py-1">{{ item.price.toFixed(2) }}</td>
                            <td class="text-right py-1">{{ (item.price * item.quantity).toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>
                <hr class="my-2 border-black">
                <div class="text-sm space-y-1">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>Rs. {{ completedTotal }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Discount:</span>
                        <span>Rs. {{ completedDiscount }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-base pt-2 border-t border-black">
                        <span>Net Total:</span>
                        <span>Rs. {{ completedNetAmount }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Paid:</span>
                        <span>Rs. {{ completedPaid }}</span>
                    </div>
                    <div class="flex justify-between font-bold">
                        <span>{{ parseFloat(completedBalance) > 0 ? 'Balance Due:' : 'Change:' }}</span>
                        <span>Rs. {{ Math.abs(parseFloat(completedBalance)).toFixed(2) }}</span>
                    </div>
                </div>
                <hr class="my-4 border-black">
                <p class="text-center text-xs">Thank you for your business!</p>
                <p class="text-center text-xs">Visit again!</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

 
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    invoice_no: String,
    customers: Array,
    products: Array,
});

const form = useForm({
    invoice_no: props.invoice_no,
    customer_id: '',
    sale_date: new Date().toISOString().split('T')[0],
    items: [],
    discount: 0,
    payment_type: 0,
    paid_amount: 0,
});

const selectedProduct = ref(null);
const selectedQuantity = ref(1);
const barcodeInput = ref('');
const barcodeField = ref(null);
const showSuccessModal = ref(false);
const completedInvoice = ref('');
const completedSaleDate = ref('');
const completedCustomer = ref('');
const completedPaymentType = ref(0);
const completedItems = ref([]);
const completedTotal = ref('0.00');
const completedDiscount = ref('0.00');
const completedNetAmount = ref('0.00');
const completedPaid = ref('0.00');
const completedBalance = ref('0.00');

// Calculations
const totalAmount = computed(() => {
    return form.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const netAmount = computed(() => {
    return totalAmount.value - form.discount;
});

const balance = computed(() => {
    return netAmount.value - form.paid_amount;
});

// Get payment type text
const getPaymentTypeText = (type) => {
    const types = { 0: 'Cash', 1: 'Card', 2: 'Credit' };
    return types[type] || 'Cash';
};

// Add product by barcode
const addByBarcode = () => {
    if (!barcodeInput.value.trim()) return;
    
    const product = props.products.find(p => p.barcode === barcodeInput.value.trim());
    
    if (product) {
        const existingIndex = form.items.findIndex(item => item.product_id === product.id);
        
        if (existingIndex !== -1) {
            form.items[existingIndex].quantity += 1;
        } else {
            form.items.push({
                product_id: product.id,
                product_name: product.name,
                price: parseFloat(product.retail_price),
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

// Add product to cart
const addToCart = () => {
    if (!selectedProduct.value || selectedQuantity.value <= 0) return;
    
    const existingIndex = form.items.findIndex(item => item.product_id === selectedProduct.value.id);
    
    if (existingIndex !== -1) {
        form.items[existingIndex].quantity += selectedQuantity.value;
    } else {
        form.items.push({
            product_id: selectedProduct.value.id,
            product_name: selectedProduct.value.name,
            price: parseFloat(selectedProduct.value.retail_price),
            quantity: selectedQuantity.value,
        });
    }
    
    selectedProduct.value = null;
    selectedQuantity.value = 1;
    barcodeField.value?.focus();
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
        form.paid_amount = 0;
        barcodeField.value?.focus();
    }
};

// Submit sale
const submitSale = () => {
    if (form.items.length === 0) {
        alert('Please add items to cart');
        return;
    }

    if (form.paid_amount < netAmount.value && form.payment_type !== 2) {
        if (!confirm('Paid amount is less than net amount. Continue?')) {
            return;
        }
    }

    // Store sale data before submitting
    completedInvoice.value = form.invoice_no;
    completedSaleDate.value = form.sale_date;
    completedCustomer.value = props.customers.find(c => c.id === form.customer_id)?.name || '';
    completedPaymentType.value = form.payment_type;
    completedItems.value = [...form.items];
    completedTotal.value = totalAmount.value.toFixed(2);
    completedDiscount.value = form.discount.toFixed(2);
    completedNetAmount.value = netAmount.value.toFixed(2);
    completedPaid.value = form.paid_amount.toFixed(2);
    completedBalance.value = balance.value.toFixed(2);

    form.post(route('sales.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccessModal.value = true;
        },
        onError: (errors) => {
            console.error('Sale error:', errors);
            let errorMsg = 'Sale failed. Please try again.';
            if (errors.invoice_no) errorMsg = errors.invoice_no[0];
            else if (errors.items) errorMsg = errors.items[0];
            alert(errorMsg);
        }
    });
};

// Print receipt
const printReceipt = () => {
    const printWindow = window.open('', '_blank', 'width=302,height=600');
    
    if (!printWindow) {
        alert('Please allow pop-ups to print receipt');
        return;
    }
    
    const receiptContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Receipt - ${completedInvoice.value}</title>
            <style>
                @page {
                    size: 80mm auto;
                    margin: 0;
                }
                @media print {
                    body {
                        margin: 0;
                        padding: 0;
                    }
                }
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                body {
                    font-family: 'Courier New', Courier, monospace;
                    font-size: 11px;
                    width: 80mm;
                    margin: 0;
                    padding: 3mm 5mm;
                    background: white;
                    color: black;
                    line-height: 1.4;
                }
                .receipt-container {
                    width: 100%;
                    max-width: 80mm;
                }
                .header {
                    text-align: center;
                    margin-bottom: 8px;
                    padding-bottom: 8px;
                    border-bottom: 2px dashed #000;
                }
                .header h1 {
                    font-size: 18px;
                    font-weight: bold;
                    margin-bottom: 4px;
                    text-transform: uppercase;
                }
                .header p {
                    font-size: 10px;
                    margin: 1px 0;
                    line-height: 1.3;
                }
                .info {
                    margin: 8px 0;
                    font-size: 10px;
                }
                .info-row {
                    display: flex;
                    justify-content: space-between;
                    margin: 2px 0;
                    line-height: 1.3;
                }
                .divider {
                    border-bottom: 1px dashed #000;
                    margin: 6px 0;
                    height: 1px;
                }
                .items-table {
                    width: 100%;
                    margin: 8px 0;
                    font-size: 10px;
                    border-collapse: collapse;
                }
                .items-table th {
                    text-align: left;
                    border-bottom: 1px solid #000;
                    padding: 3px 2px;
                    font-weight: bold;
                }
                .items-table td {
                    padding: 3px 2px;
                    border-bottom: 1px dotted #ccc;
                    vertical-align: top;
                }
                .item-name {
                    width: 38%;
                    word-wrap: break-word;
                }
                .item-qty {
                    width: 12%;
                    text-align: center;
                }
                .item-price {
                    width: 25%;
                    text-align: right;
                }
                .item-total {
                    width: 25%;
                    text-align: right;
                }
                .totals {
                    margin-top: 8px;
                    font-size: 10px;
                }
                .total-row {
                    display: flex;
                    justify-content: space-between;
                    margin: 3px 0;
                    line-height: 1.4;
                }
                .total-row.grand {
                    font-size: 13px;
                    font-weight: bold;
                    border-top: 2px solid #000;
                    border-bottom: 2px solid #000;
                    padding: 6px 0;
                    margin: 8px 0;
                }
                .footer {
                    text-align: center;
                    margin-top: 12px;
                    padding-top: 8px;
                    border-top: 2px dashed #000;
                    font-size: 10px;
                }
                .footer p {
                    margin: 2px 0;
                    line-height: 1.3;
                }
            </style>
        </head>
        <body>
            <div class="receipt-container">
                <div class="header">
                    <h1>SALES RECEIPT</h1>
                    <p>Your Company Name</p>
                    <p>Address Line 1, City</p>
                    <p>Tel: +94 XX XXX XXXX</p>
                </div>
                
                <div class="info">
                    <div class="info-row">
                        <span><strong>Invoice:</strong></span>
                        <span>${completedInvoice.value}</span>
                    </div>
                    <div class="info-row">
                        <span><strong>Date:</strong></span>
                        <span>${completedSaleDate.value}</span>
                    </div>
                    <div class="info-row">
                        <span><strong>Customer:</strong></span>
                        <span>${completedCustomer.value || 'Walk-in'}</span>
                    </div>
                    <div class="info-row">
                        <span><strong>Payment:</strong></span>
                        <span>${getPaymentTypeText(completedPaymentType.value)}</span>
                    </div>
                </div>
                
                <div class="divider"></div>
                
                <table class="items-table">
                    <thead>
                        <tr>
                            <th class="item-name">Item</th>
                            <th class="item-qty">Qty</th>
                            <th class="item-price">Price</th>
                            <th class="item-total">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${completedItems.value.map(item => `
                            <tr>
                                <td class="item-name">${item.product_name}</td>
                                <td class="item-qty">${item.quantity}</td>
                                <td class="item-price">${item.price.toFixed(2)}</td>
                                <td class="item-total">${(item.price * item.quantity).toFixed(2)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                
                <div class="divider"></div>
                
                <div class="totals">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>Rs. ${completedTotal.value}</span>
                    </div>
                    <div class="total-row">
                        <span>Discount:</span>
                        <span>Rs. ${completedDiscount.value}</span>
                    </div>
                    <div class="total-row grand">
                        <span>GRAND TOTAL:</span>
                        <span>Rs. ${completedNetAmount.value}</span>
                    </div>
                    <div class="total-row">
                        <span>Paid Amount:</span>
                        <span>Rs. ${completedPaid.value}</span>
                    </div>
                    <div class="total-row" style="font-weight: bold;">
                        <span>${parseFloat(completedBalance.value) > 0 ? 'Balance Due:' : 'Change:'}</span>
                        <span>Rs. ${Math.abs(parseFloat(completedBalance.value)).toFixed(2)}</span>
                    </div>
                </div>
                
                <div class="footer">
                    <p><strong>Thank you for your business!</strong></p>
                    <p>Please visit us again!</p>
                    <p style="margin-top: 6px; font-size: 9px;">Powered by POS System</p>
                </div>
            </div>
            
            <script type="text/javascript">
                let printExecuted = false;
                
                window.onload = function() {
                    setTimeout(function() {
                        if (!printExecuted) {
                            printExecuted = true;
                            window.print();
                        }
                    }, 300);
                }
                
                window.onafterprint = function() {
                    setTimeout(function() {
                        window.close();
                    }, 200);
                }
                
                setTimeout(function() {
                    if (!window.closed) {
                        window.close();
                    }
                }, 5000);
            <\/script>
        </body>
        </html>
    `;
    
    printWindow.document.write(receiptContent);
    printWindow.document.close();
};

// Close modal and reload
const closeModal = () => {
    showSuccessModal.value = false;
    router.visit(route('sales.index'));
};

// Keyboard shortcuts
const handleKeyboard = (event) => {
    // F9 - Complete sale
    if (event.key === 'F9') {
        event.preventDefault();
        submitSale();
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

// Focus barcode input on mount
onMounted(() => {
    barcodeField.value?.focus();
    window.addEventListener('keydown', handleKeyboard);
});
</script>
