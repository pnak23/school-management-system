<?php

namespace App\Http\Controllers\Chart;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsChartController extends Controller
{
  
    public function index(Request $request)
    {
        // Get filter parameters
        $year = $request->get('year', Carbon::now()->year);
        $month = $request->get('month'); // Optional, 1-12

        // Validate year
        if (!is_numeric($year) || $year < 2000 || $year > 2100) {
            $year = Carbon::now()->year;
        }

        // Validate month if provided
        if ($month !== null && (!is_numeric($month) || $month < 1 || $month > 12)) {
            $month = null;
        }

      
        $lineQuery = DB::table('library_visits')
            ->whereYear('visit_date', $year)
            ->where('is_active', true);

        if ($month !== null) {
            $lineQuery->whereMonth('visit_date', $month);
        }

        $lineData = $lineQuery
            ->selectRaw('MONTH(visit_date) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Initialize all months with 0
        $lineCategories = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $lineSeries = array_fill(0, 12, 0);

        foreach ($lineData as $item) {
            $lineSeries[$item->month - 1] = (int)$item->total;
        }

        $line = [
            'categories' => $lineCategories,
            'series' => [
                [
                    'name' => 'Visits',
                    'data' => $lineSeries
                ]
            ]
        ];

    
        $barQuery = DB::table('library_visits')
            ->where('is_active', true);

        if ($month !== null) {
            $barQuery->whereYear('visit_date', $year)
                ->whereMonth('visit_date', $month);
        } else {
            $barQuery->whereYear('visit_date', $year);
        }

        $barData = $barQuery
            ->selectRaw('session, COUNT(*) as total')
            ->groupBy('session')
            ->get();

        $barCategories = ['morning', 'afternoon', 'evening'];
        $barSeries = [0, 0, 0];

        foreach ($barData as $item) {
            $index = array_search($item->session, $barCategories);
            if ($index !== false) {
                $barSeries[$index] = (int)$item->total;
            }
        }

        // Capitalize first letter for display
        $barCategoriesDisplay = array_map('ucfirst', $barCategories);

        $bar = [
            'categories' => $barCategoriesDisplay,
            'series' => [
                [
                    'name' => 'Visits',
                    'data' => $barSeries
                ]
            ]
        ];

     
        $pieQuery = DB::table('library_visits')
            ->where('is_active', true);

        if ($month !== null) {
            $pieQuery->whereYear('visit_date', $year)
                ->whereMonth('visit_date', $month);
        } else {
            $pieQuery->whereYear('visit_date', $year);
        }

        $userCount = (int)(clone $pieQuery)->whereNotNull('user_id')->count();
        $guestCount = (int)(clone $pieQuery)->whereNotNull('guest_id')->count();

        $pie = [
            'categories' => ['User', 'Guest'],
            'series' => [$userCount, $guestCount]
        ];

  
        $today = Carbon::today();

        $openSessions = (int)DB::table('library_visits')
            ->whereDate('visit_date', $today)
            ->whereNull('check_out_time')
            ->where('is_active', true)
            ->count();

        $checkedOut = (int)DB::table('library_visits')
            ->whereDate('visit_date', $today)
            ->whereNotNull('check_out_time')
            ->where('is_active', true)
            ->count();

        $doughnut = [
            'categories' => ['Open', 'Checked Out'],
            'series' => [$openSessions, $checkedOut]
        ];

    
        $startDate = Carbon::now()->subDays(29);
        $endDate = Carbon::today();

        $areaData = DB::table('library_visits')
            ->whereBetween('visit_date', [$startDate, $endDate])
            ->where('is_active', true)
            ->selectRaw('DATE(visit_date) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Create date range
        $areaCategories = [];
        $areaSeries = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $areaCategories[] = $currentDate->format('M d');
            
            $found = $areaData->firstWhere('date', $dateStr);
            $areaSeries[] = $found ? (int)$found->total : 0;
            
            $currentDate->addDay();
        }

        $area = [
            'categories' => $areaCategories,
            'series' => [
                [
                    'name' => 'Visits',
                    'data' => $areaSeries
                ]
            ]
        ];

  
        $todayTotalVisits = (int)DB::table('library_visits')
            ->whereDate('visit_date', $today)
            ->where('is_active', true)
            ->count();

        $todayOpenSessions = $openSessions; 

        $todayCheckedOut = $checkedOut; 

        $thisMonthVisits = (int)DB::table('library_visits')
            ->whereYear('visit_date', Carbon::now()->year)
            ->whereMonth('visit_date', Carbon::now()->month)
            ->where('is_active', true)
            ->count();

        $kpi = [
            'today_total_visits' => $todayTotalVisits,
            'today_open_sessions' => $todayOpenSessions,
            'today_checked_out' => $todayCheckedOut,
            'this_month_visits' => $thisMonthVisits
        ];

        return view('charts.analytics.index', compact(
            'line',
            'bar',
            'pie',
            'doughnut',
            'area',
            'kpi',
            'year',
            'month'
        ));
    }
}

