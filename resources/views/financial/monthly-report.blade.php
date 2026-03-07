@extends('Layout.master')
@section('title', 'Monthly Financial Report')

@section('content')
<style>
    .fin-card { border-radius: 14px; border: none; transition: transform 0.15s ease, box-shadow 0.15s ease; }
    .fin-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important; }
    .stat-card { position: relative; border-radius: 14px; border: none; overflow: hidden; }
    .stat-card .stat-icon { position: absolute; right: 15px; top: 15px; width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; opacity: 0.9; }
    .stat-card .stat-value { font-size: 1.3rem; font-weight: 700; letter-spacing: -0.5px; }
    .stat-card .stat-label { font-size: 0.78rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #8492a6; margin-bottom: 6px; }
    .stat-card .stat-sub { font-size: 0.78rem; margin-top: 4px; }
    .section-title { font-size: 1rem; font-weight: 700; color: #3e4954; margin-bottom: 0; }
    .section-title i { width: 28px; height: 28px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; margin-right: 8px; }
    .table-fin thead th { font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; color: #8492a6; border-top: none; padding: 10px 12px; background: #f8f9fc; }
    .table-fin tbody td { padding: 10px 12px; vertical-align: middle; font-size: 0.88rem; }
    .table-fin tbody tr:hover { background: rgba(0,123,255,0.03); }
    .filter-bar { background: #fff; border-radius: 12px; padding: 12px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border: 1px solid #edf2f9; }
    .pl-table td { padding: 10px 16px !important; font-size: 0.92rem; }
    .pl-table tr.highlight-row { background: rgba(40,167,69,0.05); }
    .pl-table tr.highlight-row-danger { background: rgba(220,53,69,0.04); }
    .report-header { border-radius: 14px; overflow: hidden; background: #fff; color: #3e4954; border: 2px solid #edf2f9; }
    .bottom-line { border-radius: 14px; overflow: hidden; position: relative; }
    .bottom-line .banner-bg { position: absolute; top: 0; right: 0; bottom: 0; width: 200px; opacity: 0.04; font-size: 120px; display: flex; align-items: center; justify-content: center; }

    @media print {
        .left-side-menu, .topnav, .page-title-right, .d-print-none,
        .navbar-custom, #topnav-menu-content, .button-menu-mobile {
            display: none !important;
        }
        .content-page { margin-left: 0 !important; padding: 0 !important; }
        .card, .fin-card, .stat-card { border: 1px solid #ddd !important; box-shadow: none !important; transform: none !important; }
        .report-header { background: #fff !important; border: 1px solid #ddd !important; }
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0 d-print-none">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('financial.dashboard') }}">Financial</a></li>
                        <li class="breadcrumb-item active">Monthly Report</li>
                    </ol>
                </div>
                <h4 class="page-title">Monthly Financial Report</h4>
            </div>
        </div>
    </div>

    {{-- Filter & Print --}}
    <div class="row mb-3 d-print-none">
        <div class="col-12">
            <div class="filter-bar d-flex align-items-center justify-content-between flex-wrap">
                <form method="GET" action="{{ route('financial.monthly-report') }}" class="d-flex align-items-center">
                    <label class="mr-2 mb-0 font-weight-bold text-muted" style="white-space: nowrap; font-size: 0.85rem;">Month:</label>
                    <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm" onchange="this.form.submit()" style="max-width: 180px; border-radius: 8px;">
                </form>
                <button class="btn btn-primary btn-sm" onclick="window.print()" style="border-radius: 8px;">
                    <i class="fas fa-print mr-1"></i>Print Report
                </button>
            </div>
        </div>
    </div>

    {{-- Report Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card report-header shadow-sm">
                <div class="card-body text-center py-4">
                    <h2 class="mb-1" style="font-weight: 800; letter-spacing: -0.5px; color: #3e4954;">Monthly Financial Report</h2>
                    <h4 class="text-muted mb-1">{{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}</h4>
                    <small class="text-muted">{{ $startDate->format('d M, Y') }} &mdash; {{ $endDate->format('d M, Y') }}</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Key Metrics --}}
    <div class="row mb-4">
        <div class="col-xl col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body pb-2">
                    <div class="stat-icon" style="background: rgba(108,117,125,0.1); color: #6c757d;">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <p class="stat-label">Total Bills Amount</p>
                    <h4 class="stat-value mb-0">Rs. {{ number_format($grossRevenue, 2) }}</h4>
                    <p class="stat-sub text-muted mb-0">{{ $billCount }} bills &bull; {{ $patientCount }} patients</p>
                </div>
                <div style="height: 4px; background: #6c757d;"></div>
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
                    <p class="stat-sub text-muted mb-0">Discount: Rs. {{ number_format($totalDiscount, 2) }}</p>
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
                    <p class="stat-sub text-muted mb-0">Pending: Rs. {{ number_format($totalDue, 2) }}</p>
                </div>
                <div style="height: 4px; background: #007bff;"></div>
            </div>
        </div>
        <div class="col-xl col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body pb-2">
                    <div class="stat-icon" style="background: rgba(220,53,69,0.1); color: #dc3545;">
                        <i class="fas fa-money-check-alt"></i>
                    </div>
                    <p class="stat-label">Total Expenses</p>
                    <h4 class="stat-value text-danger mb-0">Rs. {{ number_format($totalOutgoing, 2) }}</h4>
                    <p class="stat-sub text-muted mb-0">&nbsp;</p>
                </div>
                <div style="height: 4px; background: #dc3545;"></div>
            </div>
        </div>
        <div class="col-xl col-md-6 mb-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body pb-2">
                    <div class="stat-icon" style="background: {{ $netProfit >= 0 ? 'rgba(40,167,69,0.1)' : 'rgba(220,53,69,0.1)' }}; color: {{ $netProfit >= 0 ? '#28a745' : '#dc3545' }};">
                        <i class="fas {{ $netProfit >= 0 ? 'fa-chart-line' : 'fa-chart-line' }}"></i>
                    </div>
                    <p class="stat-label">Profit / Loss</p>
                    <h4 class="stat-value {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }} mb-0">Rs. {{ number_format(abs($netProfit), 2) }}</h4>
                    <p class="stat-sub mb-0">
                        <span class="badge py-1 px-2" style="border-radius: 8px; font-size: 0.75rem; background: {{ $netProfit >= 0 ? 'rgba(40,167,69,0.1)' : 'rgba(220,53,69,0.1)' }}; color: {{ $netProfit >= 0 ? '#28a745' : '#dc3545' }};">
                            {{ $netProfit >= 0 ? 'Profit' : 'Loss' }}
                        </span>
                    </p>
                </div>
                <div style="height: 4px; background: {{ $netProfit >= 0 ? '#28a745' : '#dc3545' }};"></div>
            </div>
        </div>
    </div>

    {{-- Income & Expenditure Details --}}
    <div class="row mb-4">
        <div class="col-xl-6 mb-3">
            <div class="card fin-card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="section-title mb-4">
                        <i class="fas fa-arrow-circle-up" style="background: rgba(40,167,69,0.1); color: #28a745;"></i>
                        Income Details
                    </h5>
                    <table class="table table-borderless pl-table mb-0">
                        <tr><td>Total Bills Generated</td><td class="text-right"><strong>{{ $billCount }}</strong></td></tr>
                        <tr><td>Total Patients</td><td class="text-right"><strong>{{ $patientCount }}</strong></td></tr>
                        <tr><td>Total Bills Amount</td><td class="text-right"><strong>Rs. {{ number_format($grossRevenue, 2) }}</strong></td></tr>
                        <tr><td><i class="fas fa-minus-circle text-warning mr-1" style="font-size: 0.75rem;"></i> Less: Discount Given</td><td class="text-right text-warning">- Rs. {{ number_format($totalDiscount, 2) }}</td></tr>
                        <tr class="highlight-row border-top">
                            <td><strong>After Discount</strong></td>
                            <td class="text-right"><strong class="text-success" style="font-size: 1.05rem;">Rs. {{ number_format($netRevenue, 2) }}</strong></td>
                        </tr>
                        <tr><td>&nbsp;&nbsp;<i class="fas fa-check-circle text-success mr-1" style="font-size: 0.8rem;"></i> Amount Received</td><td class="text-right text-success">Rs. {{ number_format($totalCollected, 2) }}</td></tr>
                        <tr><td>&nbsp;&nbsp;<i class="fas fa-clock text-danger mr-1" style="font-size: 0.8rem;"></i> Pending</td><td class="text-right text-danger">Rs. {{ number_format($totalDue, 2) }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-3">
            <div class="card fin-card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="section-title mb-4">
                        <i class="fas fa-arrow-circle-down" style="background: rgba(220,53,69,0.1); color: #dc3545;"></i>
                        Expenditure Details
                    </h5>
                    <table class="table table-borderless pl-table mb-0">
                        <tr><td><i class="fas fa-receipt mr-1" style="font-size: 0.75rem; color: #8492a6;"></i> Operating Expenses</td><td class="text-right">Rs. {{ number_format($totalExpenses, 2) }}</td></tr>
                        <tr><td><i class="fas fa-wallet mr-1" style="font-size: 0.75rem; color: #8492a6;"></i> Salaries (Total Payable)</td><td class="text-right">Rs. {{ number_format($totalSalaries, 2) }}</td></tr>
                        <tr><td>&nbsp;&nbsp;&nbsp;<span class="text-success">- Salaries Paid</span></td><td class="text-right text-success">Rs. {{ number_format($salariesPaid, 2) }}</td></tr>
                        <tr><td>&nbsp;&nbsp;&nbsp;<span class="text-warning">- Salaries Pending</span></td><td class="text-right text-warning">Rs. {{ number_format($salariesPending, 2) }}</td></tr>
                        <tr><td><i class="fas fa-user-md mr-1" style="font-size: 0.75rem; color: #8492a6;"></i> Doctor Commissions</td><td class="text-right">Rs. {{ number_format($totalDoctorCommissions, 2) }}</td></tr>
                        <tr><td><i class="fas fa-handshake mr-1" style="font-size: 0.75rem; color: #8492a6;"></i> Referral Commissions</td><td class="text-right">Rs. {{ number_format($totalReferralCommissions, 2) }}</td></tr>
                        <tr class="highlight-row-danger border-top">
                            <td><strong>Total Expenses</strong></td>
                            <td class="text-right"><strong class="text-danger" style="font-size: 1.05rem;">Rs. {{ number_format($totalOutgoing, 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Expense Breakdown --}}
    @if($expenseBreakdown->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card fin-card shadow-sm">
                <div class="card-body">
                    <h5 class="section-title mb-3">
                        <i class="fas fa-tags" style="background: rgba(255,193,7,0.12); color: #e0a800;"></i>
                        Expense Breakdown by Category
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-fin mb-0">
                            <thead>
                                <tr><th>Category</th><th>Transactions</th><th>Total Amount</th><th>% of Expenses</th></tr>
                            </thead>
                            <tbody>
                                @foreach($expenseBreakdown as $cat)
                                <tr>
                                    <td class="text-capitalize font-weight-bold">{{ $cat->category }}</td>
                                    <td>{{ $cat->count }}</td>
                                    <td class="font-weight-bold">Rs. {{ number_format($cat->total, 2) }}</td>
                                    <td>
                                        @php $epct = $totalExpenses > 0 ? $cat->total / $totalExpenses * 100 : 0; @endphp
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 mr-2" style="height: 6px; border-radius: 10px; background: #edf2f9;">
                                                <div class="progress-bar" style="width: {{ $epct }}%; border-radius: 10px; background: linear-gradient(90deg, #ffc107, #e0a800);"></div>
                                            </div>
                                            <span style="font-size: 0.82rem; min-width: 40px;">{{ number_format($epct, 1) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Top Referrals --}}
    @if($topReferrals->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card fin-card shadow-sm">
                <div class="card-body">
                    <h5 class="section-title mb-3">
                        <i class="fas fa-handshake" style="background: rgba(32,201,151,0.1); color: #20c997;"></i>
                        Top Referrals by Commission
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-fin mb-0">
                            <thead>
                                <tr><th>#</th><th>Referral Name</th><th>Total Commission</th></tr>
                            </thead>
                            <tbody>
                                @foreach($topReferrals as $i => $ref)
                                <tr>
                                    <td><span class="badge badge-light" style="border-radius: 6px; font-size: 0.82rem;">{{ $i + 1 }}</span></td>
                                    <td class="font-weight-bold">{{ $ref->referral->name ?? 'Unknown' }}</td>
                                    <td class="text-success font-weight-bold">Rs. {{ number_format($ref->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Bottom Line --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bottom-line shadow-sm" style="{{ $netProfit >= 0 ? 'background: linear-gradient(135deg, rgba(40,167,69,0.06), rgba(40,167,69,0.02));' : 'background: linear-gradient(135deg, rgba(220,53,69,0.06), rgba(220,53,69,0.02));' }} border: 2px solid {{ $netProfit >= 0 ? '#28a745' : '#dc3545' }};">
                <div class="card-body text-center py-5 position-relative">
                    <div class="banner-bg">
                        <i class="fas {{ $netProfit >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                    </div>
                    <p class="text-muted mb-2" style="font-size: 0.82rem; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600;">
                        Bottom Line &mdash; {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}
                    </p>
                    <h1 class="{{ $netProfit >= 0 ? 'text-success' : 'text-danger' }} mb-2" style="font-size: 2.8rem; font-weight: 800;">
                        Rs. {{ number_format(abs($netProfit), 2) }}
                    </h1>
                    <span class="badge py-2 px-4" style="border-radius: 20px; font-size: 0.95rem; background: {{ $netProfit >= 0 ? 'rgba(40,167,69,0.1)' : 'rgba(220,53,69,0.1)' }}; color: {{ $netProfit >= 0 ? '#28a745' : '#dc3545' }};">
                        <i class="fas {{ $netProfit >= 0 ? 'fa-thumbs-up' : 'fa-thumbs-down' }} mr-1"></i>
                        {{ $netProfit >= 0 ? 'PROFIT' : 'LOSS' }}
                    </span>
                    <p class="text-muted mt-3 mb-0" style="font-size: 0.82rem;">
                        Generated on {{ now()->format('d M, Y h:i A') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
