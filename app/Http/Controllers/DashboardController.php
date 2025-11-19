<?php

namespace App\Http\Controllers;

use App\Models\MainCompanys;
use App\Models\Payments;
use App\Models\ReferralCommission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\Bills;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $company = MainCompanys::first();
        
        if (!$company) {
            return view('maincompany.maincompany');
        }
        
        // Calculate total balance from bills
        $totalBilled = Bills::sum('amount') ?? 0;
        
        // Calculate total paid from Payments table
        $totalPaymentsPaid = Payments::sum('amount') ?? 0;
        
        // Calculate total paid from ReferralCommission (paid status)
        $totalCommissionsPaid = ReferralCommission::where('status', 'paid')
            ->sum('commission_amount') ?? 0;
        
        // Combined total paid
        $totalPaid = $totalPaymentsPaid + $totalCommissionsPaid;
        
        // Outstanding balance
        $outstandingBalance = $totalBilled - $totalPaid;

        // Today's stats
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        // Include newly created bills and bills updated today (some flows update existing bill rows rather than creating new ones)
        $billedToday = Bills::where(function($q) use ($todayStart, $todayEnd) {
            $q->whereBetween('created_at', [$todayStart, $todayEnd])
              ->orWhereBetween('updated_at', [$todayStart, $todayEnd]);
            })->sum('amount') ?? 0;
                // Only use payments.date when column exists (older schema)
                if (Schema::hasColumn('payments', 'date')) {
                    $paidToday = Payments::where(function($q) use ($todayStart, $todayEnd) {
                        $q->whereBetween('created_at', [$todayStart, $todayEnd])
                          ->orWhereDate('date', Carbon::today());
                    })->sum('amount') ?? 0;
                    $paymentsCountToday = Payments::where(function($q) use ($todayStart, $todayEnd) {
                        $q->whereBetween('created_at', [$todayStart, $todayEnd])
                          ->orWhereDate('date', Carbon::today());
                    })->count();
                } else {
                    $paidToday = Payments::whereBetween('created_at', [$todayStart, $todayEnd])->sum('amount') ?? 0;
                    $paymentsCountToday = Payments::whereBetween('created_at', [$todayStart, $todayEnd])->count();
                }
        // Also include referral commissions that were marked paid today (some commission records are created earlier, and are marked paid later)
        $paidCommissionsToday = ReferralCommission::where('status', 'paid')
            ->whereBetween('updated_at', [$todayStart, $todayEnd])
            ->sum('commission_amount') ?? 0;

        $commissionsCountToday = ReferralCommission::where('status', 'paid')
            ->whereBetween('updated_at', [$todayStart, $todayEnd])
            ->count();

        $paidToday = ($paidToday ?? 0) + $paidCommissionsToday;
        $billsCountToday = Bills::where(function($q) use ($todayStart, $todayEnd) {
            $q->whereBetween('created_at', [$todayStart, $todayEnd])
              ->orWhereBetween('updated_at', [$todayStart, $todayEnd]);
            })->count();

        // Prepare monthly billed and paid totals for last 12 months
        $end = Carbon::now()->endOfMonth();
        $start = (clone $end)->subMonths(11)->startOfMonth();

        // Months labels (YYYY-MM) in ascending order
        $period = [];
        $cursor = (clone $start);
        while ($cursor->lte($end)) {
            $period[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        // Query billed amounts grouped by month
        $billedRows = DB::table('bills')
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('COALESCE(SUM(amount),0) as total'))
            ->whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Query paid amounts from Payments table
        $paidPaymentsRows = DB::table('payments')
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('COALESCE(SUM(amount),0) as total'))
            ->whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();
        
        // Query paid commissions from ReferralCommission table
        // Use updated_at for paid commissions grouping so a commission paid today but created earlier is counted under the month it was paid
        $paidCommissionsRows = DB::table('referral_commissions')
            ->where('status', 'paid')
            ->select(DB::raw("DATE_FORMAT(updated_at, '%Y-%m') as month"), DB::raw('COALESCE(SUM(commission_amount),0) as total'))
            ->whereBetween('updated_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();
        
        // Merge paid amounts (payments + commissions)
        $paidRows = [];
        foreach ($period as $month) {
            $paidRows[$month] = ($paidPaymentsRows[$month] ?? 0) + ($paidCommissionsRows[$month] ?? 0);
        }

        $labels = array_map(function($m){ return Carbon::createFromFormat('Y-m', $m)->format('M Y'); }, $period);
        $billedData = array_map(function($m) use ($billedRows){ return isset($billedRows[$m]) ? (float)$billedRows[$m] : 0; }, $period);
        $paidData = array_map(function($m) use ($paidRows){ return isset($paidRows[$m]) ? (float)$paidRows[$m] : 0; }, $period);

        // --- Daily Stats for Last 30 Days ---
        $dailyEnd = Carbon::today()->endOfDay();
        $dailyStart = Carbon::today()->subDays(29)->startOfDay();

        $dailyPeriod = [];
        $dCursor = (clone $dailyStart);
        while ($dCursor->lte($dailyEnd)) {
            $dailyPeriod[] = $dCursor->format('Y-m-d');
            $dCursor->addDay();
        }

        // Daily Billed
        $dailyBilledRows = DB::table('bills')
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as day"), DB::raw('COALESCE(SUM(amount),0) as total'))
            ->whereBetween('created_at', [$dailyStart, $dailyEnd])
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();

        // Daily Paid (Payments)
        $dailyPaidPaymentsRows = DB::table('payments')
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as day"), DB::raw('COALESCE(SUM(amount),0) as total'))
            ->whereBetween('created_at', [$dailyStart, $dailyEnd])
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();

        // Daily Paid (Commissions)
        // Use updated_at for daily paid commission grouping to reflect when a commission was paid, not when it was created
        $dailyPaidCommissionsRows = DB::table('referral_commissions')
            ->where('status', 'paid')
            ->select(DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d') as day"), DB::raw('COALESCE(SUM(commission_amount),0) as total'))
            ->whereBetween('updated_at', [$dailyStart, $dailyEnd])
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $dailyLabels = array_map(function($d){ return Carbon::createFromFormat('Y-m-d', $d)->format('M d'); }, $dailyPeriod);
        $dailyBilledData = array_map(function($d) use ($dailyBilledRows){ return isset($dailyBilledRows[$d]) ? (float)$dailyBilledRows[$d] : 0; }, $dailyPeriod);
        
        $dailyPaidData = [];
        foreach ($dailyPeriod as $day) {
            $dailyPaidData[] = ($dailyPaidPaymentsRows[$day] ?? 0) + ($dailyPaidCommissionsRows[$day] ?? 0);
        }

        return view('dashboard', [
            'company' => $company,
            'totalBalance' => $outstandingBalance,
            'totalBilled' => $totalBilled,
            'totalPaid' => $totalPaid,
            'billedToday' => $billedToday,
            'paidToday' => $paidToday,
            'billsCountToday' => $billsCountToday,
            'paymentsCountToday' => $paymentsCountToday,
            'commissionsCountToday' => $commissionsCountToday ?? 0,
            'chartLabels' => $labels,
            'chartBilled' => $billedData,
            'chartPaid' => $paidData,
            'dailyLabels' => $dailyLabels,
            'dailyBilled' => $dailyBilledData,
            'dailyPaid' => $dailyPaidData,
        ]);
    }

    public function exportCsv(Request $request)
    {
        $type = $request->query('type', 'monthly');
        $filename = $type . '_data_' . date('Y-m-d') . '.csv';

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($type) {
            $file = fopen('php://output', 'w');

            if ($type === 'monthly') {
                fputcsv($file, ['Month', 'Billed Amount', 'Paid Amount']);

                $end = Carbon::now()->endOfMonth();
                $start = (clone $end)->subMonths(11)->startOfMonth();
                
                // Re-query logic for simplicity or refactor to shared service. 
                // For now, duplicating query logic for export to ensure fresh data.
                
                $period = [];
                $cursor = (clone $start);
                while ($cursor->lte($end)) {
                    $period[] = $cursor->format('Y-m');
                    $cursor->addMonth();
                }

                $billedRows = DB::table('bills')
                    ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('COALESCE(SUM(amount),0) as total'))
                    ->whereBetween('created_at', [$start, $end])
                    ->groupBy('month')
                    ->pluck('total', 'month')->toArray();

                $paidPaymentsRows = DB::table('payments')
                    ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('COALESCE(SUM(amount),0) as total'))
                    ->whereBetween('created_at', [$start, $end])
                    ->groupBy('month')
                    ->pluck('total', 'month')->toArray();

                $paidCommissionsRows = DB::table('referral_commissions')
                    ->where('status', 'paid')
                    ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('COALESCE(SUM(commission_amount),0) as total'))
                    ->whereBetween('created_at', [$start, $end])
                    ->groupBy('month')
                    ->pluck('total', 'month')->toArray();

                foreach ($period as $month) {
                    $billed = $billedRows[$month] ?? 0;
                    $paid = ($paidPaymentsRows[$month] ?? 0) + ($paidCommissionsRows[$month] ?? 0);
                    fputcsv($file, [$month, $billed, $paid]);
                }

            } else {
                // Daily
                fputcsv($file, ['Date', 'Billed Amount', 'Paid Amount']);

                $dailyEnd = Carbon::today()->endOfDay();
                $dailyStart = Carbon::today()->subDays(29)->startOfDay();

                $dailyPeriod = [];
                $dCursor = (clone $dailyStart);
                while ($dCursor->lte($dailyEnd)) {
                    $dailyPeriod[] = $dCursor->format('Y-m-d');
                    $dCursor->addDay();
                }

                $dailyBilledRows = DB::table('bills')
                    ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as day"), DB::raw('COALESCE(SUM(amount),0) as total'))
                    ->whereBetween('created_at', [$dailyStart, $dailyEnd])
                    ->groupBy('day')
                    ->pluck('total', 'day')->toArray();

                $dailyPaidPaymentsRows = DB::table('payments')
                    ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as day"), DB::raw('COALESCE(SUM(amount),0) as total'))
                    ->whereBetween('created_at', [$dailyStart, $dailyEnd])
                    ->groupBy('day')
                    ->pluck('total', 'day')->toArray();

                $dailyPaidCommissionsRows = DB::table('referral_commissions')
                    ->where('status', 'paid')
                    ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as day"), DB::raw('COALESCE(SUM(commission_amount),0) as total'))
                    ->whereBetween('created_at', [$dailyStart, $dailyEnd])
                    ->groupBy('day')
                    ->pluck('total', 'day')->toArray();

                foreach ($dailyPeriod as $day) {
                    $billed = $dailyBilledRows[$day] ?? 0;
                    $paid = ($dailyPaidPaymentsRows[$day] ?? 0) + ($dailyPaidCommissionsRows[$day] ?? 0);
                    fputcsv($file, [$day, $billed, $paid]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $lab = new MainCompanys;
        $lab->id = 1;
        $lab->lab_name = $request->lab_name;
        $lab->lab_address = $request->lab_address;
        $lab->lab_phone = $request->lab_phone;
        $lab->lab_email = $request->lab_email;
        $lab->balance = 0;
        if ($request->hasFile('lab_image')) {
            $file = $request->file('lab_image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('/assets/HMS/lablogo/'), $filename);
            $lab->lab_image = $filename;
        }
        $lab->save();
        return response()->json(['success' => 'Data Add successfully.']);
    }

    public function details(){
        return view('maincompany.details');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
