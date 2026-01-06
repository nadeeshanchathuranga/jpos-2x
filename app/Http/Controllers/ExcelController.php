<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\GenericImport;
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

            // Validate that the uploaded file name matches the expected module
            $uploadedFileName = $request->file('file')->getClientOriginalName();
            
            // Allow multiple filename patterns:
            // 1. Exact match: categories.xlsx or categories.xls
            // 2. Header files: categories_headers_*.xlsx
            // 3. Data files: categories_data_*.xlsx
            $isValidFile = false;
            
            // Check exact match
            if ($uploadedFileName === $module . '.xlsx' || $uploadedFileName === $module . '.xls') {
                $isValidFile = true;
            }
            // Check if it's a header file
            elseif (str_starts_with($uploadedFileName, $module . '_headers_') && 
                    (str_ends_with($uploadedFileName, '.xlsx') || str_ends_with($uploadedFileName, '.xls'))) {
                $isValidFile = true;
            }
            // Check if it's a data file
            elseif (str_starts_with($uploadedFileName, $module . '_data_') && 
                    (str_ends_with($uploadedFileName, '.xlsx') || str_ends_with($uploadedFileName, '.xls'))) {
                $isValidFile = true;
            }
            
            if (!$isValidFile) {
                return response()->json([
                    'success' => false,
                    'message' => "❌ Please upload the correct file for this module."
                ], 422);
            }

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