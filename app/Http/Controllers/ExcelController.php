<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\GenericImport;

class ExcelController extends Controller
{
    public function upload(Request $request, $module)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        Excel::import(new GenericImport($module), $request->file('file'));

        return response()->json(['success' => true]);
    }
}
