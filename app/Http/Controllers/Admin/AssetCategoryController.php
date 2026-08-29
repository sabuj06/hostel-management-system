<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssetCategory;
use Illuminate\Http\Request;

class AssetCategoryController extends Controller
{
    public function index()
    {
        $categories = AssetCategory::withCount('assets')
            ->latest()
            ->paginate(10);

        return view('asset-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('asset-categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:asset_categories,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        AssetCategory::create($data);

        return redirect()
            ->route('asset-categories.index')
            ->with('status', 'Asset category created successfully.');
    }

    public function edit(AssetCategory $assetCategory)
    {
        return view('asset-categories.edit', compact('assetCategory'));
    }

    public function update(Request $request, AssetCategory $assetCategory)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:asset_categories,name,' . $assetCategory->id,
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $assetCategory->update($data);

        return redirect()
            ->route('asset-categories.index')
            ->with('status', 'Asset category updated successfully.');
    }

    public function destroy(AssetCategory $assetCategory)
    {
        if ($assetCategory->assets()->exists()) {
            return back()->with(
                'status',
                'This category cannot be deleted because assets are using it.'
            );
        }

        $assetCategory->delete();

        return redirect()
            ->route('asset-categories.index')
            ->with('status', 'Asset category deleted successfully.');
    }
}