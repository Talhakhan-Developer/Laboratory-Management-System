<?php

namespace App\Http\Controllers;

use App\Models\Bills;
use App\Models\LabTestCat;
use App\Models\Patients;
use App\Models\MainCompanys;
use App\Models\Payments;
use App\Models\TestReport;
use App\Models\Referrals;
use App\Models\ReferralCommission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class BillsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    return response()->view('Bill.allbills');
    }

    public function allbills(Request $request)
    {
        if ($request->ajax()) {
            $query = Bills::with('patient')->orderBy('id', 'DESC');

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->orderColumn('DT_RowIndex', 'id $1')
                ->addColumn('patient_id', function ($item) {
                    return optional($item->patient)->patient_id ?? 'N/A';
                })
                ->addColumn('patient_name', function ($item) {
                    return optional($item->patient)->name ?? 'N/A';
                })
                ->addColumn('billing_date', function ($item) {
                    return $item->created_at ? $item->created_at->format('d-m-Y') : 'N/A';
                })
                ->addColumn('all_test', function ($item) {
                    $all_test = json_decode($item->all_test);
                    $all_test_name = [];
                    if ($all_test) {
                        foreach ($all_test as $test) {
                            $all_test_name[] = $test->test_name;
                        }
                    }
                    return implode(', ', $all_test_name);
                })
                ->addColumn('status', function ($item) {
                    return ucfirst((string)($item->status ?? 'unpaid'));
                })
                ->addColumn('paid_amount', function ($item) {
                    // prefer stored paid_amount, otherwise sum payments
                    $paid = $item->paid_amount ?? Payments::where('bill_id', $item->id)->sum('amount');
                    return number_format((float)$paid, 2);
                })
                ->addColumn('tests_completed', function ($item) {
                    // Simple heuristic: if there are any test reports for this patient, mark as Complete
                    $hasReports = TestReport::where('patient_id', $item->patient_id)->exists();
                    return $hasReports ? 'Complete' : 'Pending';
                })
                ->addColumn('action', function ($row) {
                    $btn = '&nbsp;&nbsp;<a href="' . route("billing.details", $row->id) . '" class="btn btn-info btn-sm detailsview" data-id="' . $row->id . '"><i class="fas fa-eye"></i></a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('Bill.allbills');
    }


    public function allbills1(Request $request)
    {
        if ($request->ajax()) {
            $query = Bills::with('patients')->orderBy('id', 'DESC');
            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->orderColumn('DT_RowIndex', 'id $1')
                ->addColumn('patient_id', function ($item) {
                    return $item->patients->patient_id;
                })
                ->addColumn('patient_name', function ($item) {
                    return $item->patients->name;
                })
                ->addColumn('billing_date', function ($item) {
                    return $item->created_at->format('d-m-Y');
                })
                ->addColumn('all_test', function ($item) {
                    $all_test = json_decode($item->all_test);
                    $all_test_name = [];
                    if ($all_test) {
                        foreach ($all_test as $test) {
                            $all_test_name[] = $test->test_name;
                        }
                    }
                    return $all_test_name;
                })
                ->addColumn('action', function ($row) {
                    $btn = '&nbsp&nbsp<a href=' . (route("billing.details", $row->id)) . ' class="btn btn-info btn-sm detailsview" data-id="' . $row->id . '"><i class="fas fa-eye"></i></a>';
                    return $btn;
                })
                ->rawColumns(['patient_id', 'patient_name', 'all_test', 'action', 'billing_date',])
                ->make(true);
        }

        return view('Bill.allbills');
    }

    /**
     * Show the form for creating a new resource.
     * REQUIRED: Patient ID must be provided via URL
     */
    public function create($id)
    {
        try {
            if (empty($id)) {
                return redirect()->route('patients.list')->with('error', 'Patient ID is required.');
            }

            $patient = Patients::findOrFail($id);
            $tests = LabTestCat::all();
            $registeredTests = $this->resolvePatientTests($patient);

            return view('Bill.bills', compact('patient', 'tests', 'registeredTests'));
        } catch (\Exception $e) {
            Log::error('BillsController@create failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('patients.list')->with('error', 'Unable to load billing page.');
        }
    }

    /**
     * Get registered tests for a patient (AJAX endpoint)
     */
    public function getRegisteredTests($patientId)
    {
        try {
            $patient = Patients::findOrFail($patientId);
            $tests = $this->resolvePatientTests($patient);

            return response()->json([
                'tests' => $tests->map(fn($test) => [
                    'id' => $test->id,
                    'name' => $test->cat_name,
                    'department' => $test->department ?? 'N/A',
                    'price' => (float) $test->price,
                ]),
            ]);
        } catch (\Exception $e) {
            Log::error('getRegisteredTests failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Unable to refresh tests'], 500);
        }
    }

    private function resolvePatientTests(Patients $patient)
    {
        $raw = $patient->test_category ?? [];
        if (!is_array($raw)) {
            $raw = json_decode($raw, true) ?: [];
        }

        $ids = [];
        $names = [];

        foreach ($raw as $entry) {
            if (is_array($entry)) {
                if (!empty($entry['id']) && is_numeric($entry['id'])) {
                    $ids[] = (int) $entry['id'];
                }
                if (!empty($entry['cat_name'])) {
                    $names[] = $entry['cat_name'];
                }
            } elseif (is_numeric($entry)) {
                $ids[] = (int) $entry;
            } elseif (is_string($entry) && $entry !== '') {
                $names[] = $entry;
            }
        }

        $ids = array_unique($ids);
        $names = array_unique($names);

        return LabTestCat::query()
            ->when($ids, fn($query) => $query->whereIn('id', $ids))
            ->when($names, fn($query) => $ids
                ? $query->orWhereIn('cat_name', $names)
                : $query->whereIn('cat_name', $names))
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Debug: log the incoming request
            Log::info('Bill store request received', [
                'patient_id' => $request->input('patient_id'),
                'test_ids' => $request->input('id'),
                'test_names' => $request->input('cat_name'),
                'total' => $request->input('total_'),
                'all_inputs' => $request->all(),
            ]);

            $patientId = $request->input('patient_id');
            $testIds = array_filter((array) $request->input('id', []));
            $selectedNames = array_filter((array) $request->input('cat_name', []));
            // use total_ sent by your form
            $total = (float) ($request->input('total_') ?? $request->input('amount') ?? 0);
            $paid  = (float) ($request->input('pay', 0));
            $status = ($paid >= $total && $total > 0) ? 'paid' : 'unpaid';

            if (!$patientId || (empty($testIds) && empty($selectedNames)) || $total <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required fields (patient, tests, or amount). PatientID: ' . $patientId . ', TestIds: ' . count($testIds) . ', TestNames: ' . count($selectedNames) . ', Total: ' . $total
                ], 400);
            }

            $patient = \App\Models\Patients::find($patientId);
            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient not found.'
                ], 404);
            }

            // Fetch selected test details for all_test JSON using IDs
            $tests = [];
            if (!empty($testIds)) {
                $tests = \App\Models\LabTestCat::whereIn('id', $testIds)->get();
            } elseif (!empty($selectedNames)) {
                // Fallback: fetch by names if IDs are missing
                $tests = \App\Models\LabTestCat::whereIn('cat_name', $selectedNames)->get();
            }

            if ($tests->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tests found for the selected items.'
                ], 404);
            }

            // Get test notes if provided
            $testNotes = $request->input('notes', []);

            // Prepare all_test JSON array
            $allTestArr = [];
            $testNames = [];
            foreach ($tests as $index => $test) {
                $allTestArr[] = [
                    'id' => $test->id,
                    'test_name' => $test->cat_name,
                    'test_price' => $test->price,
                    'department' => $test->department ?? null,
                    'notes' => $testNotes[$index] ?? '',
                ];
                $testNames[] = $test->cat_name;
            }

            // Update patient's test_category with test names
            $rawCategories = $patient->test_category ?? [];
            if (!is_array($rawCategories)) {
                $decoded = json_decode($rawCategories, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $rawCategories = $decoded;
                } else {
                    $rawCategories = array_map('trim', explode(',', (string)$rawCategories));
                }
            }
            $merged = array_values(array_unique(array_merge($rawCategories, $testNames)));
            $patient->test_category = json_encode($merged);
            $patient->save();

            // Update or create the single bill row for the patient
            $bill = \App\Models\Bills::updateOrCreate(
                ['patient_id' => $patient->id],
                [
                    'amount' => $total,
                    'status' => $status,
                    'all_test' => json_encode($allTestArr),
                    'updated_at' => now(),
                ]
            );

            // Ensure extended billing fields are set (total_price, paid_amount, discount, due_amount, payment_type)
            try {
                $bill->total_price = $total;
                $paid = (float) ($request->input('pay') ?? 0);
                $bill->paid_amount = $paid;
                $bill->discount = (float) ($request->input('discount') ?? 0);
                $bill->due_amount = $bill->total_price - $bill->paid_amount;
                if ($request->filled('payment_type')) {
                    $bill->payment_type = $request->input('payment_type');
                }
                // generate bill number if not present
                if (empty($bill->bill_no)) {
                    $bill->bill_no = 'INV-' . now()->format('Ymd') . '-' . $bill->id;
                }
                $bill->save();

                // Create or update referral commission if applicable
                try {
                    $this->createOrUpdateReferralCommission($bill);
                } catch (\Exception $e) {
                    Log::warning('Failed to create referral commission: ' . $e->getMessage());
                }

                // If payment provided, create a payments record and update company balance
                if ($paid > 0) {
                    $payment = new Payments();
                    $payment->bill_id = $bill->id;
                    $payment->amount = $paid;
                    $payment->method = $request->input('payment_type') ?? 'Cash';
                    $payment->date = date('Y-m-d');
                    $payment->employee_name = Auth::user()->name ?? null;
                    $payment->save();

                    $company = MainCompanys::where('id', 1)->first();
                    if ($company) {
                        $company->balance = ($company->balance ?? 0) + $paid;
                        $company->save();
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to set extended bill/payment fields: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Bill saved successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('BillsController@store failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'input' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save bill: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $bills = Bills::with('patient', 'referralCommission')->findOrFail($id);
            // Get test IDs from all_test JSON (array of objects with id or test_name)
            $testIds = [];
            $all_test = json_decode($bills->all_test, true);
            if (is_array($all_test)) {
                foreach ($all_test as $test) {
                    if (isset($test['id'])) {
                        $testIds[] = $test['id'];
                    }
                }
            }
            $tests = [];
            if (!empty($testIds)) {
                $tests = \App\Models\LabTestCat::whereIn('id', $testIds)->get();
            }

            // Get referral commission details
            $commissionDetails = $bills->getReferralCommissionDetails();

            return view('Bill.billdetails', compact('bills', 'tests', 'commissionDetails'));
        } catch (\Exception $e) {
            Log::error('Error in BillsController@show: ' . $e->getMessage());
            return redirect()->route('billing')
                ->with('error', 'Bill not found');
        }
    }

    /**
     * Print bill in A4 format
     */
    public function printA4($id)
    {
        try {
            $bills = Bills::with('patient')->findOrFail($id);
            // Get test IDs from all_test JSON
            $testIds = [];
            $all_test = json_decode($bills->all_test, true);
            if (is_array($all_test)) {
                foreach ($all_test as $test) {
                    if (isset($test['id'])) {
                        $testIds[] = $test['id'];
                    }
                }
            }
            $tests = [];
            if (!empty($testIds)) {
                $tests = \App\Models\LabTestCat::whereIn('id', $testIds)->get();
            }

            return view('Bill.bill_print', compact('bills', 'tests'));
        } catch (\Exception $e) {
            Log::error('Error in BillsController@printA4: ' . $e->getMessage());
            return redirect()->route('billing.details', $id)
                ->with('error', 'Unable to print bill');
        }
    }

    /**
     * Print bill in thermal format
     */
    public function printThermal($id)
    {
        try {
            $bills = Bills::with('patient')->findOrFail($id);
            // Get test IDs from all_test JSON
            $testIds = [];
            $all_test = json_decode($bills->all_test, true);
            if (is_array($all_test)) {
                foreach ($all_test as $test) {
                    if (isset($test['id'])) {
                        $testIds[] = $test['id'];
                    }
                }
            }
            $tests = [];
            if (!empty($testIds)) {
                $tests = \App\Models\LabTestCat::whereIn('id', $testIds)->get();
            }

            return view('Bill.bill_print_thermal', compact('bills', 'tests'));
        } catch (\Exception $e) {
            Log::error('Error in BillsController@printThermal: ' . $e->getMessage());
            return redirect()->route('billing.details', $id)
                ->with('error', 'Unable to print bill');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bills $bills)
    {
        try {
            $patient = $bills->patient;
            $tests = LabTestCat::all();
            $companies = MainCompanys::all();

            return view('Bill.edit_bill', compact('bills', 'patient', 'tests', 'companies'));
        } catch (\Exception $e) {
            Log::error('Error in BillsController@edit: ' . $e->getMessage());
            return redirect()->route('billing')
                ->with('error', 'Failed to load bill for editing');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bills $bills)
    {
        try {
            Log::info('Update request received', [
                'bill_id' => $bills->id ?? null,
                'request_data' => $request->all(),
            ]);

            // Defensive: ensure $bills is loaded even if route-model binding failed for any reason
            if (empty($bills) || empty($bills->id)) {
                $routeId = $request->route('bill') ?? $request->route('id');
                if ($routeId) {
                    $bills = Bills::find($routeId);
                }
            }

            if (empty($bills) || empty($bills->id)) {
                Log::error('BillsController@update: Could not resolve bill id from route or model binding.', ['route_param' => $request->route('bill')]);
                return response()->json(['success' => false, 'message' => 'Bill not found for update.'], 404);
            }

            $validated = $request->validate([
                'discount' => 'required|numeric|min:0',
                'total_price' => 'required|numeric|min:0',
                'payment_type' => 'required|string',
                'paid_amount' => 'required|numeric|min:0',
                'due_amount' => 'required|numeric',
            ]);

            Log::info('Validation passed', ['validated_data' => $validated]);

            // Update bill with validated data
            $bills->update($validated);

            // If a payment amount was provided, record only the delta (new payment)
            $paidAmount = (float) ($validated['paid_amount'] ?? 0);
            $totalPrice = (float) ($validated['total_price'] ?? 0);

            if ($paidAmount > 0) {
                DB::beginTransaction();
                try {
                    // Sum existing payments for this bill
                    $existingPaid = (float) Payments::where('bill_id', $bills->id)->sum('amount');

                    // Determine delta to record now
                    $delta = $paidAmount - $existingPaid;
                    if ($delta > 0) {
                        $payment = new Payments();
                        $payment->bill_id = $bills->id;
                        $payment->amount = $delta;
                        $payment->method = $validated['payment_type'] ?? 'Cash';
                        $payment->save();

                        // Update company balance by the delta
                        $company = MainCompanys::where('id', 1)->first();
                        if ($company) {
                            $company->balance = ($company->balance ?? 0) + $delta;
                            $company->save();
                        }
                    }

                    // Recalculate total paid after potential new payment
                    $totalPaid = (float) Payments::where('bill_id', $bills->id)->sum('amount');

                    // If totalPaid covers the bill total, mark paid
                    if ($totalPrice > 0 && $totalPaid >= $totalPrice) {
                        $bills->status = 'paid';
                        $bills->save();
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::warning('Failed to record payment/delta for bill ' . ($bills->id ?? 'unknown') . ': ' . $e->getMessage());
                }
            }

            // Refresh to get updated data
            $bills->refresh();

            Log::info('Bill updated successfully', [
                'bill_id' => $bills->id,
                'updated_discount' => $bills->discount,
                'updated_total_price' => $bills->total_price,
                'updated_payment_type' => $bills->payment_type,
            ]);

            // If AJAX request, return JSON response
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bill details updated successfully',
                    'bill' => $bills
                ]);
            }

            // Otherwise redirect with success message
            return redirect()->route('bills.show', $bills->id)
                ->with('success', 'Bill updated successfully');
        } catch (\Exception $e) {
            Log::error('Error in BillsController@update: ' . $e->getMessage(), [
                'bill_id' => $bills->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            // If AJAX request, return JSON error
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update bill: ' . $e->getMessage()
                ], 500);
            }

            // Otherwise redirect with error message
            return redirect()->back()
                ->with('error', 'Failed to update bill: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bills $bills)
    {
        try {
            $bills->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bill deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in BillsController@destroy: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete bill: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a bill as paid: record the remaining payment (delta), update company balance and set bill status to 'paid'.
     */
    public function markAsPaid(Request $request, Bills $bills)
    {
        try {
            Log::info('markAsPaid request received', ['bill_id' => $bills->id ?? null, 'user_id' => Auth::id() ?? null, 'input' => $request->all()]);
            if (empty($bills) || empty($bills->id)) {
                return response()->json(['success' => false, 'message' => 'Bill not found.'], 404);
            }

            // Determine the bill total to be considered as 'paid'
            // Note: amount already has discount subtracted (form sends total_ = subtotal - discount)
            $totalPrice = (float) ($bills->total_price ?? $bills->amount);
            if ($totalPrice <= 0) {
                return response()->json(['success' => false, 'message' => 'Invalid bill total.'], 400);
            }

            DB::beginTransaction();
            try {
                // Sum existing payments for this bill
                $existingPaid = (float) Payments::where('bill_id', $bills->id)->sum('amount');

                // Amount to record now
                $delta = $totalPrice - $existingPaid;

                if ($delta > 0) {
                    $payment = new Payments();
                    $payment->bill_id = $bills->id;
                    $payment->amount = $delta;
                    $payment->method = $request->input('method', $bills->payment_type ?? 'Cash');
                    $payment->date = date('Y-m-d');
                    $payment->employee_name = Auth::user()->name ?? null;
                    $payment->save();

                    // Update company balance
                    $company = MainCompanys::where('id', 1)->first();
                    if ($company) {
                        $company->balance = ($company->balance ?? 0) + $delta;
                        $company->save();
                    }
                }

                // Recalculate total paid and update bill
                $totalPaid = (float) Payments::where('bill_id', $bills->id)->sum('amount');
                $bills->paid_amount = $totalPaid;
                $bills->due_amount = max(0, $totalPrice - $totalPaid);
                $bills->total_price = $totalPrice;
                if ($totalPaid >= $totalPrice) {
                    $bills->status = 'paid';
                }
                $bills->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Bill marked as paid.',
                    'bill' => $bills
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('markAsPaid failed for bill ' . ($bills->id ?? 'unknown') . ': ' . $e->getMessage());
                return response()->json(['success' => false, 'message' => 'Failed to mark as paid: ' . $e->getMessage()], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error in BillsController@markAsPaid: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Internal error.'], 500);
        }
    }

    public function createTestRequest(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'test_id' => 'required|exists:labtest,id',
        ]);

        // Create pending test with accession
        $accession = 'ACC-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);

        $testReport = TestReport::create([
            'patient_id' => $validated['patient_id'],
            'test_id' => $validated['test_id'],
            'invoice_id' => null, // Link to bill if needed
            'result' => json_encode([
                'status' => 'pending',
                'accession_no' => $accession,
                'created_at' => now()->toISOString(),
            ]),
        ]);

        return response()->json([
            'accession' => $accession,
            'test_report_id' => $testReport->id,
        ]);
    }

    /**
     * Create or update referral commission based on bill
     * Called after bill is created/updated with a referred patient
     */
    private function createOrUpdateReferralCommission(Bills $bill)
    {
        try {
            // Check if patient has a referral
            if (!$bill->patient || !$bill->patient->referred_by) {
                return null;
            }

            // Find referral by name
            $referral = Referrals::where('name', $bill->patient->referred_by)->first();

            if (!$referral || $referral->commission_percentage <= 0) {
                return null;
            }

            $billAmount = $bill->total_price ?? $bill->amount;
            $commissionAmount = $billAmount * ($referral->commission_percentage / 100);

            // Check if commission already exists for this bill
            $existingCommission = ReferralCommission::where('bill_id', $bill->id)->first();

            if ($existingCommission) {
                // Update existing commission
                $existingCommission->update([
                    'bill_amount' => $billAmount,
                    'commission_percentage' => $referral->commission_percentage,
                    'commission_amount' => $commissionAmount,
                ]);
                Log::info('Referral commission updated for bill ' . $bill->id);
                return $existingCommission;
            }

            // Create new commission record
            $testNames = [];
            if ($bill->all_test) {
                $allTests = json_decode($bill->all_test, true);
                if (is_array($allTests)) {
                    $testNames = array_column($allTests, 'test_name');
                }
            }

            $commission = ReferralCommission::create([
                'referral_id' => $referral->id,
                'bill_id' => $bill->id,
                'patient_id' => $bill->patient_id,
                'bill_amount' => $billAmount,
                'commission_percentage' => $referral->commission_percentage,
                'commission_amount' => $commissionAmount,
                'status' => 'pending',
                'notes' => 'Commission for test(s): ' . implode(', ', $testNames),
            ]);

            Log::info('Referral commission created for bill ' . $bill->id . ' with amount: ' . $commissionAmount);
            return $commission;
        } catch (\Exception $e) {
            Log::error('Failed to create referral commission: ' . $e->getMessage(), [
                'bill_id' => $bill->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}

