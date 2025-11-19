@extends('Layout.master')
@section('title', 'Dashboard')

@section('content')

    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">BKLT</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Dashboard</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->



        @if (Auth::user()->user_type == 'Admin' || Auth::user()->user_type == 'Super Admin')
            <!-- Quick Links Section -->
            <div class="row mb-4">
                <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                    <a href="{{ route('patients.create') }}" class="text-decoration-none">
                        <div class="card dashboard-card h-100">
                            <div class="card-body card-body dashboard-card-unified">
                                <div class="d-flex align-items-center">
                                    <div class="icon-section">
                                        <i class="fas fa-user-plus dashboard-card-icon"></i>
                                    </div>
                                    <div class="content-section">
                                        <h5 class="card-title dashboard-card-unified">Add Patient</h5>
                                        <p class="dashboard-card-text1">Create new patient</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                    <a href="{{ route('allbills') }}" class="text-decoration-none">
                        <div class="card dashboard-card h-100">
                            <div class="card-body card-body dashboard-card-unified">
                                <div class="d-flex align-items-center">
                                    <div class="icon-section">
                                        <i class="fas fa-file-invoice-dollar dashboard-card-icon"></i>
                                    </div>
                                    <div class="content-section">
                                        <h5 class="card-title dashboard-card-unified">All Bills</h5>
                                        <p class="dashboard-card-text1">View all bills</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <!-- End Quick Links Section -->

            <!-- Stats Cards Section -->
            <div class="row mb-4">
                <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                    <a href="{{ route('patients.list') }}" class="text-decoration-none">
                        <div class="card dashboard-card h-100">
                            <div class="card-body card-body dashboard-card-unified">
                                <div class="d-flex align-items-center">
                                    <div class="icon-section">
                                        <i class="fas fa-user-injured dashboard-card-icon"></i>
                                    </div>
                                    <div class="content-section">
                                        <h5 class="card-title dashboard-card-unified">Patients</h5>
                                        <p class="dashboard-card-text1">{{ App\Models\Patients::get()->count() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                    <a href="{{ route('balance.index') }}" class="text-decoration-none" aria-label="View balance details">
                        <div class="card dashboard-card balance-card-accent h-100 clickable-card" role="link">
                            <div class="card-body card-body dashboard-card-unified">
                                <div class="d-flex align-items-center">
                                    <div class="icon-section">
                                        <i class="fas fa-balance-scale dashboard-card-icon"></i>
                                    </div>
                                    <div class="content-section">
                                        <h5 class="card-title dashboard-card-unified">Company Total Balance</h5>
                                        <p class="dashboard-card-text1">
                                            {{ isset($totalBalance) ? number_format($totalBalance, 2) : '0.00' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                    <a href="{{ route('referrals.patients') }}" class="text-decoration-none">
                        <div class="card dashboard-card h-100">
                            <div class="card-body card-body dashboard-card-unified">
                                <div class="d-flex align-items-center">
                                    <div class="icon-section">
                                        <i class="fas fa-handshake dashboard-card-icon"></i>
                                    </div>
                                    <div class="content-section">
                                        <h5 class="card-title dashboard-card-unified">Referrals</h5>
                                        <p class="dashboard-card-text1">{{ App\Models\Referrals::get()->count() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <!-- End Stats Cards Section -->
            <!-- Today's Finance Stats -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                    <div class="card dashboard-card h-100">
                        <div class="card-body card-body dashboard-card-unified">
                            <div class="d-flex align-items-center">
                                <div class="icon-section">
                                    <i class="fas fa-calendar-day dashboard-card-icon"></i>
                                </div>
                                <div class="content-section">
                                    <h5 class="card-title dashboard-card-unified">Today's Billed</h5>
                                    <p class="dashboard-card-text1">{{ isset($billedToday) ? number_format($billedToday, 2) : '0.00' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                    <div class="card dashboard-card h-100">
                        <div class="card-body card-body dashboard-card-unified">
                            <div class="d-flex align-items-center">
                                <div class="icon-section">
                                    <i class="fas fa-hand-holding-usd dashboard-card-icon"></i>
                                </div>
                                <div class="content-section">
                                    <h5 class="card-title dashboard-card-unified">Today's Paid</h5>
                                    <p class="dashboard-card-text1">{{ isset($paidToday) ? number_format($paidToday, 2) : '0.00' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                    <div class="card dashboard-card h-100">
                        <div class="card-body card-body dashboard-card-unified">
                            <div class="d-flex align-items-center">
                                <div class="icon-section">
                                    <i class="fas fa-file-invoice-dollar dashboard-card-icon"></i>
                                </div>
                                <div class="content-section">
                                    <h5 class="card-title dashboard-card-unified">Today's Due</h5>
                                    <p class="dashboard-card-text1">{{ isset($billedToday, $paidToday) ? number_format(max($billedToday - $paidToday, 0), 2) : '0.00' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
                <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                    <div class="card dashboard-card h-100">
                        <div class="card-body card-body dashboard-card-unified">
                            <div class="d-flex align-items-center">
                                <div class="icon-section">
                                    <i class="fas fa-receipt dashboard-card-icon"></i>
                                </div>
                                <div class="content-section">
                                    <h5 class="card-title dashboard-card-unified">Transactions Today</h5>
                                    <p class="dashboard-card-text1">Bills: {{ $billsCountToday ?? 0 }} | Payments: {{ $paymentsCountToday ?? 0 }} | Commissions: {{ $commissionsCountToday ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body bg-dark">
                    <div class="text-center">
                        <div id="DisplayDate" class="clock mt-5 mb-5" onload="showTime()"></div>
                        <div id="MyClockDisplay" class="clock mt-5 mb-5" onload="showTime()"></div>
                    </div>
                </div>
            </div>
        @endif
        <!-- Charts Row -->
        <div class="row mt-4">
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title dashboard-card-unified mb-0">Monthly Billed vs Paid</h5>
                            <div>
                                <a href="{{ route('dashboard.export', ['type' => 'monthly']) }}" class="btn btn-sm btn-primary me-1" title="Export CSV">
                                    <i class="fas fa-file-csv"></i> CSV
                                </a>
                                <button onclick="downloadChart('chartRevenue', 'monthly_chart')" class="btn btn-sm btn-success" title="Download Image">
                                    <i class="fas fa-download"></i> Image
                                </button>
                            </div>
                        </div>
                        <canvas id="chartRevenue" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title dashboard-card-unified mb-0">Monthly Paid (Payments)</h5>
                            <div>
                                <button onclick="downloadChart('chartPayments', 'monthly_payments_chart')" class="btn btn-sm btn-success" title="Download Image">
                                    <i class="fas fa-download"></i> Image
                                </button>
                            </div>
                        </div>
                        <canvas id="chartPayments" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <!-- Daily Chart Row -->
        <div class="row mt-4">
            <div class="col-lg-12 col-md-12 mb-4">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title dashboard-card-unified mb-0">Daily Billed vs Paid (Last 30 Days)</h5>
                            <div>
                                <a href="{{ route('dashboard.export', ['type' => 'daily']) }}" class="btn btn-sm btn-primary me-1" title="Export CSV">
                                    <i class="fas fa-file-csv"></i> CSV
                                </a>
                                <button onclick="downloadChart('chartDaily', 'daily_chart')" class="btn btn-sm btn-success" title="Download Image">
                                    <i class="fas fa-download"></i> Image
                                </button>
                            </div>
                        </div>
                        <canvas id="chartDaily" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <!-- end row -->


    </div> <!-- container -->

    <script>
        function showTime() {
            var date = new Date();
            // var day = date.getDay(); // 0 - 23
            // var month = date.getMonth(); // 0 - 23
            // var year = date.getYear(); // 0 - 23
            var h = date.getHours(); // 0 - 23
            var m = date.getMinutes(); // 0 - 59
            var s = date.getSeconds(); // 0 - 59
            // var session = "AM";

            // if (h == 0) {
            //     h = 12;
            // }

            // if (h > 12) {
            //     h = h - 12;
            //     session = "PM";
            // }

            h = (h < 10) ? "0" + h : h;
            m = (m < 10) ? "0" + m : m;
            s = (s < 10) ? "0" + s : s;

            // var date1 = day+"-"+month+"-"+year;
            var time = h + ":" + m + ":" + s;
            document.getElementById("MyClockDisplay").innerText = time;
            document.getElementById("MyClockDisplay").textContent = time;
            // document.getElementById("DisplayDate").innerText = date1;
            // document.getElementById("DisplayDate").textContent = date1;


            setTimeout(showTime, 1000);

        }

        showTime();
    </script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        // Store chart instances globally for download functionality
        let chartInstances = {};

        (function() {
            // Data passed from controller
            const labels = @json($chartLabels ?? []);
            const billed = @json($chartBilled ?? []);
            const paid = @json($chartPaid ?? []);
            const dailyLabels = @json($dailyLabels ?? []);
            const dailyBilled = @json($dailyBilled ?? []);
            const dailyPaid = @json($dailyPaid ?? []);

            // Combined chart: billed vs paid
            const ctx = document.getElementById('chartRevenue');
            if (ctx) {
                chartInstances['chartRevenue'] = new Chart(ctx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                                label: 'Billed',
                                data: billed,
                                borderColor: 'rgba(54, 162, 235, 1)',
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                fill: true,
                                tension: 0.3
                            },
                            {
                                label: 'Paid',
                                data: paid,
                                borderColor: 'rgba(75, 192, 192, 1)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                fill: true,
                                tension: 0.3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Payments bar chart
            const ctx2 = document.getElementById('chartPayments');
            if (ctx2) {
                chartInstances['chartPayments'] = new Chart(ctx2.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Payments',
                            data: paid,
                            backgroundColor: 'rgba(75, 192, 192, 0.6)'
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Daily chart: billed vs paid
            const ctx3 = document.getElementById('chartDaily');
            if (ctx3) {
                chartInstances['chartDaily'] = new Chart(ctx3.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: dailyLabels,
                        datasets: [{
                                label: 'Billed',
                                data: dailyBilled,
                                borderColor: 'rgba(255, 99, 132, 1)',
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                fill: true,
                                tension: 0.3
                            },
                            {
                                label: 'Paid',
                                data: dailyPaid,
                                borderColor: 'rgba(54, 162, 235, 1)',
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                fill: true,
                                tension: 0.3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        })();

        // Function to download chart as image
        function downloadChart(chartId, filename) {
            const chart = chartInstances[chartId];
            if (!chart) {
                alert('Chart not found!');
                return;
            }

            // Get the canvas element
            const canvas = document.getElementById(chartId);
            
            // Convert to image and download
            const url = canvas.toDataURL('image/png');
            const link = document.createElement('a');
            link.download = filename + '_' + new Date().toISOString().split('T')[0] + '.png';
            link.href = url;
            link.click();
        }
    </script>
@endsection
