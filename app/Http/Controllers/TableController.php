<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TableController extends Controller
{
    public function index()
    {
        $tables = Table::orderBy('area')->orderBy('name')->get();

        return Inertia::render('Tables/Index', [
            'tables' => $tables
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'area' => 'required|string|max:255',
        ]);

        Table::create($validated);

        return back()->with('success', 'Thêm bàn mới thành công!');
    }

    public function update(Request $request, $id)
    {
        $table = Table::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'area' => 'required|string|max:255',
        ]);

        $table->update($validated);

        return back()->with('success', 'Cập nhật bàn thành công!');
    }

    public function destroy($id)
    {
        $table = Table::findOrFail($id);

        if ($table->status === 'occupied') {
            return back()->with('error', 'Không thể xóa bàn đang có khách!');
        }

        $table->delete();

        return back()->with('success', 'Xóa bàn thành công!');
    }
}
