<template>
  <div>
    <Head title="Database Backup" />
    
    <AuthenticatedLayout>
      <!-- Header -->
      <div class="bg-secondary py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <h1 class="text-3xl font-bold text-white">Database Backup</h1>
        </div>
      </div>

      <!-- Backup Form Container -->
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 bg-gray-900 border-b border-gray-200">
            
            <!-- Create Backup Section -->
            <div class="mb-8">
              <h2 class="text-xl font-semibold text-white mb-4">Create New Backup</h2>
              <p class="text-gray-600 mb-4">
                Create a backup of your database. The backup will be automatically downloaded to your computer.
              </p>
              
              <div class="flex items-center gap-4">
                <PrimaryButton 
                  @click="createBackup" 
                  :disabled="isCreating || isRestoring"
                  class="flex items-center gap-2"
                >
                  <svg v-if="isCreating" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  {{ isCreating ? 'Creating Backup...' : 'Create & Download Backup' }}
                </PrimaryButton>
              </div>
            </div>

            <!-- Restore Backup Section -->
            <div class="mb-8 border-t pt-8">
              <h2 class="text-xl font-semibold text-gray-900 mb-4">Restore Database</h2>
              <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                  </div>
                  <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Warning</h3>
                    <p class="mt-1 text-sm text-yellow-700">
                      Restoring a backup will <strong>completely replace</strong> your current database. This action cannot be undone. Make sure to create a backup first if you want to preserve your current data.
                    </p>
                  </div>
                </div>
              </div>
              
              <p class="text-gray-600 mb-4">
                Upload a SQL backup file to restore your database. Only .sql files are accepted.
              </p>
              
              <div class="space-y-4">
                <div>
                  <input
                    ref="fileInput"
                    type="file"
                    accept=".sql"
                    @change="handleFileSelect"
                    class="hidden"
                  />
                  
                  <div class="flex items-center gap-4">
                    <button
                      @click="$refs.fileInput.click()"
                      :disabled="isCreating || isRestoring"
                      class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                    >
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                      </svg>
                      Choose SQL File
                    </button>
                    
                    <span v-if="selectedFile" class="text-sm text-gray-600">
                      Selected: {{ selectedFile.name }} ({{ formatFileSize(selectedFile.size) }})
                    </span>
                  </div>
                </div>
                
                <div v-if="selectedFile" class="flex items-center gap-4">
                  <button
                    @click="restoreBackup"
                    :disabled="isCreating || isRestoring || !selectedFile"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                  >
                    <svg v-if="isRestoring" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    {{ isRestoring ? 'Restoring Database...' : 'Restore Database' }}
                  </button>
                  
                  <button
                    @click="clearSelectedFile"
                    :disabled="isCreating || isRestoring"
                    class="text-gray-500 hover:text-gray-700 text-sm"
                  >
                    Cancel
                  </button>
                </div>
              </div>
            </div>

            <!-- Success/Error Messages -->
            <div v-if="message.text" :class="messageClasses" class="p-4 rounded-md">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg v-if="message.type === 'success'" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                  </svg>
                  <svg v-else class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium">{{ message.text }}</p>
                </div>
              </div>
            </div>


          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import axios from 'axios';

// Reactive data
const isCreating = ref(false);
const isRestoring = ref(false);
const selectedFile = ref(null);
const message = ref({ text: '', type: '' });

// Computed properties
const messageClasses = computed(() => ({
  'bg-green-50 text-green-700': message.value.type === 'success',
  'bg-red-50 text-red-700': message.value.type === 'error'
}));

/**
 * Create a new database backup
 */
const createBackup = async () => {
  isCreating.value = true;
  message.value = { text: '', type: '' };
  
  try {
    const response = await axios.post('/backup/create', {}, {
      responseType: 'blob'
    });
    
    // Create download link
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    
    // Get filename from response headers or create default
    const contentDisposition = response.headers['content-disposition'];
    let filename = 'database_backup.sql';
    
    if (contentDisposition) {
      const match = contentDisposition.match(/filename="?(.+)"?/);
      if (match) {
        filename = match[1];
      }
    }
    
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
    
    message.value = { text: 'Backup created and downloaded successfully!', type: 'success' };
    
  } catch (error) {
    console.error('Backup failed:', error);
    message.value = { 
      text: error.response?.data?.error || 'Failed to create backup. Please try again.', 
      type: 'error' 
    };
  } finally {
    isCreating.value = false;
  }
};

/**
 * Handle file selection for restore
 */
const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (file && file.name.endsWith('.sql')) {
    selectedFile.value = file;
    message.value = { text: '', type: '' };
  } else if (file) {
    message.value = { text: 'Please select a valid .sql file.', type: 'error' };
    event.target.value = '';
  }
};

/**
 * Clear selected file
 */
const clearSelectedFile = () => {
  selectedFile.value = null;
  const fileInput = document.querySelector('input[type="file"]');
  if (fileInput) {
    fileInput.value = '';
  }
};

/**
 * Restore database from selected backup file
 */
const restoreBackup = async () => {
  if (!selectedFile.value) {
    message.value = { text: 'Please select a backup file first.', type: 'error' };
    return;
  }
  
  // Show confirmation dialog
  if (!confirm('Are you sure you want to restore the database? This will replace all current data and cannot be undone.')) {
    return;
  }
  
  isRestoring.value = true;
  message.value = { text: '', type: '' };
  
  try {
    const formData = new FormData();
    formData.append('backup_file', selectedFile.value);
    
    const response = await axios.post('/backup/restore', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    message.value = { text: 'Database restored successfully!', type: 'success' };
    clearSelectedFile();
    
  } catch (error) {
    console.error('Restore failed:', error);
    message.value = { 
      text: error.response?.data?.error || 'Failed to restore database. Please try again.', 
      type: 'error' 
    };
  } finally {
    isRestoring.value = false;
  }
};

/**
 * Format file size for display
 */
const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>