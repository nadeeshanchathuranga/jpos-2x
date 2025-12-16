<template>
    <Head title="Sync Report" />
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-6">
            <div class="max-w-7xl mx-auto">
                <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-4 mb-2">
                            <button
                                @click="$inertia.visit(route('dashboard'))"
                                class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg transition flex items-center gap-2"
                            >
                                Back
                            </button>
                            <h1 class="text-3xl font-bold text-white flex items-center gap-2">
                                <span>🔄</span> Sync Report
                            </h1>
                        </div>
                        <p class="text-gray-400">Track all sync operations performed in the system</p>
                    </div>
                </div>
                <div class="bg-gray-800 rounded-lg p-6 shadow-lg mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-white">Sync Log Details</h3>
                    </div>
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        <div
                            v-for="log in filteredLogs"
                            :key="log.id"
                            class="rounded-lg p-4 border-l-4 transition bg-gray-900 border-green-600 text-gray-200"
                        >
                            <div class="flex justify-between items-start gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xl">🔄</span>
                                        <span class="font-semibold">{{ log.user_name }}</span>
                                        <span class="text-sm text-green-400 bg-gray-800 px-2 py-1 rounded">{{ log.module }}</span>
                                    </div>
                                    <div class="text-md text-gray-300 mb-1">Action: <span class="text-green-400 font-semibold">{{ log.action }}</span></div>
                                    <div class="text-sm text-gray-400 mb-1">{{ formatDateTime(log.created_at) }}</div>
                                    <div class="text-sm text-gray-400">{{ log.details }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="filteredLogs.length === 0" class="text-center text-gray-400 py-8">
                        No sync logs found
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    logs: Object,
});

// Handle pagination object and filter out null values
const filteredLogs = computed(() => {
    const logData = props.logs?.data || props.logs || [];
    return Array.isArray(logData) ? logData.filter(log => log !== null) : [];
});

const formatDateTime = (dateTime) => {
    if (!dateTime) return 'N/A';
    const date = new Date(dateTime);
    return date.toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>
