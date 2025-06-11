<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Hier kunnen later statistieken worden opgehaald
        $user = Auth::user();

        // Voorbeelddata voor de dashboard statistieken
        $stats = [
            'reports_this_month' => 24,
            'monitored_students' => 156,
            'active_courses' => 12,
            'saved_reports' => 8
        ];

        return view('dashboard', compact('stats'));
    }
}
