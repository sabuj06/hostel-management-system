<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NaturalLanguageSearchService;
use Illuminate\Http\Request;

class SmartSearchController extends Controller
{
    public function index()
    {
        return view('smart-search.index');
    }

    public function search(Request $request, NaturalLanguageSearchService $service)
    {
        $data = $request->validate(['query' => ['required', 'string', 'max:300']]);

        return response()->json($service->search($data['query']));
    }
}