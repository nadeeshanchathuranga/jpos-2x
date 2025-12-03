<script setup>
/**
 * Module Settings Component
 * 
 * Allows enabling/disabling system modules
 */
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    moduleSetting: {
        type: Object,
        required: true
    }
});

// Initialize form with current settings
const form = useForm({
    // Core Modules - Supplier/Purchase group
    supplier: props.moduleSetting.supplier ?? true,
    purchase_order: props.moduleSetting.purchase_order ?? true,
    grn: props.moduleSetting.grn ?? true,
    grn_return: props.moduleSetting.grn_return ?? true,
    
    // Stock Transfer group
    stock_transfer_request: props.moduleSetting.stock_transfer_request ?? true,
    stock_transfer_receive: props.moduleSetting.stock_transfer_receive ?? true,
    
    // Brand/Type group
    brand: props.moduleSetting.brand ?? true,
    type: props.moduleSetting.type ?? true,
    
    // Individual modules
    tax: props.moduleSetting.tax ?? true,
    discount: props.moduleSetting.discount ?? true,
    sales_return: props.moduleSetting.sales_return ?? true,
    
    // Optional Modules
    barcode: props.moduleSetting.barcode ?? false,
    email_notification: props.moduleSetting.email_notification ?? false,
});

// Computed properties for grouped checkboxes
const supplierPurchaseGroup = computed({
    get: () => form.supplier && form.purchase_order && form.grn && form.grn_return,
    set: (value) => {
        form.supplier = value;
        form.purchase_order = value;
        form.grn = value;
        form.grn_return = value;
    }
});

const stockTransferGroup = computed({
    get: () => form.stock_transfer_request && form.stock_transfer_receive,
    set: (value) => {
        form.stock_transfer_request = value;
        form.stock_transfer_receive = value;
    }
});

const brandTypeGroup = computed({
    get: () => form.brand && form.type,
    set: (value) => {
        form.brand = value;
        form.type = value;
    }
});

const submit = () => {
    form.post(route('settings.modules.store'), {
        preserveScroll: true,
        onSuccess: () => {
            // Success message handled by backend
        }
    });
};
</script>

<template>
    <AppLayout title="Module Settings">
        <div class="min-h-screen bg-secondary p-6">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">Module Settings</h1>
                <p class="text-gray-400">Enable or disable system modules</p>
            </div>

            <!-- Settings Form -->
            <div>
                <form @submit.prevent="submit" class="space-y-8">
                    
                    <!-- Core Modules Section -->
                    <div class="bg-primary rounded-lg p-6">
                        <h2 class="text-2xl font-bold text-white mb-6 flex items-center">
                            <span class="mr-2">✔️</span> Core Modules
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Supplier, Purchase Order, GRN, GRN Return -->
                            <label class="flex items-center justify-between p-4 bg-secondary rounded-lg cursor-pointer hover:bg-opacity-80 transition">
                                <span class="text-white font-medium">Supplier, Purchase Order, GRN, GRN Return</span>
                                <input 
                                    type="checkbox" 
                                    v-model="supplierPurchaseGroup"
                                    class="w-5 h-5 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500 focus:ring-2"
                                />
                            </label>

                            <!-- Stock Transfer Request, Stock Transfer Receive -->
                            <label class="flex items-center justify-between p-4 bg-secondary rounded-lg cursor-pointer hover:bg-opacity-80 transition">
                                <span class="text-white font-medium">Stock Transfer Request, Stock Transfer Receive</span>
                                <input 
                                    type="checkbox" 
                                    v-model="stockTransferGroup"
                                    class="w-5 h-5 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500 focus:ring-2"
                                />
                            </label>

                            <!-- Brand and Type -->
                            <label class="flex items-center justify-between p-4 bg-secondary rounded-lg cursor-pointer hover:bg-opacity-80 transition">
                                <span class="text-white font-medium">Brand and Type</span>
                                <input 
                                    type="checkbox" 
                                    v-model="brandTypeGroup"
                                    class="w-5 h-5 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500 focus:ring-2"
                                />
                            </label>

                            <!-- Tax -->
                            <label class="flex items-center justify-between p-4 bg-secondary rounded-lg cursor-pointer hover:bg-opacity-80 transition">
                                <span class="text-white font-medium">Tax</span>
                                <input 
                                    type="checkbox" 
                                    v-model="form.tax"
                                    class="w-5 h-5 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500 focus:ring-2"
                                />
                            </label>

                            <!-- Discount -->
                            <label class="flex items-center justify-between p-4 bg-secondary rounded-lg cursor-pointer hover:bg-opacity-80 transition">
                                <span class="text-white font-medium">Discount</span>
                                <input 
                                    type="checkbox" 
                                    v-model="form.discount"
                                    class="w-5 h-5 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500 focus:ring-2"
                                />
                            </label>

                            <!-- Sales Return -->
                            <label class="flex items-center justify-between p-4 bg-secondary rounded-lg cursor-pointer hover:bg-opacity-80 transition">
                                <span class="text-white font-medium">Sales Return</span>
                                <input 
                                    type="checkbox" 
                                    v-model="form.sales_return"
                                    class="w-5 h-5 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500 focus:ring-2"
                                />
                            </label>
                        </div>
                    </div>

                    <!-- Optional Modules Section -->
                    <div class="bg-primary rounded-lg p-6">
                        <h2 class="text-2xl font-bold text-white mb-6 flex items-center">
                            <span class="mr-2">✔️</span> Optional Modules
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Barcode -->
                            <label class="flex items-center justify-between p-4 bg-secondary rounded-lg cursor-pointer hover:bg-opacity-80 transition">
                                <span class="text-white font-medium">Barcode</span>
                                <input 
                                    type="checkbox" 
                                    v-model="form.barcode"
                                    class="w-5 h-5 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500 focus:ring-2"
                                />
                            </label>

                            <!-- Email Notification -->
                            <label class="flex items-center justify-between p-4 bg-secondary rounded-lg cursor-pointer hover:bg-opacity-80 transition">
                                <span class="text-white font-medium">Email Notification</span>
                                <input 
                                    type="checkbox" 
                                    v-model="form.email_notification"
                                    class="w-5 h-5 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500 focus:ring-2"
                                />
                            </label>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="flex justify-end">
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition font-semibold"
                        >
                            {{ form.processing ? 'Saving...' : 'Save Settings' }}
                        </button>
                    </div>

                    <!-- Success Message -->
                    <div v-if="$page.props.flash?.success" class="bg-green-500 text-white p-4 rounded-lg">
                        {{ $page.props.flash.success }}
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Checkbox custom styling */
input[type="checkbox"] {
    cursor: pointer;
}

input[type="checkbox"]:checked {
    background-color: #3b82f6;
    border-color: #3b82f6;
}
</style>
