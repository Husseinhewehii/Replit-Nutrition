<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseViewerController extends Controller
{
    public function index()
    {
        $tables = $this->getTables();
        $tableData = [];

        foreach ($tables as $table) {
            $tableData[$table] = [
                'columns' => $this->getColumns($table),
                'data' => DB::table($table)->get()
            ];
        }

        return view('database-viewer', compact('tableData'));
    }

    private function getTables(): array
    {
        $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
        return array_map(fn($table) => $table->name, $tables);
    }

    private function getColumns(string $table): array
    {
        return Schema::getColumnListing($table);
    }
}
