@extends('Layout.master')
@section('title', 'Day-wise Balance Report')

@section('content')

    {{-- Page-specific styles to fix text visibility on light backgrounds --}}
    <style>
        /* Balance page card overrides */
        .balance-stat-card {
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: none;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .balance-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }
        .balance-stat-card .card-body {
            padding: 1.25rem;
        }
        .balance-icon-box {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            border-radius: 14px;
        }
        .balance-icon-box i {
            font-size: 1.6rem;
            color: #fff;
        }
        .balance-icon-box.bg-blue    { background: linear-gradient(135deg, #4e73df, #224abe); }
        .balance-icon-box.bg-green   { background: linear-gradient(135deg, #1cc88a, #13855c); }
        .balance-icon-box.bg-red     { background: linear-gradient(135deg, #e74a3b, #be2617); }
        .balance-icon-box.bg-purple  { background: linear-gradient(135deg, #6f42c1, #4e2d8b); }

        .balance-stat-label {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-body, #475569);
            margin-bottom: 4px;
        }
        .balance-stat-value {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--text-heading, #1e293b);
            margin-bottom: 2px;
            line-height: 1.2;
        }
        .balance-stat-sub {
            font-size: 0.78rem;
            color: #6b7280;
        }

        /* Breakdown cards */
        .breakdown-card {
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
        .breakdown-card .card-header {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.85rem 1.15rem;
        }
        .breakdown-card .card-header h5 {
            color: var(--text-heading, #1e293b);
            font-size: 0.95rem;
            font-weight: 600;
        }
        .breakdown-card .card-body {
            padding: 1rem 1.15rem;
        }
        .breakdown-card .card-body strong {
            color: var(--text-heading, #1e293b);
        }
        .breakdown-card .card-body span,
        .breakdown-card .card-body small {
            color: var(--text-body, #475569);
        }
        .breakdown-card .table th {
            color: var(--text-heading, #1e293b);
            font-weight: 600;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 2px solid #e5e7eb;
        }
        .breakdown-card .table td {
            color: var(--text-body, #475569);
            font-size: 0.88rem;
            vertical-align: middle;
        }
        .breakdown-card .accordion-button {
            font-size: 0.88rem;
            font-weight: 500;
            color: var(--text-heading, #1e293b);
        }

        /* Date selector card */
        .date-selector-card {
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }
        .date-selector-card .card-title {
            color: var(--text-heading, #1e293b);
            font-weight: 600;
        }
        .date-selector-card label {
            color: var(--text-body, #475569);
            font-weight: 500;
        }

        /* Selected date banner */
        #selectedDateDisplay {
            color: var(--text-heading, #1e293b) !important;
            font-weight: 600;
        }

        /* Loading text */
        #loadingIndicator p {
            color: var(--text-body, #475569);
        }
    </style>

    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">BKLT</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('balance.index') }}">Balance</a></li>
                            <li class="breadcrumb-item active">Day-wise Balance</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Day-wise Balance Report</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- Date Selection Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card date-selector-card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Select Date</h5>
                        <div class="row align-items-end">
                            <div class="col-md-4 mb-2">
                                <label for="balanceDate" class="form-label">Date</label>
                                <input type="date" class="form-control" id="balanceDate" max="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-8 mb-2">
                                <label class="form-label d-block">Quick Actions</label>
                                <button type="button" class="btn btn-primary btn-sm me-1" onclick="loadQuickDate('yesterday')">
                                    <i class="fas fa-calendar-day"></i> Yesterday
                                </button>
                                <button type="button" class="btn btn-primary btn-sm me-1" onclick="loadQuickDate('2days')">
                                    <i class="fas fa-calendar-alt"></i> 2 Days Ago
                                </button>
                                <button type="button" class="btn btn-primary btn-sm me-1" onclick="loadQuickDate('lastweek')">
                                    <i class="fas fa-calendar-week"></i> Last Week
                                </button>
                                <button type="button" class="btn btn-success btn-sm" onclick="loadBalance()">
                                    <i class="fas fa-search"></i> Load Balance
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div id="loadingIndicator" class="row mb-4" style="display:none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading balance data...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Balance Display Section -->
        <div id="balanceDisplaySection" style="display:none;">
            <!-- Selected Date Header -->
            <div class="row mb-3">
                <div class="col-12">
                    <h5 id="selectedDateDisplay"></h5>
                </div>
            </div>

            <!-- Balance Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card balance-stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="balance-icon-box bg-blue">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <div class="ml-3 pl-3" style="flex:1;">
                                    <div class="balance-stat-label">Billed Amount</div>
                                    <div class="balance-stat-value" id="billedAmountDisplay">0.00</div>
                                    <div class="balance-stat-sub">Bills: <span id="billsCount">0</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card balance-stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="balance-icon-box bg-green">
                                    <i class="fas fa-hand-holding-usd"></i>
                                </div>
                                <div class="ml-3 pl-3" style="flex:1;">
                                    <div class="balance-stat-label">Total Paid</div>
                                    <div class="balance-stat-value" id="totalPaidDisplay">0.00</div>
                                    <div class="balance-stat-sub">Payments: <span id="paymentsCount">0</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card balance-stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="balance-icon-box bg-red">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="ml-3 pl-3" style="flex:1;">
                                    <div class="balance-stat-label">Expenses</div>
                                    <div class="balance-stat-value" id="expensesAmountDisplay">0.00</div>
                                    <div class="balance-stat-sub">Count: <span id="expensesCount">0</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card balance-stat-card h-100" style="border-left: 4px solid #6f42c1;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="balance-icon-box bg-purple">
                                    <i class="fas fa-balance-scale"></i>
                                </div>
                                <div class="ml-3 pl-3" style="flex:1;">
                                    <div class="balance-stat-label">Balance</div>
                                    <div class="balance-stat-value" id="balanceDisplay">0.00</div>
                                    <div class="balance-stat-sub">Paid &minus; Expenses</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Breakdown Section -->
            <div class="row">
                <!-- Payments Breakdown -->
                <div class="col-md-4 mb-3">
                    <div class="card breakdown-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-receipt mr-2" style="color:#1cc88a;"></i> Payments Breakdown
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-2 d-flex justify-content-between">
                                <strong>Patient Payments:</strong>
                                <span id="paymentsAmountBreakdown" style="font-weight:600;color:#1e293b;">0.00</span>
                            </div>
                            <hr>
                            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Patient</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="paymentsTableBody">
                                        <tr>
                                            <td colspan="2" class="text-center" style="color:#6b7280;">No payments found</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commissions Breakdown -->
                <div class="col-md-4 mb-3">
                    <div class="card breakdown-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-handshake mr-2" style="color:#f59e0b;"></i> Commissions Breakdown
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-2 d-flex justify-content-between">
                                <strong style="color:#13855c;">Paid Commissions:</strong>
                                <span id="paidCommissionsAmountBreakdown" style="font-weight:600;color:#13855c;">0.00</span>
                            </div>
                            <div class="mb-2">
                                <small style="color:#6b7280;">Count: <span id="paidCommissionsCountBreakdown">0</span></small>
                            </div>
                            <hr>
                            <div class="mb-2 d-flex justify-content-between">
                                <strong style="color:#d97706;">Pending Commissions:</strong>
                                <span id="pendingCommissionsAmountBreakdown" style="font-weight:600;color:#d97706;">0.00</span>
                            </div>
                            <div class="mb-2">
                                <small style="color:#6b7280;">Count: <span id="pendingCommissionsCountBreakdown">0</span></small>
                            </div>
                            <hr>
                            <div class="accordion" id="commissionsAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingPaid">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapsePaid">
                                            Paid Commissions List
                                        </button>
                                    </h2>
                                    <div id="collapsePaid" class="accordion-collapse collapse" data-bs-parent="#commissionsAccordion">
                                        <div class="accordion-body" style="max-height: 200px; overflow-y: auto;">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Referral</th>
                                                        <th class="text-right">Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="paidCommissionsTableBody">
                                                    <tr>
                                                        <td colspan="2" class="text-center" style="color:#6b7280;">No paid commissions</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingPending">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapsePending">
                                            Pending Commissions List
                                        </button>
                                    </h2>
                                    <div id="collapsePending" class="accordion-collapse collapse" data-bs-parent="#commissionsAccordion">
                                        <div class="accordion-body" style="max-height: 200px; overflow-y: auto;">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Referral</th>
                                                        <th class="text-right">Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="pendingCommissionsTableBody">
                                                    <tr>
                                                        <td colspan="2" class="text-center" style="color:#6b7280;">No pending commissions</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expenses Breakdown -->
                <div class="col-md-4 mb-3">
                    <div class="card breakdown-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-shopping-cart mr-2" style="color:#e74a3b;"></i> Expenses Breakdown
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-2 d-flex justify-content-between">
                                <strong>Total Expenses:</strong>
                                <span id="expensesAmountBreakdown" style="font-weight:600;color:#1e293b;">0.00</span>
                            </div>
                            <hr>
                            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="expensesTableBody">
                                        <tr>
                                            <td colspan="2" class="text-center" style="color:#6b7280;">No expenses found</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- container -->

    <script>
        // Set default date to yesterday
        document.addEventListener('DOMContentLoaded', function() {
            const yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);
            document.getElementById('balanceDate').value = yesterday.toISOString().split('T')[0];
        });

        function loadQuickDate(period) {
            const dateInput = document.getElementById('balanceDate');
            const today = new Date();
            let targetDate;

            switch(period) {
                case 'yesterday':
                    targetDate = new Date(today);
                    targetDate.setDate(today.getDate() - 1);
                    break;
                case '2days':
                    targetDate = new Date(today);
                    targetDate.setDate(today.getDate() - 2);
                    break;
                case 'lastweek':
                    targetDate = new Date(today);
                    targetDate.setDate(today.getDate() - 7);
                    break;
                default:
                    targetDate = today;
            }

            dateInput.value = targetDate.toISOString().split('T')[0];
            loadBalance();
        }

        function loadBalance() {
            const date = document.getElementById('balanceDate').value;

            if (!date) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Date Required',
                    text: 'Please select a date to view balance.',
                });
                return;
            }

            // Show loading, hide results
            document.getElementById('loadingIndicator').style.display = 'block';
            document.getElementById('balanceDisplaySection').style.display = 'none';

            // Make AJAX request
            fetch(`{{ route('balance.day-wise.data') }}?date=${date}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayBalanceData(data);
                } else {
                    throw new Error(data.message || 'Failed to load balance data');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to load balance data. Please try again.',
                });
            })
            .finally(() => {
                document.getElementById('loadingIndicator').style.display = 'none';
            });
        }

        function displayBalanceData(data) {
            const balanceData = data.data;
            const breakdown = data.breakdown;

            // Display selected date
            document.getElementById('selectedDateDisplay').textContent = `Balance for ${data.formatted_date}`;

            // Display main cards
            document.getElementById('billedAmountDisplay').textContent = formatNumber(balanceData.billed_amount);
            document.getElementById('totalPaidDisplay').textContent = formatNumber(balanceData.total_paid);
            document.getElementById('expensesAmountDisplay').textContent = formatNumber(balanceData.expenses_amount);
            document.getElementById('balanceDisplay').textContent = formatNumber(balanceData.balance);

            // Display counts
            document.getElementById('billsCount').textContent = balanceData.bills_count;
            document.getElementById('paymentsCount').textContent = balanceData.payments_count;
            document.getElementById('expensesCount').textContent = balanceData.expenses_count;

            // Display breakdown amounts
            document.getElementById('paymentsAmountBreakdown').textContent = formatNumber(balanceData.payments_amount);
            document.getElementById('paidCommissionsAmountBreakdown').textContent = formatNumber(balanceData.paid_commissions_amount);
            document.getElementById('pendingCommissionsAmountBreakdown').textContent = formatNumber(balanceData.pending_commissions_amount);
            document.getElementById('expensesAmountBreakdown').textContent = formatNumber(balanceData.expenses_amount);
            document.getElementById('paidCommissionsCountBreakdown').textContent = balanceData.paid_commissions_count;
            document.getElementById('pendingCommissionsCountBreakdown').textContent = balanceData.pending_commissions_count;

            // Populate tables
            populatePaymentsTable(breakdown.payments);
            populatePaidCommissionsTable(breakdown.paid_commissions);
            populatePendingCommissionsTable(breakdown.pending_commissions);
            populateExpensesTable(breakdown.expenses);

            // Show results section
            document.getElementById('balanceDisplaySection').style.display = 'block';
        }

        function populatePaymentsTable(payments) {
            const tbody = document.getElementById('paymentsTableBody');
            tbody.innerHTML = '';

            if (payments.length === 0) {
                tbody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">No payments found</td></tr>';
                return;
            }

            payments.forEach(payment => {
                const row = `
                    <tr>
                        <td>${payment.patient_name}</td>
                        <td>${formatNumber(payment.amount)}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        }

        function populatePaidCommissionsTable(commissions) {
            const tbody = document.getElementById('paidCommissionsTableBody');
            tbody.innerHTML = '';

            if (commissions.length === 0) {
                tbody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">No paid commissions</td></tr>';
                return;
            }

            commissions.forEach(commission => {
                const row = `
                    <tr>
                        <td>${commission.referral_name}</td>
                        <td>${formatNumber(commission.amount)}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        }

        function populatePendingCommissionsTable(commissions) {
            const tbody = document.getElementById('pendingCommissionsTableBody');
            tbody.innerHTML = '';

            if (commissions.length === 0) {
                tbody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">No pending commissions</td></tr>';
                return;
            }

            commissions.forEach(commission => {
                const row = `
                    <tr>
                        <td>${commission.referral_name}</td>
                        <td>${formatNumber(commission.amount)}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        }

        function populateExpensesTable(expenses) {
            const tbody = document.getElementById('expensesTableBody');
            tbody.innerHTML = '';

            if (expenses.length === 0) {
                tbody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">No expenses found</td></tr>';
                return;
            }

            expenses.forEach(expense => {
                const row = `
                    <tr>
                        <td>${expense.category}</td>
                        <td>${formatNumber(expense.amount)}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        }

        function formatNumber(number) {
            return parseFloat(number).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    </script>
@endsection
