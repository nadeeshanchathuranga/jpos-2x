<template>
    <Head title="Sync Setting" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-6">
            <div class="max-w-3xl mx-auto">

                <div class="mb-4 flex justify-between items-center">
                    <button
                        type="button"
                        @click="$inertia.visit('/dashboard')"
                        class="px-4 py-2 bg-accent hover:bg-accent text-white rounded-lg font-semibold shadow transition"
                    >
                        Back
                    </button>
                </div>

                <h1 class="text-3xl font-bold text-white mb-6 flex items-center gap-2">
                    <span>🔄</span> Sync Setting
                </h1>

                <div class="bg-gray-800 rounded-lg p-6 shadow-lg text-gray-200">

                    <!-- Enable Sync -->
                    <div class="mb-4">
                        <label class="inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="enableSync"
                                class="form-checkbox h-5 w-5 text-blue-600"
                            />
                            <span class="ml-2 text-lg">Enable Sync Mode</span>
                        </label>
                    </div>

                    <!-- Sync Settings -->
                    <div v-if="enableSync" class="space-y-4">
                        <form @submit.prevent>

                            <!-- Save Button -->
                            <div class="flex justify-end">
                                <button
                                    type="button"
                                    @click="saveCredentials"
                                    class="mt-2 px-6 py-2 bg-green-700 hover:bg-green-800 text-white rounded-lg font-semibold transition shadow-lg"
                                    :disabled="saving"
                                >
                                    <span v-if="saving">Saving...</span>
                                    <span v-else>Save</span>
                                </button>

                                <span v-if="saveSuccess" class="ml-4 text-green-400">
                                    Saved!
                                </span>
                            </div>

                            <!-- Host -->
                            <div>
                                <label class="block mb-1 text-sm">Host</label>
                                <input
                                    type="text"
                                    v-model="host"
                                    class="w-full px-3 py-2 rounded bg-gray-700 text-white"
                                    placeholder="Enter host..."
                                />
                            </div>

                            <!-- DB -->
                            <div>
                                <label class="block mb-1 text-sm">DB</label>
                                <input
                                    type="text"
                                    v-model="db"
                                    class="w-full px-3 py-2 rounded bg-gray-700 text-white"
                                    placeholder="Enter database name..."
                                />
                            </div>

                            <!-- Username -->
                            <div>
                                <label class="block mb-1 text-sm">Username</label>
                                <input
                                    type="text"
                                    v-model="username"
                                    class="w-full px-3 py-2 rounded bg-gray-700 text-white"
                                    placeholder="Enter username..."
                                />
                            </div>

                            <!-- Password -->
                            <div>
                                <label class="block mb-1 text-sm">Password</label>
                                <input
                                    type="password"
                                    v-model="password"
                                    class="w-full px-3 py-2 rounded bg-gray-700 text-white"
                                    placeholder="Enter password..."
                                />
                            </div>

                            <!-- Port -->
                            <div>
                                <label class="block mb-1 text-sm">Port</label>
                                <input
                                    type="text"
                                    v-model="port"
                                    class="w-full px-3 py-2 rounded bg-gray-700 text-white"
                                    placeholder="Enter port..."
                                />
                            </div>

                            <!-- Test Button -->
                            <div class="flex justify-start">
                                <button
                                    type="button"
                                    class="mt-2 px-6 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg font-semibold"
                                    :disabled="testing"
                                    @click="testConnection"
                                >
                                    <span v-if="testing">Testing...</span>
                                    <span v-else>Test</span>
                                </button>

                                <span v-if="testError" class="ml-4 text-red-400">
                                    {{ testError }}
                                </span>
                                <span v-if="testSuccess" class="ml-4 text-green-400">
                                    Connection successful!
                                </span>
                            </div>

                        </form>
                    </div>
                </div>

                <!-- Sync Section and Modules List -->
                <div v-if="enableSync" class="mt-8">
                    <div class="flex justify-end mb-4">
                        <button
                            type="button"
                            :disabled="!testSuccess"
                            :class="[
                                testSuccess
                                    ? 'bg-green-600 hover:bg-green-700 text-white'
                                    : 'bg-gray-600 text-gray-300 cursor-not-allowed opacity-60',
                                'px-6 py-2 rounded-lg font-semibold transition'
                            ]"
                        >
                            Sync
                        </button>
                    </div>

                    <div v-if="testSuccess" class="flex flex-col gap-2">
                        <div
                            v-for="module in modules"
                            :key="module"
                            class="flex items-center gap-2 bg-gray-700 px-3 py-2 rounded"
                        >
                            <span class="inline-block w-6 h-6">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="12" fill="#22c55e" />
                                    <path
                                        d="M7 13.5L10.5 17L17 10.5"
                                        stroke="white"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </span>
                            <span class="text-white">{{ module }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch, defineProps } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import axios from 'axios'

const enableSync = ref(false)
const host = ref('')
const db = ref('')
const username = ref('')
const password = ref('')
const port = ref('')

const saving = ref(false)
const saveSuccess = ref(false)
const saveError = ref('')

const testing = ref(false)
const testSuccess = ref(false)
const testError = ref('')

const props = defineProps({
    secondDb: {
        type: Object,
        default: () => ({})
    }
})

watch(enableSync, (val) => {
    if (val) {
        host.value = props.secondDb.host || ''
        db.value = props.secondDb.database || ''
        username.value = props.secondDb.username || ''
        password.value = props.secondDb.password || ''
        port.value = props.secondDb.port || ''
    }
})

const saveCredentials = async () => {
    saving.value = true
    saveSuccess.value = false

    try {
        const res = await axios.post('/settings/sync/update-second-db', {
            host: host.value,
            port: port.value,
            database: db.value,
            username: username.value,
            password: password.value,
        })

        if (res.data.success) {
            saveSuccess.value = true
        }
    } catch (e) {
        console.error(e)
    } finally {
        saving.value = false
    }
}

const testConnection = async () => {
    testing.value = true
    testSuccess.value = false
    testError.value = ''

    try {
        const res = await axios.post('/settings/sync/test-connection', {
            host: host.value,
            db: db.value,
            username: username.value,
            password: password.value,
            port: port.value,
        })

        testSuccess.value = res.data.success
        if (!res.data.success) testError.value = res.data.message
    } catch (e) {
        testError.value = e.response?.data?.message || e.message
    } finally {
        testing.value = false
    }
}

const modules = [
    'products','brands','categories','types','units','purchase orders',
    'goods received','goods received notes return','expenses','suppliers',
    'product transfer request','product release notes','stock returns',
    'customers','discounts','taxes','sales','product return','sales report',
    'stock report','activity log','expenses report','income report',
    'product release report','stock return report','low stock report',
    'goods received notes report','goods received notes return report',
    'product movement report','users','company info','app setting','sync setting'
]
</script>
