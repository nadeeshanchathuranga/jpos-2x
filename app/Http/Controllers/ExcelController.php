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
}
