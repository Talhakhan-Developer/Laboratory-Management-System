@extends('Layout.master')
@section('title', 'Financial Dashboard')

@section('content')
<style>
    .fin-card {
        border-radius: 14px;
        border: none;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        overflow: hidden;
    }
    .fin-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
    }
    .stat-card {
        position: relative;
        border-radius: 14px;
        border: none;
        overflow: hidden;
    }
    .stat-card .stat-icon {
        position: absolute;
        right: 15px;
        top: 15px;
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        opacity: 0.9;
    }
    .stat-card .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: -0.5px;
    }
    .stat-card .stat-label {
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #8492a6;
        margin-bottom: 6px;
    }
    .stat-card .stat-sub {
        font-size: 0.8rem;
        margin-top: 4px;
    }
    .breakdown-card {
        border-radius: 14px;
        border: none;
        text-align: center;
        transition: transform 0.15s ease;
    }
    .breakdown-card:hover {
        transform: translateY(-2px);
    }
    .breakdown-card .bd-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        margin-bottom: 12px;
    }
    .quick-link {
        border-radius: 12px;
        padding: 18px 10px;
        text-align: center;
        transition: all 0.2s ease;
        border: 2px solid transparent;
        text-decoration: none !important;
        display: block;
    }
    .quick-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    .quick-link i {
        font-size: 1.6rem;
        margin-bottom: 6px;
        display: block;
    }
    .quick-link span {
        font-size: 0.85rem;
        font-weight: 600;
    }
    .section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #3e4954;
        margin-bottom: 0;
    }
    .section-title i {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        margin-right: 8px;
    }
    .table-fin thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #8492a6;
        border-top: none;
        padding: 10px 12px;
        background: #f8f9fc;
    }
    .table-fin tbody td {
        padding: 10px 12px;
        vertical-align: middle;
        font-size: 0.88rem;
    }
    .table-fin tbody tr:hover {
        background: rgba(0,123,255,0.03);
    }
    .progress-thin {
        height: 5px;
        border-radius: 5px;
        background: #edf2f9;
    }
    .cat-row {
        padding: 8px 0;
        border-bottom: 1px solid #f1f3f8;
    }
    .cat-row:last-child {
        border-bottom: none;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Financial Dashboard</li>
                    </ol>
                </div>
                <h4 class="page-title">Financial Dashboard</h4>
            </div>
        </div>
    </div>

    {{-- Month Selector --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <form method="GET" action="{{ route('financial.dashboard') }}" class="d-flex align-items-center">
                <label class="mr-2 mb-0 font-weight-bold" style="white-space: nowrap;">Month:</label>
                <input type="month" name="month" value="{{ $month }}" class="form-control" onchange="this.form.submit()" style="max-width: 200px; border-radius: 8px;">
            </form>
        </div>
        <div class="col-md-8 text-right">
            <a href="{{ route('financial.monthly-report', ['month' => $month]) }}" class="btn btn-primary" style="border-radius: 8px;">
                <i class="fas fa-print mr-1"></i> Monthly Report
            </a>
        </div>
    </div>

    {{-- Top Summary Cards --}}
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body pb-2">
                    <div class="stat-icon" style="background: rgba(108,117,125,0.1); color: #6c757d;">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <p class="stat-label">Total Bills Amount</p>
                    <h4 class="stat-value mb-0">Rs. {{ number_format($grossRevenue, 2) }}</h4>
                    <p class="stat-sub text-muted mb-0">{{ $billCount }} bills generated</p>
                </div>
                <div style="height: 4px; background: #6c757d;"></div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body pb-2">
                    <div class="stat-icon" style="background: rgba(255,193,7,0.12); color: #e0a800;">
                        <i class="fas fa-percent"></i>
                    </div>
                    <p class="stat-label">Discount Given</p>
                    <h4 class="stat-value text-warning mb-0">Rs. {{ number_format($totalDiscount, 2) }}</h4>
                    <p class="stat-sub text-muted mb-0">To patients</p>
                </div>
                <div style="height: 4px; background: #ffc107;"></div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body pb-2">
                    <div class="stat-icon" style="background: rgba(40,167,69,0.1); color: #28a745;">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <p class="stat-label">After Discount</p>
                    <h4 class="stat-value text-success mb-0">Rs. {{ number_format($netRevenue, 2) }}</h4>
                    <p class="stat-sub text-muted mb-0">Actual bill total</p>
                </div>
                <div style="height: 4px; background: #28a745;"></div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body pb-2">
                    <div class="stat-icon" style="background: rgba(0,123,255,0.1); color: #007bff;">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <p class="stat-label">Amount Received</p>
                    <h4 class="stat-value text-primary mb-0">Rs. {{ number_format($totalCollected, 2) }}</h4>
                    <p class="stat-sub mb-0"><span class="text-danger font-weight-bold">Pending: Rs. {{ number_format($totalDue, 2) }}</span></p>
                </div>
                <div style="height: 4px; background: #007bff;"></div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body pb-2">
                    <div class="stat-icon" style="background: rgba(220,53,69,0.1); color: #dc3545;">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <p class="stat-label">Total Expenses</p>
                    <h4 class="stat-value text-danger mb-0">Rs. {{ number_format($totalOutgoing, 2) }}</h4>
                    <p class="stat-sub text-muted mb-0">Expenses + Commission + Salaries </p>
                </div>
                <div style="height: 4px; background: #dc3545;"></div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body pb-2">
                    <div class="stat-icon" style="background: rgba({{ $netProfit >= 0 ? '40,167,69' : '220,53,69' }},0.1); color: {{ $netProfit >= 0 ? '#28a745' : '#dc3545' }};">
                        <i class="fas {{ $netProfit >= 0 ? 'fa-trending-up fa-arrow-up' : 'fa-arrow-down' }}"></i>
                    </div>
                    <p class="stat-label">{{ $netProfit >= 0 ? 'Profit' : 'Loss' }}</p>
                    <h4 class="stat-value {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }} mb-0">Rs. {{ number_format($netProfit, 2) }}</h4>
                    <p class="stat-sub text-muted mb-0">Received - Expenses</p>
                </div>
                <div style="height: 4px; background: {{ $netProfit >= 0 ? '#28a745' : '#dc3545' }};"></div>
            </div>
        </div>
    </div>

    {{-- Detailed Breakdown Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card breakdown-card shadow-sm h-100">
                <div class="card-body">
                    <div class="bd-icon" style="background: rgba(255,193,7,0.12); color: #e0a800;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h6 class="text-muted mb-1">Expenses</h6>
                    <h4 class="font-weight-bold">Rs. {{ number_format($totalExpenses, 2) }}</h4>
                    <a href="{{ route('financial.expense-analysis', ['month' => $month]) }}" class="btn btn-sm btn-outline-warning mt-2" style="border-radius: 8px;">
                        <i class="fas fa-external-link-alt mr-1"></i>View Details
                    </a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card breakdown-card shadow-sm h-100">
                <div class="card-body">
                    <div class="bd-icon" style="background: rgba(23,162,184,0.12); color: #17a2b8;">
                        <i class="fas fa-users"></i>
                    </div>
                    <h6 class="text-muted mb-1">Salaries</h6>
                    <h4 class="font-weight-bold">Rs. {{ number_format($salaryStats['total_payable'] ?? 0, 2) }}</h4>
                    <small class="text-muted d-block mb-1">Paid: <span class="text-success font-weight-bold">Rs. {{ number_format($salaryStats['total_paid'] ?? 0, 2) }}</span></small>
                    <a href="{{ route('financial.wages', ['month' => $month]) }}" class="btn btn-sm btn-outline-info mt-1" style="border-radius: 8px;">
                        <i class="fas fa-external-link-alt mr-1"></i>View Details
                    </a>
                </div>
            </div>
        </div>
        <!-- <div class="col-xl-3 col-md-6 mb-3">
            <div class="card breakdown-card shadow-sm h-100">
                <div class="card-body">
                    <div class="bd-icon" style="background: rgba(111,66,193,0.12); color: #6f42c1;">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h6 class="text-muted mb-1">Doctor Commissions</h6>
                    <h4 class="font-weight-bold">Rs. {{ number_format($commissionStats['total'] ?? 0, 2) }}</h4>
                    <small class="text-muted d-block mb-1">Pending: <span class="text-danger font-weight-bold">Rs. {{ number_format($commissionStats['pending'] ?? 0, 2) }}</span></small>
                    <a href="{{ route('financial.doctor-commissions', ['month' => $month]) }}" class="btn btn-sm btn-outline-secondary mt-1" style="border-radius: 8px;">
                        <i class="fas fa-external-link-alt mr-1"></i>View Details
                    </a>
                </div>
            </div>
        </div> -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card breakdown-card shadow-sm h-100">
                <div class="card-body">
                    <div class="bd-icon" style="background: rgba(32,201,151,0.12); color: #20c997;">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h6 class="text-muted mb-1">Referral Commissions</h6>
                    <h4 class="font-weight-bold">Rs. {{ number_format($referralCommissionTotal, 2) }}</h4>
                    <small class="text-muted d-block mb-1">Pending: <span class="text-danger font-weight-bold">Rs. {{ number_format($referralCommissionPending, 2) }}</span></small>
                    <a href="{{ route('commissions.dashboard') }}" class="btn btn-sm btn-outline-success mt-1" style="border-radius: 8px;">
                        <i class="fas fa-external-link-alt mr-1"></i>View Details
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row mb-4">
        <div class="col-xl-8 mb-3">
            <div class="card fin-card shadow-sm">
                <div class="card-body">
                    <h5 class="section-title mb-3">
                        <i class="fas fa-chart-area" style="background: rgba(0,123,255,0.1); color: #007bff;"></i>
                        6-Month Revenue Trend
                    </h5>
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4 mb-3">
            <div class="card fin-card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="section-title mb-3">
                        <i class="fas fa-credit-card" style="background: rgba(0,123,255,0.1); color: #007bff;"></i>
                        Payment Methods
                    </h5>
                    <canvas id="paymentChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Daily Revenue + Expenses --}}
    <div class="row mb-4">
        <div class="col-xl-8 mb-3">
            <div class="card fin-card shadow-sm">
                <div class="card-body">
                    <h5 class="section-title mb-3">
                        <i class="fas fa-calendar-day" style="background: rgba(23,162,184,0.1); color: #17a2b8;"></i>
                        Daily Revenue Breakdown
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-fin mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>After Discount</th>
                                    <th>Amount Received</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dailyRevenue as $day)
                                <tr>
                                    <td class="font-weight-bold">{{ \Carbon\Carbon::parse($day->date)->format('d M, Y (D)') }}</td>
                                    <td>Rs. {{ number_format($day->revenue, 2) }}</td>
                                    <td class="text-success font-weight-bold">Rs. {{ number_format($day->collected, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">No data for this month</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 mb-3">
            <div class="card fin-card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="section-title mb-3">
                        <i class="fas fa-tags" style="background: rgba(255,193,7,0.12); color: #e0a800;"></i>
                        Expenses by Category
                    </h5>
                    @forelse($expensesByCategory as $cat)
                    <div class="cat-row">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-capitalize font-weight-bold" style="font-size: 0.88rem;">{{ $cat->category }}</span>
                            <span class="font-weight-bold" style="font-size: 0.88rem;">Rs. {{ number_format($cat->total, 2) }}</span>
                        </div>
                        <div class="progress progress-thin">
                            <div class="progress-bar" style="width: {{ $totalExpenses > 0 ? ($cat->total / $totalExpenses * 100) : 0 }}%; background: linear-gradient(90deg, #ffc107, #e0a800);"></div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block" style="opacity: 0.3;"></i>
                        No expenses recorded
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card fin-card shadow-sm">
                <div class="card-body">
                    <h5 class="section-title mb-3">
                        <i class="fas fa-th-large" style="background: rgba(0,123,255,0.1); color: #007bff;"></i>
                        Quick Actions
                    </h5>
                    <div class="row">
                        <div class="col-md-2 col-6 mb-2">
                            <a href="{{ route('financial.revenue', ['month' => $month]) }}" class="quick-link" style="background: rgba(40,167,69,0.06); border-color: rgba(40,167,69,0.15); color: #28a745;">
                                <i class="fas fa-chart-line"></i>
                                <span>Revenue</span>
                            </a>
                        </div>
                        <div class="col-md-2 col-6 mb-2">
                            <a href="{{ route('financial.expense-analysis', ['month' => $month]) }}" class="quick-link" style="background: rgba(255,193,7,0.06); border-color: rgba(255,193,7,0.2); color: #e0a800;">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>Expenses</span>
                            </a>
                        </div>
                        <div class="col-md-2 col-6 mb-2">
                            <a href="{{ route('financial.doctor-commissions', ['month' => $month]) }}" class="quick-link" style="background: rgba(23,162,184,0.06); border-color: rgba(23,162,184,0.15); color: #17a2b8;">
                                <i class="fas fa-user-md"></i>
                                <span>Commissions</span>
                            </a>
                        </div>
                        <div class="col-md-2 col-6 mb-2">
                            <a href="{{ route('financial.wages', ['month' => $month]) }}" class="quick-link" style="background: rgba(0,123,255,0.06); border-color: rgba(0,123,255,0.15); color: #007bff;">
                                <i class="fas fa-hand-holding-usd"></i>
                                <span>Wages</span>
                            </a>
                        </div>
                        <div class="col-md-2 col-6 mb-2">
                            <a href="{{ route('financial.profit-loss', ['month' => $month]) }}" class="quick-link" style="background: rgba(220,53,69,0.06); border-color: rgba(220,53,69,0.15); color: #dc3545;">
                                <i class="fas fa-balance-scale"></i>
                                <span>P&L</span>
                            </a>
                        </div>
                        <div class="col-md-2 col-6 mb-2">
                            <a href="{{ route('financial.monthly-report', ['month' => $month]) }}" class="quick-link" style="background: rgba(108,117,125,0.06); border-color: rgba(108,117,125,0.15); color: #6c757d;">
                                <i class="fas fa-file-invoice-dollar"></i>
                                <span>Report</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Trend Chart
    var ctx1 = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: {!! json_encode(collect($monthlyTrends)->pluck('month')) !!},
            datasets: [
                {
                    label: 'After Discount',
                    data: {!! json_encode(collect($monthlyTrends)->pluck('revenue')) !!},
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40,167,69,0.08)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#28a745',
                },
                {
                    label: 'Amount Received',
                    data: {!! json_encode(collect($monthlyTrends)->pluck('collected')) !!},
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0,123,255,0.08)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#007bff',
                },
                {
                    label: 'Total Expenses',
                    data: {!! json_encode(collect($monthlyTrends)->pluck('expenses')) !!},
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220,53,69,0.08)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#dc3545',
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(ctx) { return ctx.dataset.label + ': Rs. ' + ctx.parsed.y.toLocaleString(); }
                    }
                }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: function(v) { return 'Rs. ' + (v/1000).toFixed(0) + 'k'; } } },
                x: { grid: { display: false } }
            }
        }
    });

    // Payment Methods Doughnut
    var ctx2 = document.getElementById('paymentChart').getContext('2d');
    var paymentData = @json($paymentMethods);
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: paymentData.map(p => (p.payment_type || 'Cash').charAt(0).toUpperCase() + (p.payment_type || 'cash').slice(1)),
            datasets: [{
                data: paymentData.map(p => p.total),
                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#20c997'],
                borderWidth: 0,
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15 } },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(ctx) { return ctx.label + ': Rs. ' + ctx.parsed.toLocaleString(); }
                    }
                }
            }
        }
    });
});
</script>
@endsection
