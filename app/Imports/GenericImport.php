<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\DB;

class GenericImport implements ToCollection
{
    // The module/table name to import data into
    protected $module;

    public function __construct($module)
    {
        $this->module = $module;
    }
     /**
     * This method is called for each uploaded Excel file.
     * It expects the first row to be the header matching the table columns.
     * Each subsequent row is inserted/updated in the database.
     *
     * @param Collection $rows
     */

    public function collection(Collection $rows)
    {
        $table = $this->module;
        $header = $rows->first()->toArray(); // Get column names from first row
        $dataRows = $rows->slice(1); // Skip header row

        foreach ($dataRows as $row) {
            $data = array_combine($header, $row->toArray()); // Map columns to values

              // Insert or update row in the database
            // Uses 'id' as the unique key; change if your table uses a different key
            DB::table($table)->updateOrInsert(
                ['id' => $data['id'] ?? null], // or another unique key
                $data
            );
        }
    }
}
