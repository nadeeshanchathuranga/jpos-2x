<script setup>
/**
 * ImportExport Component Script
 * 
 * Page for managing import and export of data
 */

import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { logActivity } from '@/composables/useActivityLog';




// Methods for handling structure download (template)
const handleStructureDownload = async (type) => {
    try {
        // Log activity before download, pass module name as details (string)
        await logActivity('download', 'import & export', type);
        // Download template structure
        window.location.href = `/excel/export/${type}`;
    } catch (error) {
        console.error('Download error:', error);
        alert('Download failed. Please try again.');
    }
};

// Methods for handling header-only download (MySQL structure without data)
const handleHeaderDownload = async (type) => {
    try {
        // Log activity before download
        await logActivity('download-header', 'import & export', type);
        // Download only table headers from MySQL
        window.location.href = `/excel/export-headers/${type}`;
    } catch (error) {
        console.error('Header download error:', error);
        alert('Header download failed. Please try again.');
    }
};

// Methods for handling data export (with actual MySQL data)
const handleDataExport = async (type) => {
    try {
        // Log activity before export
        await logActivity('export', 'import & export', type);
        // Export actual data from database
        window.location.href = `/excel/export-data/${type}`;
    } catch (error) {
        console.error('Export error:', error);
        alert('Export failed. Please try again.');
    }
};

// Back button handler
const goBack = () => {
    window.history.back();
};

const handleUpload = (type) => {
    // Create a hidden file input
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.xlsx,.xls';
    input.onchange = async (e) => {
        const file = e.target.files[0];
        if (!file) return;
        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await fetch(`/excel/upload/${type}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const result = await response.json();
            
            if (!response.ok || !result.success) {
                alert(result.message || 'Upload failed: Unknown error');
                return;
            }
            
            // Log activity after successful upload, pass module name as details (string)
            await logActivity('upload', 'import & export', type);
            alert(result.message || `${type} data uploaded successfully!`);
            
            // Optionally reload the page to reflect changes
            // window.location.reload();
        } catch (error) {
            console.error('Upload error:', error);
            alert(`Upload failed: ${error.message || 'Network error'}`);
        }
    };
    input.click();
};
</script>

<template>
    <!-- Page Title for Browser Tab -->
    <Head title="Import & Export" />

    <AppLayout>
        <div class="min-h-screen bg-secondary p-6">
            <!-- Header -->
            <div class="mb-8 flex items-center gap-4">
                <button
                    @click="goBack"
                    class="bg-accent hover:bg-accent text-white font-bold py-2 px-6 rounded-lg shadow transition-all duration-300 ease-in-out"
                >
                    Back
                </button>
                <div>
                    <h1 class="text-4xl font-bold text-white mb-2">Import & Export</h1>
                    <p class="text-white">Manage your data by importing and exporting files</p>
                </div>
            </div>

            <!-- Modern Table for Import & Export -->
            <div class="overflow-x-auto">
                <table class="table-auto w-full bg-black text-white rounded-lg shadow-lg">
                    <thead>
                        <tr class="bg-gray-800 border-b">
                            <th class="px-4 py-2 text-left font-semibold">Modules</th>
                            <th class="px-4 py-2 text-left font-semibold">Download</th>
                            <th class="px-4 py-2 text-left font-semibold">Upload</th>
                            <th class="px-4 py-2 text-left font-semibold">Structure</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="section in sections" :key="section.name" class="border-b hover:bg-gray-700">
                            <td class="px-4 py-2">{{ section.title }}</td>
                            <td class="px-4 py-2">
                                <button 
                                    @click="() => handleDataExport(section.name)"
                                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                                    Download
                                </button>
                            </td>
                            <td class="px-4 py-2">
                                <button 
                                    @click="() => handleUpload(section.name)"
                                    class="bg-purple-500 hover:bg-purple-600 text-white font-bold py-2 px-4 rounded">
                                    Upload
                                </button>
                            </td>
                            <td class="px-4 py-2">
                                <button 
                                    @click="() => handleHeaderDownload(section.name)"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded">
                                    Download
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

<script>
// Define sections for import/export
const sections = [
    { name: 'categories', title: 'Categories' },
    { name: 'types', title: 'Types' },
    { name: 'brands', title: 'Brands' },
    { name: 'suppliers', title: 'Suppliers' },
    { name: 'customers', title: 'Customers' },
    { name: 'discounts', title: 'Discounts' },
    { name: 'taxes', title: 'Taxes' },
    { name: 'products', title: 'Products' },
];
</script>

<style scoped>
/* Modern table styling with black background and curved corners */
table {
    border-collapse: collapse;
    width: 100%;
    border-radius: 12px; /* Add curved corners */
    overflow: hidden; /* Ensure corners are applied */
}
th, td {
    text-align: left;
    padding: 12px;
}
th {
    background-color: #1f2937; /* Dark gray for header */
    font-weight: 600;
}
tr:nth-child(even) {
    background-color: #111827; /* Slightly lighter black for alternating rows */
}
tr:hover {
    background-color: #374151; /* Dark gray for hover effect */
}

/* Smooth transitions */
button {
    @apply transition-all duration-300 ease-in-out;
}
</style>