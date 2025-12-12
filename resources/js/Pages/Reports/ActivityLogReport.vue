<template>
    <Head title="Activity Log Report" />
    <AuthenticatedLayout>
        <template #header>
            <div class="bg-gradient-to-r from-blue-900 to-purple-900 rounded-xl shadow-lg p-6 mb-6">
                <div class="flex items-center gap-3">
                    <button
                        @click="$inertia.visit(route('dashboard'))"
                        class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg font-semibold mr-2"
                    >
                        Back
                    </button>
                    <span class="text-3xl font-bold text-white flex items-center gap-2">
                        <span>📝</span> Activity Log Report
                    </span>
                </div>
                <p class="text-blue-200 mt-2">Track user activities and actions in the system</p>
            </div>
        </template>

        <div class="min-h-screen bg-gradient-to-br from-gray-900 via-black to-gray-800 py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                <!-- Filter -->
                <div class="bg-gray-900 rounded-xl p-6 shadow-lg mb-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Filter Activity Logs</h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Start Date</label>
                            <input v-model="startDate" type="date" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">End Date</label>
                            <input v-model="endDate" type="date" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">User</label>
                            <select v-model="selectedUser" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                                <option value="">All Users</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Module</label>
                            <select v-model="selectedModule" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                                <option value="">All Modules</option>
                                <option v-for="module in modules" :key="module">{{ module }}</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2 mt-4 md:mt-0">
                            <button @click="filterLogs" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">🔍 Filter</button>
                            <button @click="resetFilter" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition">🔄 Reset</button>
                        </div>
                    </div>
                </div>

                <!-- Activity Log Table -->
                <div class="bg-gray-900 rounded-xl p-6 shadow-lg mb-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Activity Log Details</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full bg-black">
                            <thead class="bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase">User</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase">Module</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase">Action</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase">Details</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-750">
                                    <td class="px-4 py-3 text-gray-200">{{ formatDateTime(log.created_at) }}</td>
                                    <td class="px-4 py-3 text-blue-300">{{ log.user_name }}</td>
                                    <td class="px-4 py-3 text-gray-200">{{ log.module }}</td>
                                    <td class="px-4 py-3 text-green-300">{{ log.action }}</td>
                                    <td class="px-4 py-3 text-gray-300">{{ log.details }}</td>
                                </tr>
                            </tbody>
                        </table>
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
        preserveState: true,
        preserveScroll: true,
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
