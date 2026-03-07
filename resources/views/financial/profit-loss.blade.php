@extends('Layout.master')
@section('title', 'Profit & Loss Statement')

@section('content')
<style>
    .fin-card { border-radius: 14px; border: none; transition: transform 0.15s ease, box-shadow 0.15s ease; }
    .fin-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important; }
    .section-title { font-size: 1rem; font-weight: 700; color: #3e4954; margin-bottom: 0; }
    .section-title i { width: 28px; height: 28px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; margin-right: 8px; }
    .table-fin thead th { font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; color: #8492a6; border-top: none; padding: 10px 12px; background: #f8f9fc; }
    .table-fin tbody td { padding: 10px 12px; vertical-align: middle; font-size: 0.88rem; }
    .table-fin tbody tr:hover { background: rgba(0,123,255,0.03); }
    .filter-bar { background: #fff; border-radius: 12px; padding: 12px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border: 1px solid #edf2f9; }
    .pl-table td { padding: 10px 16px !important; font-size: 0.92rem; }
    .pl-table tr.highlight-row { background: rgba(40,167,69,0.05); }
    .pl-table tr.highlight-row-danger { background: rgba(220,53,69,0.04); }
    .profit-banner { border-radius: 14px; overflow: hidden; position: relative; }
    .profit-banner .banner-bg { position: absolute; top: 0; right: 0; bottom: 0; width: 200px; opacity: 0.04; font-size: 120px; display: flex; align-items: center; justify-content: center; }
    .expense-item { background: rgba(0,0,0,0.02); border: 1px solid #f1f3f8; border-radius: 10px; padding: 12px 16px; margin-bottom: 12px; }
    .status-pill { padding: 6px 14px; border-radius: 20px; font-size: 0.82rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('financial.dashboard') }}">Financial</a></li>
                        <li class="breadcrumb-item active">Profit & Loss</li>
                    </ol>
                </div>
                <h4 class="page-title">Profit & Loss Statement</h4>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="filter-bar d-flex align-items-center flex-wrap">
                <form method="GET" action="{{ route('financial.profit-loss') }}" id="plFilterForm" class="d-flex align-items-center flex-wrap w-100">
                    <input type="hidden" name="month" id="hiddenMonth" value="{{ $month }}">

                    <div class="btn-group mr-3 mb-2 mb-md-0" role="group">
                        <button type="button" class="btn btn-{{ $filterMode === 'month' ? 'primary' : 'outline-primary' }} btn-sm" onclick="switchFilterMode('month')" style="border-radius: 8px 0 0 8px;">
                            <i class="fas fa-calendar-alt mr-1"></i>Monthly
                        </button>
                        <button type="button" class="btn btn-{{ $filterMode === 'day' ? 'primary' : 'outline-primary' }} btn-sm" onclick="switchFilterMode('day')" style="border-radius: 0 8px 8px 0;">
                            <i class="fas fa-calendar-day mr-1"></i>Daily
                        </button>
                    </div>

                    <div id="monthFilter" class="d-flex align-items-center mr-3 mb-2 mb-md-0" style="{{ $filterMode === 'day' ? 'display:none!important;' : '' }}">
                        <label class="mr-2 mb-0 font-weight-bold text-muted" style="white-space: nowrap; font-size: 0.85rem;">Month:</label>
                        <input type="month" id="monthInput" value="{{ $month }}" class="form-control form-control-sm" onchange="document.getElementById('hiddenMonth').value=this.value; this.form.submit()" style="max-width: 180px; border-radius: 8px;">
                    </div>

                    <div id="dayFilter" class="d-flex align-items-center mr-3 mb-2 mb-md-0" style="{{ $filterMode !== 'day' ? 'display:none!important;' : '' }}">
                        <label class="mr-2 mb-0 font-weight-bold text-muted" style="white-space: nowrap; font-size: 0.85rem;">Date:</label>
                        <input type="date" name="day" value="{{ $day }}" class="form-control form-control-sm" onchange="this.form.submit()" style="max-width: 180px; border-radius: 8px;">
                    </div>

                    @if($filterMode === 'day' && $day)
                    <a href="{{ route('financial.profit-loss', ['month' => $month]) }}" class="btn btn-outline-secondary btn-sm mb-2 mb-md-0" style="border-radius: 8px;">
                        <i class="fas fa-times mr-1"></i>Clear
                    </a>
                    <span class="ml-auto badge badge-info py-2 px-3 mb-2 mb-md-0" style="border-radius: 8px; font-size: 0.82rem;">
                        <i class="fas fa-info-circle mr-1"></i>{{ \Carbon\Carbon::parse($day)->format('d M, Y (l)') }}
                    </span>
                    @endif
                </form>
            </div>
        </div>
    </div>

    {{-- P&L Summary --}}
    <div class="row mb-4">
        <div class="col-xl-6 mb-3">
            <div class="card fin-card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="section-title mb-4">
                        <i class="fas fa-arrow-circle-up" style="background: rgba(40,167,69,0.1); color: #28a745;"></i>
                        INCOME
                    </h5>
                    <table class="table table-borderless pl-table mb-0">
                        <tr>
                            <td>Total Bills Amount</td>
                            <td class="text-right"><strong>Rs. {{ number_format($grossRevenue, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-minus-circle text-warning mr-1" style="font-size: 0.75rem;"></i> Less: Discount Given</td>
                            <td class="text-right text-warning">- Rs. {{ number_format($totalDiscount, 2) }}</td>
                        </tr>
                        <tr class="highlight-row border-top">
                            <td><strong>After Discount</strong></td>
                            <td class="text-right"><strong class="text-success" style="font-size: 1.05rem;">Rs. {{ number_format($netRevenue, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td>&nbsp;&nbsp;<i class="fas fa-check-circle text-success mr-1" style="font-size: 0.8rem;"></i> Amount Received</td>
                            <td class="text-right text-success">Rs. {{ number_format($totalCollected, 2) }}</td>
                        </tr>
                        <tr>
                            <td>&nbsp;&nbsp;<i class="fas fa-clock text-danger mr-1" style="font-size: 0.8rem;"></i> Pending</td>
                            <td class="text-right text-danger">Rs. {{ number_format($totalDue, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-3">
            <div class="card fin-card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="section-title mb-4">
                        <i class="fas fa-arrow-circle-down" style="background: rgba(220,53,69,0.1); color: #dc3545;"></i>
                        EXPENDITURE
                    </h5>
                    <table class="table table-borderless pl-table mb-0">
                        <tr>
                            <td><i class="fas fa-receipt mr-1" style="font-size: 0.75rem; color: #8492a6;"></i> Operating Expenses</td>
                            <td class="text-right">Rs. {{ number_format($totalExpenses, 2) }}</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-wallet mr-1" style="font-size: 0.75rem; color: #8492a6;"></i> Salaries & Wages (Paid)</td>
                            <td class="text-right">Rs. {{ number_format($totalSalaries, 2) }}</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-user-md mr-1" style="font-size: 0.75rem; color: #8492a6;"></i> Doctor Commissions</td>
                            <td class="text-right">Rs. {{ number_format($totalDoctorCommissions, 2) }}</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-handshake mr-1" style="font-size: 0.75rem; color: #8492a6;"></i> Referral Commissions</td>
                            <td class="text-right">Rs. {{ number_format($totalReferralCommissions, 2) }}</td>
                        </tr>
                        <tr class="highlight-row-danger border-top">
                            <td><strong>Total Expenses</strong></td>
                            <td class="text-right"><strong class="text-danger" style="font-size: 1.05rem;">Rs. {{ number_format($totalOutgoing, 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Net Profit Banner --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card profit-banner shadow-sm" style="border: 2px solid {{ $netProfit >= 0 ? '#28a745' : '#dc3545' }};">
                <div class="card-body text-center py-4 position-relative">
                    <div class="banner-bg">
                        <i class="fas {{ $netProfit >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                    </div>
                    <p class="text-muted mb-2" style="font-size: 0.82rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Profit / Loss</p>
                    <h1 class="{{ $netProfit >= 0 ? 'text-success' : 'text-danger' }} mb-2" style="font-size: 2.2rem; font-weight: 800;">
                        <i class="fas {{ $netProfit >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-2" style="font-size: 1.5rem;"></i>
                        Rs. {{ number_format(abs($netProfit), 2) }}
                    </h1>
                    <span class="badge py-2 px-3" style="border-radius: 20px; font-size: 0.85rem; background: {{ $netProfit >= 0 ? 'rgba(40,167,69,0.1)' : 'rgba(220,53,69,0.1)' }}; color: {{ $netProfit >= 0 ? '#28a745' : '#dc3545' }};">
                        {{ $netProfit >= 0 ? 'Profit' : 'Loss' }}
                    </span>
                    <p class="text-muted mt-3 mb-0" style="font-size: 0.85rem;">
                        Amount Received (<strong>Rs. {{ number_format($totalCollected, 2) }}</strong>) &minus; Total Expenses (<strong>Rs. {{ number_format($totalOutgoing, 2) }}</strong>)
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Expense Breakdown & P&L Trend --}}
    <div class="row mb-4">
        <div class="{{ $filterMode === 'day' ? 'col-xl-12' : 'col-xl-5' }} mb-3">
            <div class="card fin-card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="section-title mb-3">
                        <i class="fas fa-tags" style="background: rgba(255,193,7,0.12); color: #e0a800;"></i>
                        Expense Breakdown
                    </h5>
                    @forelse($expenseBreakdown as $cat)
                    <div class="expense-item">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-capitalize font-weight-bold" style="font-size: 0.9rem;">{{ $cat->category }}</span>
                            <strong style="font-size: 0.9rem;">Rs. {{ number_format($cat->total, 2) }}</strong>
                        </div>
                        <div class="progress" style="height: 5px; border-radius: 10px; background: #edf2f9;">
                            <div class="progress-bar" style="width: {{ $totalExpenses > 0 ? ($cat->total / $totalExpenses * 100) : 0 }}%; border-radius: 10px; background: linear-gradient(90deg, #ffc107, #e0a800);"></div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block" style="opacity: 0.3;"></i>No expenses
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        @if($filterMode !== 'day')
        <div class="col-xl-7 mb-3">
            <div class="card fin-card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="section-title mb-3">
                        <i class="fas fa-chart-area" style="background: rgba(0,123,255,0.1); color: #007bff;"></i>
                        6-Month P&L Trend
                    </h5>
                    <canvas id="plChart" height="120"></canvas>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Bills Detail (day view) --}}
    @if($filterMode === 'day' && $day)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card fin-card shadow-sm">
                <div class="card-body">
                    <h5 class="section-title mb-3">
                        <i class="fas fa-clock" style="background: rgba(23,162,184,0.1); color: #17a2b8;"></i>
                        Bills on {{ \Carbon\Carbon::parse($day)->format('d M, Y') }}
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-fin mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Time</th>
                                    <th>Patient</th>
                                    <th>Total</th>
                                    <th>Paid</th>
                                    <th>Due</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bills as $i => $bill)
                                <tr>
                                    <td><span class="badge badge-light" style="border-radius: 6px;">{{ $i + 1 }}</span></td>
                                    <td>{{ \Carbon\Carbon::parse($bill->created_at)->format('h:i A') }}</td>
                                    <td class="font-weight-bold">{{ $bill->patient->name ?? 'N/A' }}</td>
                                    <td>Rs. {{ number_format($bill->total_price, 2) }}</td>
                                    <td class="text-success font-weight-bold">Rs. {{ number_format($bill->paid_amount, 2) }}</td>
                                    <td class="text-danger">Rs. {{ number_format($bill->due_amount, 2) }}</td>
                                    <td>
                                        <span class="status-pill" style="font-size: 0.78rem; background: {{ $bill->status == 'Paid' ? 'rgba(40,167,69,0.1)' : ($bill->status == 'Pending' ? 'rgba(255,193,7,0.12)' : 'rgba(220,53,69,0.1)') }}; color: {{ $bill->status == 'Paid' ? '#28a745' : ($bill->status == 'Pending' ? '#e0a800' : '#dc3545') }};">
                                            {{ $bill->status ?? 'Unknown' }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">No bills for this day</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
function switchFilterMode(mode) {
    var monthFilter = document.getElementById('monthFilter');
    var dayFilter = document.getElementById('dayFilter');
    if (mode === 'day') {
        monthFilter.style.display = 'none';
        dayFilter.style.display = 'flex';
    } else {
        monthFilter.style.display = 'flex';
        dayFilter.style.display = 'none';
        dayFilter.querySelector('input[name=day]').value = '';
        document.getElementById('plFilterForm').submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var chartEl = document.getElementById('plChart');
    if (!chartEl) return;
    var plData = @json($plTrends);
    new Chart(chartEl.getContext('2d'), {
        type: 'bar',
        data: {
            labels: plData.map(d => d.month),
            datasets: [
                {
                    label: 'Income',
                    data: plData.map(d => d.income),
                    backgroundColor: 'rgba(40,167,69,0.65)',
                    borderRadius: 4,
                    borderSkipped: false,
                },
                {
                    label: 'Expenditure',
                    data: plData.map(d => d.outgoing),
                    backgroundColor: 'rgba(220,53,69,0.65)',
                    borderRadius: 4,
                    borderSkipped: false,
                },
                {
                    label: 'Net Profit',
                    data: plData.map(d => d.profit),
                    type: 'line',
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0,123,255,0.08)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#007bff',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 2,
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)', padding: 12, cornerRadius: 8,
                    callbacks: { label: function(ctx) { return ctx.dataset.label + ': Rs. ' + ctx.parsed.y.toLocaleString(); } }
                }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: function(v) { return 'Rs. ' + (v/1000).toFixed(0) + 'k'; } } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
@endsection
