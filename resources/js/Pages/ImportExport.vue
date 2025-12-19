<script setup>
/**
 * ImportExport Component Script
 * 
 * Page for managing import and export of data
 */
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';



// Methods for handling download and upload actions
const handleDownload = (type) => {
    // Download the Excel template from the public/excel-templates directory
    window.location.href = `/excel/${type}.xlsx`;
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
            await fetch(`/excel/upload/${type}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            alert(`${type} data uploaded successfully.`);
        } catch (error) {
            alert('Upload failed.');
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
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="section in sections" :key="section.name" class="border-b hover:bg-gray-700">
                            <td class="px-4 py-2">{{ section.title }}</td>
                            <td class="px-4 py-2">
                                <button 
                                    @click="() => handleDownload(section.name)"
                                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                                    Download
                                </button>
                            </td>
                            <td class="px-4 py-2">
                                <button 
                                    @click="() => handleUpload(section.name)"
                                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                                    Upload
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