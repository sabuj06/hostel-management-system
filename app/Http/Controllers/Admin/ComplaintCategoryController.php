<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComplaintCategory;
use Illuminate\Http\Request;
use App\ActivityLogger;

class ComplaintCategoryController extends Controller
{
    public function index()
    {
        $categories = ComplaintCategory::withCount('complaints')
            ->latest()
            ->get();

        return view('complaints.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:complaint_categories,name'
            ]
        ]);

        $category = ComplaintCategory::create($data);

        ActivityLogger::log(
            action: 'created',
            module: 'complaint_categories',
            description: "Complaint category created: {$category->name}",
            subject: $category,
            newValues: $category->toArray()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'category' => $category
            ]);
        }

        return back()->with('status', 'Category added successfully.');
    }

    public function destroy(ComplaintCategory $complaintCategory)
    {
        $oldValues = $complaintCategory->toArray();
        $categoryName = $complaintCategory->name;

        $complaintCategory->delete();

        ActivityLogger::log(
            action: 'deleted',
            module: 'complaint_categories',
            description: "Complaint category deleted: {$categoryName}",
            subject: $complaintCategory,
            oldValues: $oldValues
        );

        return back()->with('status', 'Category deleted successfully.');
    }
}