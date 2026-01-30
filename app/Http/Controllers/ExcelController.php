<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\GenericImport;
use App\Models\CompanyInformation;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelController extends Controller
{
    public function upload(Request $request, $module)
    {
        try {
            // Validate file exists and is proper format
            $validated = $request->validate([
                'file' => 'required|file|mimes:xlsx,xls'
            ]);

            // (Removed file name validation: only header match will be checked)

            // Validate Excel headers match table structure
            $excelHeaders = Excel::toArray(new GenericImport($module), $request->file('file'));

            if (empty($excelHeaders) || empty($excelHeaders[0])) {
                return response()->json([
                    'success' => false,
                    'message' => "❌ The uploaded file is empty or has no headers."
                ], 422);
            }

            // Get headers from the first row of the Excel file
            $fileHeaders = array_map('trim', array_map('strtolower', $excelHeaders[0][0]));

            // Get actual table columns from database
            $tableColumns = DB::select("DESCRIBE {$module}");
            $expectedHeaders = array_map(function($col) {
                return strtolower($col->Field);
            }, $tableColumns);

            // Compare headers (file headers should match table columns)
            $missingHeaders = array_diff($expectedHeaders, $fileHeaders);
            $extraHeaders = array_diff($fileHeaders, $expectedHeaders);

            if (!empty($missingHeaders)) {
                return response()->json([
                    'success' => false,
                    'message' => "❌ Uploaded file columns mismatch. Please use the correct template for this module."
                ], 422);
            }

            if (!empty($extraHeaders)) {
                return response()->json([
                    'success' => false,
                    'message' => "❌ Uploaded file columns mismatch. Please use the correct template for this module."
                ], 422);
            }

            // Create import instance to track insert/update counts
            $import = new GenericImport($module);
            Excel::import($import, $request->file('file'));

            // Build response message with counts
            $insertedCount = $import->getInsertedCount();
            $updatedCount = $import->getUpdatedCount();
            $totalCount = $insertedCount + $updatedCount;

            if ($totalCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => "⚠️ No valid data found in the file. Please ensure the file has data rows (not just headers).",
                    'inserted' => 0,
                    'updated' => 0,
                    'total' => 0
                ], 422);
            }

            $message = "✅ " . ucfirst($module) . " data imported successfully! ";
            $message .= "({$insertedCount} new, {$updatedCount} updated)";

            return response()->json([
                'success' => true,
                'message' => $message,
                'inserted' => $insertedCount,
                'updated' => $updatedCount,
                'total' => $totalCount
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Handle Excel validation errors
            $failures = $e->failures();
            $errorMessage = "❌ Excel validation error: ";

            foreach ($failures as $failure) {
                $errorMessage .= "Row {$failure->row()}: {$failure->errors()[0]} ";
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 422);
        } catch (\Exception $e) {
            // Generic error handling
            $errorMessage = $e->getMessage();

            // Provide more user-friendly error messages
            if (str_contains($errorMessage, 'Undefined array key')) {
                $errorMessage = "❌ Column mismatch error. Please ensure your Excel headers match the table structure.";
            } elseif (str_contains($errorMessage, 'SQLSTATE')) {
                $errorMessage = "❌ Database error: " . preg_replace('/SQLSTATE.*: /', '', $errorMessage);
            } elseif (empty($errorMessage)) {
                $errorMessage = "❌ An unknown error occurred during import.";
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 500);
        }
    }

    public function export($module)
    {
        try {
            // Serve the template file from public/excel folder
            $filename = $module . '.xlsx';
            $filePath = public_path('excel/' . $filename);

            // Check if the template file exists
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => "Template file '{$filename}' not found in excel folder"
                ], 404);
            }

            // Return the file for download
            return response()->download($filePath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Download failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportData($module)
    {
        try {
            // Get all data from the specified table
            $data = DB::table($module)->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => "No data found in {$module} table"
                ], 404);
            }

            // Convert to array for export
            $exportData = $data->toArray();

            // Get currency symbol from CompanyInformation
            $currencySymbol = '';
            try {
                $company = CompanyInformation::first();
                $currencySymbol = $company ? ($company->currency ?? '') : '';
            } catch (\Exception $e) {
                // Currency symbol will remain empty if there's any error
            }

            // Transform data based on module type
            $exportData = $this->transformExportData($exportData, $module, $currencySymbol);

            // Create filename with timestamp
            $filename = $module . '_data_' . date('Y-m-d_His') . '.xlsx';

            // Create Excel export using PhpSpreadsheet
            return response()->streamDownload(function() use ($exportData) {
                // Create a new Spreadsheet object
                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // Add headers (column names from first row)
                if (!empty($exportData)) {
                    $headers = array_keys((array)$exportData[0]);
                    $sheet->fromArray($headers, null, 'A1');

                    // Style header row
                    $headerStyle = [
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']]
                    ];
                    $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray($headerStyle);

                    // Add data rows
                    $rowIndex = 2;
                    foreach ($exportData as $row) {
                        $sheet->fromArray(array_values((array)$row), null, 'A' . $rowIndex);
                        $rowIndex++;
                    }

                    // Auto-size columns
                    foreach (range('A', $sheet->getHighestColumn()) as $col) {
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }
                }

                // Write to output
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $writer->save('php://output');

            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function transformExportData($data, $module, $currencySymbol)
    {
        // Pre-load lookup data for efficient transformation
        $lookupData = $this->prepareLookupData($module);

        // Transform data based on the module
        return array_map(function($row) use ($module, $currencySymbol, $lookupData) {
            $row = (array)$row;

            // (No status transformation: keep raw value 0/1/2)

            // Transform type field: 0 = Percentage (%), 1 = Fixed (currency)
            // Apply to all modules that have a type field (discounts, taxes)
            if (isset($row['type']) && is_numeric($row['type'])) {
                $row['type'] = ((int)$row['type'] === 0)
                    ? 'Percentage (%)'
                    : 'Fixed (' . $currencySymbol . ')';
            }

            // Transform parent_id in categories: show parent category name instead of ID
            if ($module === 'categories' && isset($row['parent_id'])) {
                $row['parent_id'] = $this->resolveForeignKey($row['parent_id'], $lookupData['categories'] ?? []);
            }

            // Transform relational IDs in products
            if ($module === 'products') {
                if (isset($row['category_id']) && is_numeric($row['category_id'])) {
                    $row['category_id'] = $this->resolveForeignKey($row['category_id'], $lookupData['categories'] ?? []);
                }
                if (isset($row['brand_id']) && is_numeric($row['brand_id'])) {
                    $row['brand_id'] = $this->resolveForeignKey($row['brand_id'], $lookupData['brands'] ?? []);
                }
                if (isset($row['type_id']) && is_numeric($row['type_id'])) {
                    $row['type_id'] = $this->resolveForeignKey($row['type_id'], $lookupData['types'] ?? []);
                }
                if (isset($row['discount_id']) && is_numeric($row['discount_id'])) {
                    $row['discount_id'] = $this->resolveForeignKey($row['discount_id'], $lookupData['discounts'] ?? []);
                }
                if (isset($row['tax_id']) && is_numeric($row['tax_id'])) {
                    $row['tax_id'] = $this->resolveForeignKey($row['tax_id'], $lookupData['taxes'] ?? []);
                }
                if (isset($row['purchase_unit_id']) && is_numeric($row['purchase_unit_id'])) {
                    $row['purchase_unit_id'] = $this->resolveForeignKey($row['purchase_unit_id'], $lookupData['measurement_units'] ?? []);
                }
                if (isset($row['sales_unit_id']) && is_numeric($row['sales_unit_id'])) {
                    $row['sales_unit_id'] = $this->resolveForeignKey($row['sales_unit_id'], $lookupData['measurement_units'] ?? []);
                }
                if (isset($row['transfer_unit_id']) && is_numeric($row['transfer_unit_id'])) {
                    $row['transfer_unit_id'] = $this->resolveForeignKey($row['transfer_unit_id'], $lookupData['measurement_units'] ?? []);
                }
            }

            return $row;
        }, $data);
    }

    /**
     * Prepare lookup data (foreign key mappings) for efficient transformations
     */
    private function prepareLookupData($module)
    {
        $lookupData = [];

        // Always load categories for parent_id and category_id resolution
        $lookupData['categories'] = DB::table('categories')
            ->select('id', 'name')
            ->get()
            ->pluck('name', 'id')
            ->toArray();

        // Load product-related lookups if exporting products
        if ($module === 'products') {
            $lookupData['brands'] = DB::table('brands')
                ->select('id', 'name')
                ->get()
                ->pluck('name', 'id')
                ->toArray();

            $lookupData['types'] = DB::table('types')
                ->select('id', 'name')
                ->get()
                ->pluck('name', 'id')
                ->toArray();

            $lookupData['discounts'] = DB::table('discounts')
                ->select('id', 'name')
                ->get()
                ->pluck('name', 'id')
                ->toArray();

            $lookupData['taxes'] = DB::table('taxes')
                ->select('id', 'name')
                ->get()
                ->pluck('name', 'id')
                ->toArray();

            $lookupData['measurement_units'] = DB::table('measurement_units')
                ->select('id', 'symbol')
                ->get()
                ->pluck('symbol', 'id')
                ->toArray();
        }

        return $lookupData;
    }

    /**
     * Resolve foreign key ID to its display name/label
     */
    private function resolveForeignKey($id, $lookupMap)
    {
        // Handle null or empty values
        if (!$id) {
            return '-';
        }

        // Return the mapped value or 'Unknown' if not found
        return $lookupMap[$id] ?? 'Unknown';
    }

    public function exportHeaders($module)
    {
        try {
            // Get table structure (column names) from MySQL
            $columns = DB::select("DESCRIBE {$module}");

            if (empty($columns)) {
                return response()->json([
                    'success' => false,
                    'message' => "Table {$module} not found or has no columns"
                ], 404);
            }

            // Extract column names
            $headers = array_map(function($col) {
                return $col->Field;
            }, $columns);

            // Create filename with timestamp
            $filename = $module . '_headers_' . date('Y-m-d_His') . '.xlsx';

            // Create Excel export with headers only
            return response()->streamDownload(function() use ($headers) {
                // Create a new Spreadsheet object
                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // Add headers (column names)
                $sheet->fromArray($headers, null, 'A1');

                // Style header row
                $headerStyle = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFA500']]
                ];
                $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray($headerStyle);

                // Auto-size columns
                foreach (range('A', $sheet->getHighestColumn()) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Write to output
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $writer->save('php://output');

            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Header export failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
