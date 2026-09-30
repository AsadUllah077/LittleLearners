<?php

namespace App\Http\Controllers;

use App\Models\LearningCategory;

class HomeController extends Controller
{
    public function index()
    {
        $categories = LearningCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('landing', compact('categories'));
    }
}
