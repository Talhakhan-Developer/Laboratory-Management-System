@extends('Layout.master')
@section('title', 'Revenue Analysis')

@section('content')
<style>
    .fin-card { border-radius: 14px; border: none; transition: transform 0.15s ease, box-shadow 0.15s ease; }
    .fin-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important; }
    .stat-card { position: relative; border-radius: 14px; border: none; overflow: hidden; }
    .stat-card .stat-icon { position: absolute; right: 15px; top: 15px; width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; opacity: 0.9; }
    .stat-card .stat-value { font-size: 1.4rem; font-weight: 700; letter-spacing: -0.5px; }
    .stat-card .stat-label { font-size: 0.78rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #8492a6; margin-bottom: 6px; }
    .stat-card .stat-sub { font-size: 0.8rem; margin-top: 4px; }
    .section-title { font-size: 1rem; font-weight: 700; color: #3e4954; margin-bottom: 0; }
    .section-title i { width: 28px; height: 28px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; margin-right: 8px; }
    .table-fin thead th { font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; color: #8492a6; border-top: none; padding: 10px 12px; background: #f8f9fc; }
    .table-fin tbody td { padding: 10px 12px; vertical-align: middle; font-size: 0.88rem; }
    .table-fin tbody tr:hover { background: rgba(0,123,255,0.03); }
    .filter-bar { background: #fff; border-radius: 12px; padding: 12px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border: 1px solid #edf2f9; }
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
                        <li class="breadcrumb-item active">Revenue Analysis</li>
                    </ol>
                </div>
                <h4 class="page-title">Revenue Analysis</h4>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="filter-bar d-flex align-items-center flex-wrap">
                <form method="GET" action="{{ route('financial.revenue') }}" id="revenueFilterForm" class="d-flex align-items-center flex-wrap w-100">
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
                    <a href="{{ route('financial.revenue', ['month' => $month]) }}" class="btn btn-outline-secondary btn-sm mb-2 mb-md-0" style="border-radius: 8px;">
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

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-xl col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body pb-2">
                    <div class="stat-icon" style="background: rgba(108,117,125,0.1); color: #6c757d;">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <p class="stat-label">Total Bills Amount</p>
                    <h4 class="stat-value mb-0">Rs. {{ number_format($grossRevenue, 2) }}</h4>
                    <p class="stat-sub text-muted mb-0">Before discount</p>
                </div>
                <div style="height: 4px; background: #6c757d;"></div>
            </div>
        </div>
        <div class="col-xl col-md-6 mb-3">
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
        <div class="col-xl col-md-6 mb-3">
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
        <div class="col-xl col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body pb-2">
                    <div class="stat-icon" style="background: rgba(0,123,255,0.1); color: #007bff;">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <p class="stat-label">Amount Received</p>
                    <h4 class="stat-value text-primary mb-0">Rs. {{ number_format($totalCollected, 2) }}</h4>
                    <p class="stat-sub text-muted mb-0">Payments received</p>
                </div>
                <div style="height: 4px; background: #007bff;"></div>
            </div>
        </div>
        <div class="col-xl col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body pb-2">
                    <div class="stat-icon" style="background: rgba(220,53,69,0.1); color: #dc3545;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <p class="stat-label">Pending</p>
                    <h4 class="stat-value text-danger mb-0">Rs. {{ number_format($totalDue, 2) }}</h4>
                    <p class="stat-sub text-muted mb-0">Yet to collect</p>
                </div>
                <div style="height: 4px; background: #dc3545;"></div>
            </div>
        </div>
    </div>

    {{-- Chart + Status --}}
    <div class="row mb-4">
        @if($filterMode !== 'day')
        <div class="col-xl-8 mb-3">
            <div class="card fin-card shadow-sm">
                <div class="card-body">
                    <h5 class="section-title mb-3">
                        <i class="fas fa-chart-bar" style="background: rgba(0,123,255,0.1); color: #007bff;"></i>
                        Daily Revenue
                    </h5>
                    <canvas id="dailyRevenueChart" height="100"></canvas>
                </div>
            </div>
        </div>
        @endif
        <div class="{{ $filterMode === 'day' ? 'col-xl-12' : 'col-xl-4' }} mb-3">
            <div class="card fin-card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="section-title mb-3">
                        <i class="fas fa-clipboard-list" style="background: rgba(0,123,255,0.1); color: #007bff;"></i>
                        Revenue by Status
                    </h5>
                    @forelse($revenueByStatus as $status)
                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded" style="background: rgba(0,0,0,0.02); border: 1px solid #f1f3f8;">
                        <div>
                            <span class="status-pill" style="background: {{ $status->status == 'Paid' ? 'rgba(40,167,69,0.1)' : ($status->status == 'Pending' ? 'rgba(255,193,7,0.12)' : 'rgba(220,53,69,0.1)') }}; color: {{ $status->status == 'Paid' ? '#28a745' : ($status->status == 'Pending' ? '#e0a800' : '#dc3545') }};">
                                <i class="fas {{ $status->status == 'Paid' ? 'fa-check-circle' : ($status->status == 'Pending' ? 'fa-clock' : 'fa-times-circle') }}"></i>
                                {{ $status->status ?? 'Unknown' }}
                            </span>
                            <small class="text-muted ml-2">{{ $status->count }} bills</small>
                        </div>
                        <strong style="font-size: 0.95rem;">Rs. {{ number_format($status->total, 2) }}</strong>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block" style="opacity: 0.3;"></i>No data
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Top Tests --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card fin-card shadow-sm">
                <div class="card-body">
                    <h5 class="section-title mb-3">
                        <i class="fas fa-flask" style="background: rgba(40,167,69,0.1); color: #28a745;"></i>
                        Top Tests by Revenue
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-fin mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Test Name</th>
                                    <th>Times Ordered</th>
                                    <th>Total Revenue</th>
                                    <th>Avg. Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($testRevenue as $i => $test)
                                <tr>
                                    <td><span class="badge badge-light" style="border-radius: 6px; font-size: 0.82rem;">{{ $i + 1 }}</span></td>
                                    <td class="font-weight-bold">{{ $test['name'] }}</td>
                                    <td>{{ $test['count'] }}</td>
                                    <td class="text-success font-weight-bold">Rs. {{ number_format($test['total'], 2) }}</td>
                                    <td>Rs. {{ $test['count'] > 0 ? number_format($test['total'] / $test['count'], 2) : '0.00' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No test data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Daily Breakdown Table (month view) --}}
    @if($filterMode !== 'day')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card fin-card shadow-sm">
                <div class="card-body">
                    <h5 class="section-title mb-3">
                        <i class="fas fa-calendar-day" style="background: rgba(23,162,184,0.1); color: #17a2b8;"></i>
                        Daily Breakdown
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-fin mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Bills</th>
                                    <th>After Discount</th>
                                    <th>Amount Received</th>
                                    <th>Pending</th>
                                    <th>Collection %</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dailyRevenue as $day)
                                <tr>
                                    <td class="font-weight-bold">{{ \Carbon\Carbon::parse($day->date)->format('d M, Y (D)') }}</td>
                                    <td>{{ $day->bill_count }}</td>
                                    <td>Rs. {{ number_format($day->revenue, 2) }}</td>
                                    <td class="text-success font-weight-bold">Rs. {{ number_format($day->collected, 2) }}</td>
                                    <td class="text-danger">Rs. {{ number_format($day->due, 2) }}</td>
                                    <td>
                                        @php $pct = $day->revenue > 0 ? round($day->collected / $day->revenue * 100) : 0; @endphp
                                        <div class="progress" style="height: 18px; border-radius: 10px; background: #edf2f9;">
                                            <div class="progress-bar bg-{{ $pct >= 80 ? 'success' : ($pct >= 50 ? 'warning' : 'danger') }}"
                                                 style="width: {{ $pct }}%; border-radius: 10px; font-size: 0.75rem; font-weight: 600;">{{ $pct }}%</div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No data for this month</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

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
        document.getElementById('revenueFilterForm').submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var chartEl = document.getElementById('dailyRevenueChart');
    if (!chartEl) return;
    var dailyData = @json($dailyRevenue);
    new Chart(chartEl.getContext('2d'), {
        type: 'bar',
        data: {
            labels: dailyData.map(d => d.date),
            datasets: [
                {
                    label: 'After Discount',
                    data: dailyData.map(d => d.revenue),
                    backgroundColor: 'rgba(40,167,69,0.65)',
                    borderRadius: 4,
                    borderSkipped: false,
                },
                {
                    label: 'Amount Received',
                    data: dailyData.map(d => d.collected),
                    backgroundColor: 'rgba(0,123,255,0.65)',
                    borderRadius: 4,
                    borderSkipped: false,
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
