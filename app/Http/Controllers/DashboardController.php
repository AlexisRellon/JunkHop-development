<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DashboardStatistic;

class DashboardController extends Controller
{
    public function getStatistics()
    {
        try {
            $latestStatistics = DashboardStatistic::latest('id')->first(); // Fetch only the latest statistics
            return response()->json(['statistics' => $latestStatistics]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching statistics', 'details' => $e->getMessage()], 500);
        }
    }
}
