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
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls'
            ]);

            // Validate that the uploaded file name matches the expected module
            $uploadedFileName = $request->file('file')->getClientOriginalName();
            $expectedFileName = $module . '.xlsx';
            $expectedFileNameAlt = $module . '.xls';
            
            if ($uploadedFileName !== $expectedFileName && $uploadedFileName !== $expectedFileNameAlt) {
                return response()->json([
                    'success' => false,
                    'message' => "Invalid file. Please upload '{$expectedFileName}' for this module."
                ], 422);
            }

            Excel::import(new GenericImport($module), $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => ucfirst($module) . ' data imported successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
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
}
