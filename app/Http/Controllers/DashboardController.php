<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $listings = $request->user()->listings();

        $stats = [
            'Active ads' => (clone $listings)->active()->count(),
            'Sold' => (clone $listings)->where('status', 'sold')->count(),
            'Total views' => (int) (clone $listings)->sum('views_count'),
        ];

        $recent = (clone $listings)->withCardData()->latest()->take(4)->get();

        return view('dashboard', compact('stats', 'recent'));
    }
}
