<template>
    <Head title="Sync Report" />
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header with Date/User Filter -->
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
                    <!-- Date & User Filter -->
                    <div class="flex flex-wrap items-center gap-2 bg-gray-800 rounded-lg p-3 shadow-lg">
                        <input 
                            type="date" 
                            v-model="startDate" 
                            class="px-3 py-1.5 bg-gray-700 text-white text-sm rounded focus:ring-2 focus:ring-blue-500"
                        />
                        <span class="text-gray-400">to</span>
                        <input 
                            type="date" 
                            v-model="endDate" 
                            class="px-3 py-1.5 bg-gray-700 text-white text-sm rounded focus:ring-2 focus:ring-blue-500"
                        />
                        <select
                            v-model.number="selectedUser"
                            class="px-3 py-1.5 bg-gray-700 text-white text-sm rounded focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">All Users</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                        <button 
                            @click="filterLogs" 
                            class="px-4 py-1.5 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded transition"
                        >
                            Apply
                        </button>
                        <button 
                            @click="resetFilter" 
                            class="px-4 py-1.5 bg-gray-700 hover:bg-gray-800 text-white text-sm font-semibold rounded transition"
                        >
                            Reset
                        </button>
                    </div>
                </div>

               

                <!-- Sync Log Table -->
                <div class="bg-gray-800 rounded-lg p-6 shadow-lg mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-white">Sync Log Details</h3>
                        <div class="flex gap-2">
                            <button
                                @click="exportPdf"
                                class="px-4 py-2 bg-red-700 hover:bg-red-800 text-white rounded-lg transition flex items-center gap-2"
                            >
                                Export PDF
                            </button>
                            <button
                                @click="exportExcel"
                                class="px-4 py-2 bg-green-700 hover:bg-green-800 text-white rounded-lg transition flex items-center gap-2"
                            >
                                Export Excel
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">User</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-300">Module</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-300">Action</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-300">Date & Time</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-300">Details</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <tr v-for="log in filteredLogs" :key="log.id" class="text-gray-300 hover:bg-gray-900">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xl">🔄</span>
                                            <span class="font-semibold">{{ log.user_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-sm text-green-400 bg-gray-900 px-3 py-1 rounded-full">{{ log.module }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-green-400 font-semibold">{{ log.action }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm">{{ formatDateTime(log.created_at) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-400">{{ log.details }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="filteredLogs.length === 0" class="text-center text-gray-400 py-8">
                        No sync logs found
                    </div>

                    <!-- Pagination -->
                    <div v-if="filteredLogs.length > 0" class="mt-6 flex justify-between items-center">
                        <div class="text-sm text-gray-400">
                            Showing {{ props.logs.from }} to {{ props.logs.to }} of {{ props.logs.total }} sync logs
                        </div>
                        <div class="flex gap-2">
                            <template v-for="(link, index) in props.logs.links" :key="index">
                                <a
                                    v-if="link.url"
                                    :href="link.url"
                                    @click.prevent="router.visit(link.url, { preserveState: true, preserveScroll: true })"
                                    :class="[
                                        'px-3 py-2 text-sm rounded-lg transition',
                                        link.active 
                                            ? 'bg-blue-600 text-white font-semibold' 
                                            : 'bg-gray-700 text-gray-300 hover:bg-gray-600'
                                    ]"
                                    v-html="link.label"
                                ></a>
                                <span
                                    v-else
                                    :class="[
                                        'px-3 py-2 text-sm rounded-lg',
                                        'bg-gray-800 text-gray-600 cursor-not-allowed'
                                    ]"
                                    v-html="link.label"
                                ></span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    logs: Object,
    users: Array,
    startDate: String,
    endDate: String,
    selectedUser: [String, Number, null],
});

const startDate = ref(props.startDate || '');
const endDate = ref(props.endDate || '');
const selectedUser = ref(props.selectedUser || '');
const users = props.users || [];

// Handle pagination object and filter out null values
const filteredLogs = computed(() => {
    const logData = props.logs?.data || [];
    let logs = Array.isArray(logData) ? logData.filter(log => log !== null) : [];
    return logs;
});

// Compute unique user count from filtered logs
const uniqueUserCount = computed(() => {
    const ids = new Set(filteredLogs.value.map(log => log.user_id).filter(Boolean));
    return ids.size;
});

const filterLogs = () => {
    router.get(route('reports.sync'), {
        start_date: startDate.value,
        end_date: endDate.value,
        user_id: selectedUser.value,
    }, {
        preserveState: false,
        preserveScroll: false,
    });
};

const resetFilter = () => {
    router.get(route('reports.sync'), {}, {
        preserveState: false,
        preserveScroll: false,
    });
};

const formatDateTime = (dateTime) => {
    if (!dateTime) return 'N/A';
    const date = new Date(dateTime);
    return date.toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const logExportActivity = async (type) => {
    try {
        await axios.post('/products/log-activity', {
            action: 'export',
            module: 'sync report',
            details: {
                export_type: type,
                start_date: startDate.value,
                end_date: endDate.value,
                user_id: selectedUser.value,
            },
        });
    } catch (e) {
        // Optionally handle/log error
        console.error('Activity log failed', e);
    }
};

const exportPdf = () => {
    logExportActivity('pdf');
    window.open(route('reports.export.sync.pdf', { start_date: startDate.value, end_date: endDate.value, user_id: selectedUser.value }), '_blank');
};

const exportExcel = () => {
    logExportActivity('excel');
    window.open(route('reports.export.sync.excel', { start_date: startDate.value, end_date: endDate.value, user_id: selectedUser.value }), '_blank');
};
</script>
