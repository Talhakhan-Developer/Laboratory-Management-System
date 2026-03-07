<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bills;
use App\Models\Expense;
use App\Models\Employees;
use App\Models\SalaryPayment;
use App\Models\DoctorCommission;
use App\Models\ReferralCommission;
use App\Models\Payments;
use App\Models\Patients;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialStatisticsController extends Controller
{
    /**
     * Financial Dashboard - Main overview page
     */
    public function dashboard(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();

        // Revenue stats
        // DB total_price/amount already has discount subtracted, so it IS the net revenue
        $netRevenue = Bills::whereBetween('created_at', [$startDate, $endDate])->sum(DB::raw('COALESCE(total_price, amount)')) ?: 0;
        $totalDiscount = Bills::whereBetween('created_at', [$startDate, $endDate])->sum('discount') ?: 0;
        $grossRevenue = $netRevenue + $totalDiscount; // Reconstruct pre-discount amount
        $totalCollected = Bills::whereBetween('created_at', [$startDate, $endDate])->sum('paid_amount') ?: 0;
        $totalDue = $netRevenue - $totalCollected; // Outstanding = what's owed minus what's paid
        $billCount = Bills::whereBetween('created_at', [$startDate, $endDate])->count();

        // Keep $totalRevenue for backward compat in view
        $totalRevenue = $grossRevenue;

        // Expense stats
        $totalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount') ?: 0;
        $expensesByCategory = Expense::select('category', DB::raw('SUM(amount) as total'))
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->groupBy('category')
            ->get();

        // Salary stats
        $salaryStats = SalaryPayment::getMonthlyStats($month);

        // Doctor commissions
        $commissionStats = DoctorCommission::getMonthlyStats($month);

        // Referral commissions
        $referralCommissionTotal = ReferralCommission::whereBetween('created_at', [$startDate, $endDate])->sum('commission_amount') ?: 0;
        $referralCommissionPending = ReferralCommission::whereBetween('created_at', [$startDate, $endDate])->where('status', 'pending')->sum('commission_amount') ?: 0;

        // Net profit calculation
        $totalOutgoing = $totalExpenses + ($salaryStats['total_paid'] ?? 0) + ($commissionStats['paid'] ?? 0) + $referralCommissionTotal;
        $netProfit = $totalCollected - $totalOutgoing;

        // Monthly trend data (last 6 months)
        $monthlyTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $trendMonth = now()->subMonths($i);
            $tStart = $trendMonth->copy()->startOfMonth();
            $tEnd = $trendMonth->copy()->endOfMonth();
            
            $monthlyTrends[] = [
                'month' => $trendMonth->format('M Y'),
                'revenue' => Bills::whereBetween('created_at', [$tStart, $tEnd])->sum(DB::raw('COALESCE(total_price, amount)')) ?: 0,
                'collected' => Bills::whereBetween('created_at', [$tStart, $tEnd])->sum('paid_amount') ?: 0,
                'expenses' => Expense::whereBetween('expense_date', [$tStart, $tEnd])->sum('amount') ?: 0,
            ];
        }

        // Payment method breakdown
        $paymentMethods = Bills::select('payment_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(paid_amount) as total'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('payment_type')
            ->groupBy('payment_type')
            ->get();

        // Daily revenue for chart
        $dailyRevenue = Bills::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(COALESCE(total_price, amount)) as revenue'),
                DB::raw('SUM(paid_amount) as collected')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return view('financial.dashboard', compact(
            'month', 'grossRevenue', 'netRevenue', 'totalRevenue', 'totalCollected', 'totalDue', 'totalDiscount',
            'billCount', 'totalExpenses', 'expensesByCategory', 'salaryStats',
            'commissionStats', 'referralCommissionTotal', 'referralCommissionPending',
            'totalOutgoing', 'netProfit', 'monthlyTrends', 'paymentMethods', 'dailyRevenue'
        ));
    }

    /**
     * Revenue Analysis page
     */
    public function revenueAnalysis(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $day = $request->get('day', ''); // optional day filter (Y-m-d)
        $filterMode = $day ? 'day' : 'month';

        if ($day) {
            $startDate = Carbon::parse($day)->startOfDay();
            $endDate = Carbon::parse($day)->endOfDay();
        } else {
            $startDate = Carbon::parse($month . '-01')->startOfMonth();
            $endDate = Carbon::parse($month . '-01')->endOfMonth();
        }

        $bills = Bills::with('patient')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        // Revenue by status
        $revenueByStatus = Bills::select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(COALESCE(total_price, amount)) as total'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('status')
            ->get();

        // Daily revenue
        $dailyRevenue = Bills::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(COALESCE(total_price, amount)) as revenue'),
                DB::raw('SUM(paid_amount) as collected'),
                DB::raw('SUM(COALESCE(total_price, amount) - paid_amount) as due'),
                DB::raw('COUNT(*) as bill_count')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Top tests by revenue
        $testRevenue = [];
        $billsWithTests = Bills::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('all_test')
            ->get();
        foreach ($billsWithTests as $bill) {
            $tests = json_decode($bill->all_test, true);
            if (is_array($tests)) {
                foreach ($tests as $test) {
                    $testName = $test['test_name'] ?? $test['name'] ?? 'Unknown';
                    $testPrice = $test['price'] ?? $test['test_price'] ?? 0;
                    if (!isset($testRevenue[$testName])) {
                        $testRevenue[$testName] = ['name' => $testName, 'total' => 0, 'count' => 0];
                    }
                    $testRevenue[$testName]['total'] += floatval($testPrice);
                    $testRevenue[$testName]['count']++;
                }
            }
        }
        usort($testRevenue, fn($a, $b) => $b['total'] <=> $a['total']);
        $testRevenue = array_slice($testRevenue, 0, 15);

        // DB total_price/amount already has discount subtracted, so it IS the net revenue
        $netRevenue = Bills::whereBetween('created_at', [$startDate, $endDate])->sum(DB::raw('COALESCE(total_price, amount)')) ?: 0;
        $totalDiscount = Bills::whereBetween('created_at', [$startDate, $endDate])->sum('discount') ?: 0;
        $grossRevenue = $netRevenue + $totalDiscount; // Reconstruct pre-discount amount
        $totalCollected = Bills::whereBetween('created_at', [$startDate, $endDate])->sum('paid_amount') ?: 0;
        $totalDue = $netRevenue - $totalCollected;
        $totalRevenue = $grossRevenue; // alias

        return view('financial.revenue', compact(
            'month', 'day', 'filterMode', 'bills', 'revenueByStatus', 'dailyRevenue', 'testRevenue',
            'grossRevenue', 'netRevenue', 'totalRevenue', 'totalDiscount', 'totalCollected', 'totalDue'
        ));
    }

    /**
     * Expense Analysis page
     */
    public function expenseAnalysis(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();

        $expenses = Expense::with('user')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'desc')
            ->get();

        $byCategory = Expense::select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $dailyExpenses = Expense::select(
                DB::raw('DATE(expense_date) as date'),
                DB::raw('SUM(amount) as total')
            )
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(expense_date)'))
            ->orderBy('date')
            ->get();

        $totalExpenses = $expenses->sum('amount');

        // Monthly comparison (last 6 months)
        $monthlyComparison = [];
        for ($i = 5; $i >= 0; $i--) {
            $compMonth = now()->subMonths($i);
            $monthlyComparison[] = [
                'month' => $compMonth->format('M Y'),
                'total' => Expense::whereBetween('expense_date', [
                    $compMonth->copy()->startOfMonth(),
                    $compMonth->copy()->endOfMonth()
                ])->sum('amount') ?: 0,
            ];
        }

        return view('financial.expenses', compact(
            'month', 'expenses', 'byCategory', 'dailyExpenses', 'totalExpenses', 'monthlyComparison'
        ));
    }

    /**
     * Wages / Salary Management page
     */
    public function wages(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));

        // Auto-sync: ensure all staff users have employee records
        Employees::syncFromUsers();

        $employees = Employees::with('users')->get();
        $salaryPayments = SalaryPayment::where('month', $month)->orderBy('employee_name')->get();

        $stats = SalaryPayment::getMonthlyStats($month);

        return view('financial.wages', compact('month', 'employees', 'salaryPayments', 'stats'));
    }

    /**
     * Store salary payment
     */
    public function storeSalary(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|string',
            'base_salary' => 'required|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $employee = Employees::with('users')->findOrFail($request->employee_id);
        $bonus = $request->bonus ?? 0;
        $deduction = $request->deduction ?? 0;
        $netSalary = $request->base_salary + $bonus - $deduction;

        SalaryPayment::updateOrCreate(
            ['employee_id' => $request->employee_id, 'month' => $request->month],
            [
                'employee_name' => $employee->users->name ?? 'Employee #' . $employee->id,
                'base_salary' => $request->base_salary,
                'bonus' => $bonus,
                'deduction' => $deduction,
                'net_salary' => $netSalary,
                'payment_method' => $request->payment_method ?? 'cash',
                'notes' => $request->notes,
                'status' => 'pending',
            ]
        );

        return redirect()->back()->with('success', 'Salary record saved successfully.');
    }

    /**
     * Mark salary as paid
     */
    public function markSalaryPaid(Request $request, $id)
    {
        $salary = SalaryPayment::findOrFail($id);
        $salary->update([
            'status' => 'paid',
            'payment_date' => now(),
            'paid_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Salary marked as paid.');
    }

    /**
     * Profit & Loss Statement
     */
    public function profitLoss(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $day = $request->get('day', ''); // optional day filter (Y-m-d)
        $filterMode = $day ? 'day' : 'month';

        if ($day) {
            $startDate = Carbon::parse($day)->startOfDay();
            $endDate = Carbon::parse($day)->endOfDay();
        } else {
            $startDate = Carbon::parse($month . '-01')->startOfMonth();
            $endDate = Carbon::parse($month . '-01')->endOfMonth();
        }

        // Income
        // DB total_price/amount already has discount subtracted, so it IS the net revenue
        $netRevenue = Bills::whereBetween('created_at', [$startDate, $endDate])->sum(DB::raw('COALESCE(total_price, amount)')) ?: 0;
        $totalDiscount = Bills::whereBetween('created_at', [$startDate, $endDate])->sum('discount') ?: 0;
        $grossRevenue = $netRevenue + $totalDiscount; // Reconstruct pre-discount amount
        $totalCollected = Bills::whereBetween('created_at', [$startDate, $endDate])->sum('paid_amount') ?: 0;
        $totalDue = $netRevenue - $totalCollected; // Outstanding = what's owed minus what's paid
        $totalRevenue = $grossRevenue; // alias

        // Expenditures
        $totalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount') ?: 0;
        $totalSalaries = SalaryPayment::where('month', $month)->where('status', 'paid')->sum('net_salary') ?: 0;
        $totalDoctorCommissions = DoctorCommission::whereBetween('created_at', [$startDate, $endDate])->sum('commission_amount') ?: 0;
        $totalReferralCommissions = ReferralCommission::whereBetween('created_at', [$startDate, $endDate])->sum('commission_amount') ?: 0;

        $totalOutgoing = $totalExpenses + $totalSalaries + $totalDoctorCommissions + $totalReferralCommissions;
        $netProfit = $totalCollected - $totalOutgoing;

        // Expense breakdown
        $expenseBreakdown = Expense::select('category', DB::raw('SUM(amount) as total'))
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        // Monthly P&L trend (only for month view)
        $plTrends = [];
        if ($filterMode !== 'day') {
            for ($i = 5; $i >= 0; $i--) {
                $trendMonth = now()->subMonths($i);
                $tStart = $trendMonth->copy()->startOfMonth();
                $tEnd = $trendMonth->copy()->endOfMonth();
                $mKey = $trendMonth->format('Y-m');

                $income = Bills::whereBetween('created_at', [$tStart, $tEnd])->sum('paid_amount') ?: 0;
                $expenses = Expense::whereBetween('expense_date', [$tStart, $tEnd])->sum('amount') ?: 0;
                $salaries = SalaryPayment::where('month', $mKey)->where('status', 'paid')->sum('net_salary') ?: 0;
                $commissions = DoctorCommission::whereBetween('created_at', [$tStart, $tEnd])->sum('commission_amount') ?: 0;

                $plTrends[] = [
                    'month' => $trendMonth->format('M Y'),
                    'income' => $income,
                    'outgoing' => $expenses + $salaries + $commissions,
                    'profit' => $income - ($expenses + $salaries + $commissions),
                ];
            }
        }

        // Bills list (for day view detail table)
        $bills = collect();
        if ($filterMode === 'day') {
            $bills = Bills::with('patient')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('financial.profit-loss', compact(
            'month', 'day', 'filterMode', 'bills',
            'grossRevenue', 'netRevenue', 'totalRevenue', 'totalCollected', 'totalDue', 'totalDiscount',
            'totalExpenses', 'totalSalaries', 'totalDoctorCommissions', 'totalReferralCommissions',
            'totalOutgoing', 'netProfit', 'expenseBreakdown', 'plTrends'
        ));
    }

    /**
     * Monthly Report (printable / end-of-month summary)
     */
    public function monthlyReport(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();

        // All financials
        // DB total_price/amount already has discount subtracted, so it IS the net revenue
        $netRevenue = Bills::whereBetween('created_at', [$startDate, $endDate])->sum(DB::raw('COALESCE(total_price, amount)')) ?: 0;
        $totalDiscount = Bills::whereBetween('created_at', [$startDate, $endDate])->sum('discount') ?: 0;
        $grossRevenue = $netRevenue + $totalDiscount; // Reconstruct pre-discount amount
        $totalCollected = Bills::whereBetween('created_at', [$startDate, $endDate])->sum('paid_amount') ?: 0;
        $totalDue = $netRevenue - $totalCollected;
        $totalRevenue = $grossRevenue; // alias
        $billCount = Bills::whereBetween('created_at', [$startDate, $endDate])->count();
        $patientCount = Patients::whereBetween('created_at', [$startDate, $endDate])->count();

        $totalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount') ?: 0;
        $totalSalaries = SalaryPayment::where('month', $month)->sum('net_salary') ?: 0;
        $salariesPaid = SalaryPayment::where('month', $month)->where('status', 'paid')->sum('net_salary') ?: 0;
        $salariesPending = SalaryPayment::where('month', $month)->where('status', 'pending')->sum('net_salary') ?: 0;
        
        $totalDoctorCommissions = DoctorCommission::whereBetween('created_at', [$startDate, $endDate])->sum('commission_amount') ?: 0;
        $totalReferralCommissions = ReferralCommission::whereBetween('created_at', [$startDate, $endDate])->sum('commission_amount') ?: 0;

        $expenseBreakdown = Expense::select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $totalOutgoing = $totalExpenses + $salariesPaid + $totalDoctorCommissions + $totalReferralCommissions;
        $netProfit = $totalCollected - $totalOutgoing;

        // Top referrals by commission
        $topReferrals = ReferralCommission::select('referral_id', DB::raw('SUM(commission_amount) as total'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('referral_id')
            ->with('referral')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        return view('financial.monthly-report', compact(
            'month', 'startDate', 'endDate', 'grossRevenue', 'netRevenue', 'totalRevenue',
            'totalCollected', 'totalDue', 'totalDiscount', 'billCount', 'patientCount',
            'totalExpenses', 'totalSalaries', 'salariesPaid', 'salariesPending',
            'totalDoctorCommissions', 'totalReferralCommissions',
            'expenseBreakdown', 'totalOutgoing', 'netProfit', 'topReferrals'
        ));
    }
}
