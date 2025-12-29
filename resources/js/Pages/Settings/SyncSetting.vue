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

                            <!-- Save, Test, and Sync Buttons -->
                            <div class="flex justify-between items-center gap-4">
                                <div class="flex items-center gap-4">
                                    <button
                                        type="button"
                                        @click="saveCredentials"
                                        class="mt-2 px-6 py-2 bg-green-700 hover:bg-green-800 text-white rounded-lg font-semibold transition shadow-lg"
                                        :disabled="saving"
                                    >
                                        <span v-if="saving">Saving...</span>
                                        <span v-else>Save</span>
                                    </button>

                                    <button
                                        type="button"
                                        class="mt-2 px-6 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg font-semibold"
                                        :disabled="testing"
                                        @click="testConnection"
                                    >
                                        <span v-if="testing">Testing...</span>
                                        <span v-else>Test</span>
                                    </button>

                                    <span v-if="saveSuccess" class="text-green-400">
                                        Saved!
                                    </span>
                                    <span v-if="testSuccess" class="text-green-400">
                                        Connection successful!
                                    </span>
                                    <span v-if="testError" class="text-red-400">
                                        {{ testError }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-4">
                                    <button
                                        type="button"
                                        @click="syncData"
                                        :disabled="!testSuccess || syncing"
                                        :class="[
                                            testSuccess && !syncing
                                                ? 'bg-green-600 hover:bg-green-700 text-white'
                                                : 'bg-gray-600 text-gray-300 cursor-not-allowed opacity-60',
                                            'mt-2 px-6 py-2 rounded-lg font-semibold transition'
                                        ]"
                                    >
                                        <span v-if="syncing">Syncing...</span>
                                        <span v-else>Sync</span>
                                    </button>
                                    <span v-if="syncSuccess" class="text-green-400">
                                        Sync Completed!
                                    </span>
                                    <span v-if="syncError" class="text-red-400">
                                        {{ syncError }}
                                    </span>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>

                <!-- Sync Section and Modules List -->
                <div v-if="enableSync" class="mt-8">
                    <div class="flex flex-col gap-2">
                        <!-- Header if items exist -->
                        <div v-if="syncItems.length > 0" class="text-gray-400 text-sm mb-2">
                            Synchronizing databases...
                        </div>

                        <div
                            v-for="item in syncItems"
                            :key="item.name"
                            class="flex items-center gap-3 bg-gray-700 px-4 py-3 rounded transition-all duration-300"
                            :class="{'border border-red-500': item.status === 'failed', 'border border-green-500': item.status === 'completed'}"
                        >
                            <!-- Icon Status -->
                            <span class="inline-block w-6 h-6 flex items-center justify-center">
                                <!-- Pending -->
                                <span v-if="item.status === 'pending'" class="text-gray-400">⏳</span>
                                
                                <!-- Syncing -->
                                <svg v-if="item.status === 'syncing'" class="animate-spin h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                
                                <!-- Completed -->
                                <span v-if="item.status === 'completed'" class="text-green-400">✅</span>
                                
                                <!-- Failed -->
                                <span v-if="item.status === 'failed'" class="text-red-400">❌</span>
                            </span>

                            <span class="text-white font-medium">{{ item.name }}</span>
                            
                            <span v-if="item.message" class="text-red-300 text-xs ml-auto">
                                {{ item.message }}
                            </span>
                        </div>

                        <!-- Fallback/Initial Module List (Visual only, if not syncing yet) -->
                        <div v-if="syncItems.length === 0" class="text-gray-500 text-center py-4">
                            Click "Sync" to start synchronizing database tables.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch, defineProps, onMounted } from 'vue'
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

// Load saved state from localStorage on mount
onMounted(() => {
    const savedEnableSync = localStorage.getItem('enableSync')
    const savedTestSuccess = localStorage.getItem('testSuccess')
    
    if (savedEnableSync === 'true') {
        enableSync.value = true
    }
    
    if (savedTestSuccess === 'true') {
        testSuccess.value = true
    }
    
    // Load saved sync items if they exist
    const savedSyncItems = localStorage.getItem('syncItems')
    if (savedSyncItems) {
        try {
            syncItems.value = JSON.parse(savedSyncItems)
        } catch (e) {
            console.error('Failed to parse saved sync items', e)
        }
    }
    
    const savedSyncSuccess = localStorage.getItem('syncSuccess')
    if (savedSyncSuccess === 'true') {
        syncSuccess.value = true
    }
})

watch(enableSync, (val) => {
    // Save to localStorage
    localStorage.setItem('enableSync', val ? 'true' : 'false')
    
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

const syncItems = ref([])
const syncing = ref(false)
const syncSuccess = ref(false)
const syncError = ref('')

const syncData = async () => {
    syncing.value = true
    syncSuccess.value = false
    syncError.value = ''
    syncItems.value = []

    try {
        // 1. Get Modules
        const res = await axios.get('/settings/sync/list')
        if (!res.data.success) throw new Error(res.data.message)
        
        // Initialize all items with 'syncing' status (they'll all sync simultaneously)
        syncItems.value = res.data.modules.map(mod => ({
            name: mod,
            status: 'syncing',
            message: ''
        }))

        // Verify items exist
        if (syncItems.value.length === 0) {
            syncSuccess.value = true
            syncError.value = 'No modules found to sync.'
            return
        }

        // 2. Sync All Modules Simultaneously
        const syncPromises = syncItems.value.map(async (item) => {
            try {
                const syncRes = await axios.post('/settings/sync/module', { module: item.name })
                
                if (syncRes.data.success) {
                    item.status = 'completed'
                } else {
                    item.status = 'failed'
                    item.message = syncRes.data.message
                    syncError.value = 'Some modules failed to sync.'
                }
            } catch (err) {
                item.status = 'failed'
                item.message = err.response?.data?.message || err.message
                syncError.value = 'Error occurred during sync.'
            }
        })

        // Wait for all syncs to complete
        await Promise.all(syncPromises)
        
        if (!syncError.value) {
            syncSuccess.value = true
            // Save sync success state
            localStorage.setItem('syncSuccess', 'true')
        }
        
        // Save sync items to localStorage
        localStorage.setItem('syncItems', JSON.stringify(syncItems.value))

    } catch (e) {
        syncError.value = e.response?.data?.message || e.message || 'Failed to start sync.'
    } finally {
        syncing.value = false
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
        
        // Save test success state to localStorage
        if (res.data.success) {
            localStorage.setItem('testSuccess', 'true')
        } else {
            localStorage.removeItem('testSuccess')
        }
    } catch (e) {
        testError.value = e.response?.data?.message || e.message
        localStorage.removeItem('testSuccess')
    } finally {
        testing.value = false
    }
}

const modules = [
    'products','brands','categories','types','units','purchase orders',
    'goods received','goods received notes return','expenses','suppliers',
    'product transfer request','product release notes','stock returns',
    'customers','discounts','taxes','sales','product return','sales report',
    'sales history','sync report','database backup','bill setting','import & export',
    'stock report','activity log','expenses report','income report',
    'product release report','stock return report','low stock report',
    'goods received notes report','goods received notes return report',
    'product movement report','users','company info','app setting','sync setting'
]
</script>
