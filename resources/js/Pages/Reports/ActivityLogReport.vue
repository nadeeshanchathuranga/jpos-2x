<template>
    <Head title="Activity Log Report" />

    <AuthenticatedLayout>
        <div
            class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-6"
            :key="startDate + '-' + endDate + '-' + selectedUser + '-' + selectedModule"
        >
            <div class="max-w-7xl mx-auto">
                <!-- Header with Date Filter -->
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
                                <span>📝</span> Activity Log Report
                            </h1>
                        </div>
                        <p class="text-gray-400">Track user activities and actions in the system</p>
                    </div>
                    <!-- Compact Date & User Filter -->
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
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }} (ID: {{ user.id }})</option>
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

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-gradient-to-br from-blue-700 to-blue-900 rounded-xl p-6 shadow-lg flex items-center gap-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-200 text-sm font-medium">Total Activities</p>
                                <p class="text-3xl font-bold text-white mt-2">{{ logs.length }}</p>
                            </div>
                            <div class="text-blue-200 text-4xl">📝</div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-green-700 to-green-900 rounded-xl p-6 shadow-lg flex items-center gap-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-200 text-sm font-medium">Unique Users</p>
                                <p class="text-3xl font-bold text-white mt-2">{{ users.length }}</p>
                            </div>
                            <div class="text-green-200 text-4xl">👤</div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-700 to-purple-900 rounded-xl p-6 shadow-lg flex items-center gap-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-200 text-sm font-medium">Modules</p>
                                <p class="text-3xl font-bold text-white mt-2">{{ modules.length }}</p>
                            </div>
                            <div class="text-purple-200 text-4xl">📦</div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-yellow-700 to-yellow-900 rounded-xl p-6 shadow-lg flex items-center gap-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-yellow-200 text-sm font-medium">Actions</p>
                                <p class="text-3xl font-bold text-white mt-2">{{ logs.filter(l => l.action).length }}</p>
                            </div>
                            <div class="text-yellow-200 text-4xl">⚡</div>
                        </div>
                    </div>
                </div>

                <!-- Activity Log Cards (like All Movements) -->
                <div class="bg-gray-800 rounded-lg p-6 shadow-lg mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-white">Activity Log Details</h3>
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
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        <div
                            v-for="log in logs"
                            :key="log.id"
                            class="rounded-lg p-4 border-l-4 transition bg-gray-900 border-blue-600 text-gray-200"
                        >
                            <div class="flex justify-between items-start gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xl">📝</span>
                                        <span class="font-semibold">{{ log.user_name }}</span>
                                        <span class="text-sm text-blue-400 bg-gray-800 px-2 py-1 rounded">{{ log.module }}</span>
                                    </div>
                                    <div class="text-md text-gray-300 mb-1">Action: <span class="text-green-400 font-semibold">{{ log.action }}</span></div>
                                    <div class="text-sm text-gray-400 mb-1">{{ formatDateTime(log.created_at) }}</div>
                                    <div class="text-sm text-gray-400">{{ log.details }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="logs.length === 0" class="text-center text-gray-400 py-8">
                        No activity logs found for selected filters
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
// Export handlers
const exportPdf = () => {
    window.open(route('reports.export.pdf', { type: 'activity-log', start_date: startDate.value, end_date: endDate.value, user_id: selectedUser.value, module: selectedModule.value }), '_blank');
};

const exportExcel = () => {
    window.open(route('reports.export.excel', { type: 'activity-log', start_date: startDate.value, end_date: endDate.value, user_id: selectedUser.value, module: selectedModule.value }), '_blank');
};
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    logs: Array,
    users: Array,
    modules: Array,
    startDate: String,
    endDate: String,
    selectedUser: [String, Number, null],
    selectedModule: String,
});

const startDate = ref(props.startDate);
const endDate = ref(props.endDate);
const selectedUser = ref(props.selectedUser || '');
const selectedModule = ref(props.selectedModule || '');

const users = props.users;
const modules = props.modules;
const logs = props.logs;

const filterLogs = () => {
    router.get(route('reports.activity-log'), {
        start_date: startDate.value,
        end_date: endDate.value,
        user_id: selectedUser.value,
        module: selectedModule.value,
    }, {
        preserveState: false,
        preserveScroll: false,
    });
};

const resetFilter = () => {
    router.get(route('reports.activity-log'), {}, {
        preserveState: false,
        preserveScroll: false,
    });
};

const formatDateTime = (dateTime) => {
    if (!dateTime) return 'N/A';
    const date = new Date(dateTime);
    return date.toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>
