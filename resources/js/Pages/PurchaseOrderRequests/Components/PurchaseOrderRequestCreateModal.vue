<template>
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black bg-opacity-75">
        <div
            class="relative w-full max-w-6xl p-6 mx-4 my-8 bg-black border-4 border-blue-600 rounded-lg max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-white">Create Purchase Order Request</h2>
                <button @click="closeModal" class="text-white hover:text-gray-300" type="button">
                    ×
                </button>
            </div>

            <form @submit.prevent="submitForm">
                <!-- Order Information -->
                <div class="mb-6 overflow-hidden border-2 border-blue-500 rounded-lg">
                    <div class="px-6 py-3 bg-blue-600">
                        <h5 class="font-bold text-white">Order Information</h5>
                    </div>
                    <div class="p-6 bg-gray-900">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-white">Order Number</label>
                                <input type="text"
                                    class="w-full px-4 py-2 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                                    :value="orderNumber"
                                    readonly>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-white">
                                    Order Date <span class="text-red-500">*</span>
                                </label>
                                <input type="date"
                                    class="w-full px-4 py-2 text-white bg-gray-800 border rounded focus:outline-none focus:border-blue-500"
                                    :class="form.errors.order_date ? 'border-red-500' : 'border-gray-700'"
                                    v-model="form.order_date">
                                <div v-if="form.errors.order_date" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.order_date }}
                                </div>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block mb-2 text-sm font-medium text-white">
                                    User <span class="text-red-500">*</span>
                                </label>
                                <select
                                    class="w-full px-4 py-2 text-white bg-gray-800 border rounded focus:outline-none focus:border-blue-500"
                                    :class="form.errors.user_id ? 'border-red-500' : 'border-gray-700'"
                                    v-model="form.user_id">
                                    <option value="">Select User</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.user_id" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.user_id }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="overflow-hidden border-2 border-blue-500 rounded-lg">
                    <div class="flex items-center justify-between px-6 py-3 bg-blue-600">
                        <h5 class="font-bold text-white">Products</h5>
                        <button type="button" @click="addProduct"
                            class="px-3 py-1 text-white bg-green-600 rounded hover:bg-green-700">
                            + Add Product
                        </button>
                    </div>
                    <div class="p-6 bg-gray-900 overflow-x-auto">
                        <table class="w-full text-white text-sm">
                            <thead class="bg-blue-700">
                                <tr>
                                    <th class="px-4 py-2 text-left">Product</th>
                                    <th class="px-4 py-2 text-left">Unit</th>
                                    <th class="px-4 py-2 text-center">Quantity</th>
                                    <th class="px-4 py-2 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(product, index) in form.products" :key="index" class="border-b border-gray-700 hover:bg-gray-800">
                                    <td class="px-4 py-3">
                                        <div v-if="product.product_id" class="text-white">
                                            {{ product.product_obj ? (product.product_obj.name) : getProductName(product.product_id) }}
                                        </div>
                                        <div v-else>
                                            <select
                                                class="w-full px-3 py-1 text-white bg-gray-800 border rounded focus:outline-none focus:border-blue-500"
                                                :class="form.errors[`products.${index}.product_id`] ? 'border-red-500' : 'border-gray-700'"
                                                v-model="form.products[index].product_id"
                                                @change="onProductChange(index)">
                                                <option value="">Select Product</option>
                                                <option v-for="option in allProducts" :key="option.id" :value="option.id">
                                                    {{ option.name }}
                                                </option>
                                            </select>
                                            <div v-if="form.errors[`products.${index}.product_id`]"
                                                class="mt-1 text-xs text-red-500">
                                                {{ form.errors[`products.${index}.product_id`] }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ product.product_obj ? (product.product_obj.measurement_unit && product.product_obj.measurement_unit.name ? product.product_obj.measurement_unit.name : (product.product_obj.purchaseUnit && product.product_obj.purchaseUnit.name ? product.product_obj.purchaseUnit.name : 'N/A')) : getUnitName(product.product_id) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input 
                                            type="number"
                                            class="w-24 px-3 py-1 text-white bg-gray-800 border border-gray-700 rounded focus:outline-none focus:border-blue-500"
                                            v-model.number="form.products[index].requested_quantity"
                                            min="1" 
                                            step="1">
                                        <div v-if="form.errors[`products.${index}.requested_quantity`]"
                                            class="mt-1 text-xs text-red-500">
                                            {{ form.errors[`products.${index}.requested_quantity`] }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button type="button"
                                            class="px-3 py-1 text-white bg-red-600 rounded hover:bg-red-700 disabled:opacity-50"
                                            @click="removeProduct(index)"
                                            :disabled="form.products.length <= 1">
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-4 mt-6">
                    <button type="button" @click="closeModal"
                        class="px-6 py-2 text-white bg-gray-600 rounded hover:bg-gray-700">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-6 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 disabled:opacity-50"
                        :disabled="form.processing">
                        <span v-if="form.processing">
                            Creating...
                        </span>
                        <span v-else>
                            Create POR
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { watch, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    products: {
        type: Array,
        required: true,
    },
    allProducts: {
        type: Array,
        default: () => []
    },
    measurementUnits: {
        type: Array,
        default: () => []
    },
    users: {
        type: Array,
        required: true,
    },
    orderNumber: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['update:open']);

// Low-stock products provided via props (already filtered server-side)
const lowStockProducts = computed(() => props.products || []);
// Full product list for dropdowns
const allProducts = computed(() => props.allProducts || []);

const form = useForm({
    order_number: '',
    order_date: new Date().toISOString().split('T')[0],
    user_id: '',
    products: [], // Will be populated with all products
});

// Initialize form with all products
watch(
    () => props.products,
    (newProducts) => {
        if (newProducts && newProducts.length > 0) {
            form.products = newProducts.map(p => ({
                product_id: p.id,
                requested_quantity: 1,
                measurement_unit_id: p.measurement_unit_id || p.purchase_unit_id || '',
                product_obj: p,
            }));
        } else {
            form.products = [{ product_id: '', requested_quantity: 1, measurement_unit_id: '', product_obj: null }];
        }
    },
    { immediate: true }
);

// Reset form when modal opens/closes
watch(
    () => props.open,
    (newVal) => {
        if (newVal) {
            form.order_number = props.orderNumber;
            form.order_date = new Date().toISOString().split('T')[0];
            form.user_id = '';
            form.clearErrors();
            
            // Initialize products array
            if (props.products && props.products.length > 0) {
                form.products = props.products.map(p => ({
                    product_id: p.id,
                    requested_quantity: 1,
                    measurement_unit_id: p.measurement_unit_id || p.purchase_unit_id || '',
                    product_obj: p,
                }));
            } else {
                form.products = [{ product_id: '', requested_quantity: 1, measurement_unit_id: '', product_obj: null }];
            }
        }
    }
);

const getProductById = (id) => {
    if (!id && id !== 0) return undefined;
    const pid = parseInt(id);
    // try full product list first
    let p = allProducts.value.find(item => item.id == pid || item.id === id);
    if (p) return p;
    // fallback to low-stock list
    p = lowStockProducts.value.find(item => item.id == pid || item.id === id);
    return p;
};

const getProductName = (id) => getProductById(id)?.name || 'Select product';
const getUnitName = (id) => getProductById(id)?.measurement_unit?.name || 'N/A';

const onProductChange = (index) => {
    const selectedId = form.products[index].product_id;
    const product = getProductById(selectedId);
    form.products[index].measurement_unit_id = product?.measurement_unit_id || product?.purchase_unit_id || '';
    // store the full product object on the row for immediate display
    form.products[index].product_obj = product || null;
};

const addProduct = () => {
    form.products.push({ product_id: '', requested_quantity: 1, measurement_unit_id: '', product_obj: null });
};

const removeProduct = (index) => {
    if (form.products.length <= 1) return;
    form.products.splice(index, 1);
};

const closeModal = () => {
    emit('update:open', false);
    form.reset();
};

const submitForm = () => {
    // Filter products with a selection and quantity > 0
    const productsWithQuantity = form.products.filter(p => p.product_id && p.requested_quantity > 0);
    
    if (productsWithQuantity.length === 0) {
        alert('Please select at least one product and enter a quantity');
        return;
    }

    // Transform data to ensure proper types
    const formattedData = {
        order_number: props.orderNumber,
        order_date: form.order_date,
        user_id: parseInt(form.user_id) || form.user_id,
        products: productsWithQuantity.map(p => ({
            product_id: parseInt(p.product_id) || p.product_id,
            requested_quantity: parseInt(p.requested_quantity) || 0,
            measurement_unit_id: parseInt(p.measurement_unit_id) || p.measurement_unit_id
        }))
    };

    form.transform(() => formattedData).post(route('purchase-order-requests.store'), {
        onSuccess: () => {
            closeModal();
            router.reload();
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
        }
    });
};
</script>

<style scoped>
/* Tailwind CSS is used for styling */
</style>