<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Bills;
use App\Models\Payments;
use App\Models\MainCompanys;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PatientReceipt extends Model
{
    use HasFactory;

    protected $table = 'patient_receipts';

    protected $fillable = [
        'patient_id',
        'receipt_number',
        'total_amount',
        'tests',
        'status',
        'notes',
        'printed_by',
    ];

    protected $casts = [
        'tests' => 'array',
        'total_amount' => 'float',
    ];

    /**
     * Get the patient associated with this receipt
     */
    public function patient()
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }

    /**
     * Generate unique receipt number (token)
     */
    public static function generateReceiptNumber()
    {
        do {
            // Format: YYYYMMDD + 6 random digits
            $number = date('Ymd') . str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('receipt_number', $number)->exists());

        return $number;
    }

    /**
     * Create receipt from patient registration with prices from form
     */
    public static function createFromPatientWithPrices($patient, $tests = [], $prices = [], $printedBy = null)
    {
        $receipt = new self();
        $receipt->patient_id = $patient->id;
        $receipt->receipt_number = self::generateReceiptNumber();
        $receipt->printed_by = $printedBy;
        
        // Process tests and calculate total
        $totalAmount = 0;
        $testDetails = [];
        
        if (!empty($tests) && is_array($tests)) {
            foreach ($tests as $index => $testName) {
                // Get price from the form data (already validated)
                $price = (float)(($prices[$index] ?? 0));
                
                $totalAmount += $price;
                
                $testDetails[] = [
                    'test_name' => $testName,
                    'price' => $price,
                    'paid_status' => 'unpaid',
                    'discount' => 0,
                ];
            }
        }
        
        $receipt->total_amount = $totalAmount;
        $receipt->tests = $testDetails;
        $receipt->status = 'draft';
        
        return $receipt;
    }

    /**
     * Create receipt from patient registration (fallback method)
     */
    public static function createFromPatient($patient, $tests = [], $printedBy = null)
    {
        $receipt = new self();
        $receipt->patient_id = $patient->id;
        $receipt->receipt_number = self::generateReceiptNumber();
        $receipt->printed_by = $printedBy;
        
        // Process tests and calculate total
        $totalAmount = 0;
        $testDetails = [];
        
        if (!empty($tests) && is_array($tests)) {
            foreach ($tests as $testIdentifier) {
                $price = 0;
                $testName = $testIdentifier;
                
                // Try to find in LabTestCat (test categories/packages) first
                $testCategory = LabTestCat::where('cat_name', $testIdentifier)->first();
                
                if ($testCategory) {
                    $price = (float)($testCategory->price ?? 0);
                    $testName = $testCategory->cat_name;
                } else {
                    // Try as individual test in LabTest
                    $labTest = LabTest::where('test_name', $testIdentifier)->first();
                    
                    if (!$labTest) {
                        // Try partial match in LabTestCat
                        $labTest = LabTestCat::where('cat_name', 'LIKE', '%' . $testIdentifier . '%')->first();
                    }
                    
                    if (!$labTest) {
                        // Try partial match in LabTest
                        $labTest = LabTest::where('test_name', 'LIKE', '%' . $testIdentifier . '%')->first();
                    }
                    
                    if ($labTest) {
                        $price = (float)($labTest->price ?? 0);
                        $testName = $labTest->cat_name ?? $labTest->test_name ?? $testIdentifier;
                    }
                }
                
                $totalAmount += $price;
                
                $testDetails[] = [
                    'test_name' => $testName,
                    'price' => $price,
                    'paid_status' => 'unpaid',
                    'discount' => 0,
                ];
            }
        }
        
        $receipt->total_amount = $totalAmount;
        $receipt->tests = $testDetails;
        $receipt->status = 'draft';
        
        return $receipt;
    }

    /**
     * Get formatted receipt number for barcode display
     */
    public function getFormattedReceiptNumber()
    {
        // Format as: XXXX XXX XXXX for better readability
        $num = $this->receipt_number;
        return substr($num, 0, 8) . ' ' . substr($num, 8);
    }

    /**
     * Get total tests count
     */
    public function getTestCount()
    {
        return is_array($this->tests) ? count($this->tests) : 0;
    }

    /**
     * Get paid tests count
     */
    public function getPaidTestCount()
    {
        if (!is_array($this->tests)) {
            return 0;
        }
        
        return collect($this->tests)->where('paid_status', 'paid')->count();
    }

    /**
     * Mark receipt as paid
     */
    public function markAsPaid()
    {
        // Wrap in transaction to ensure bill & payment creation happens atomically
        DB::beginTransaction();
        try {
            // Ensure tests are marked paid (avoid indirect modification on casted attribute)
            $testsArr = is_array($this->tests) ? $this->tests : (is_string($this->tests) ? json_decode((string)$this->tests, true) : []);
            if (is_array($testsArr)) {
                foreach ($testsArr as &$test) {
                    $test['paid_status'] = 'paid';
                }
            }
            $this->tests = $testsArr;
            $this->status = 'paid';
            $this->save();

            // Create or update related bill for this patient (Bills->patient_id is required for Payments FK)
            $bill = Bills::firstOrCreate(
                ['patient_id' => $this->patient_id],
                [
                    'amount' => $this->total_amount ?? 0,
                    'total_price' => $this->total_amount ?? 0,
                    'paid_amount' => 0,
                    'due_amount' => $this->total_amount ?? 0,
                    'status' => 'unpaid',
                    'all_test' => is_array($this->tests) ? json_encode($this->tests) : ($this->tests ?? '[]')
                ]
            );

            // Update bill with accurate totals if needed
            $bill->total_price = (float)($bill->total_price ?? $this->total_amount ?? 0);
            $bill->amount = (float)($bill->amount ?? $this->total_amount ?? 0);
            $bill->all_test = is_array($this->tests) ? json_encode($this->tests) : ($this->tests ?? '[]');

            // If it's already paid, we won't duplicate payments; compute existing paid
            $existingPaid = (float) Payments::where('bill_id', $bill->id)->sum('amount');
            $totalToPay = (float) ($this->total_amount ?? 0);
            $delta = max(0, $totalToPay - $existingPaid);

            if ($delta > 0) {
                // Create a payment tied to the bill
                $payment = new Payments();
                $payment->bill_id = $bill->id;
                $payment->amount = $delta;
                $payment->method = 'Cash';
                // Save a date if column exists; if not, created_at will be used
                try {
                    if (Schema::hasColumn('payments', 'date')) {
                        $payment->date = date('Y-m-d');
                    }
                    if (Schema::hasColumn('payments', 'employee_name')) {
                        $payment->employee_name = Auth::user()->name ?? ($this->printed_by ?? null);
                    }
                } catch (\Exception $e) {
                    // Graceful fallback when running in some test envs without Schema facade
                }
                $payment->save();

                // Update company balance if company exists
                $company = MainCompanys::where('id', 1)->first();
                if ($company) {
                    $company->balance = ($company->balance ?? 0) + $delta;
                    $company->save();
                }

                // Update bill's paid_amount and due_amount
                $bill->paid_amount = (float) Payments::where('bill_id', $bill->id)->sum('amount');
                $bill->due_amount = max(0, $bill->total_price - $bill->paid_amount);
            }

            // Mark bill as paid if fully paid
            if ($bill->paid_amount >= ($bill->total_price ?? 0) && ($bill->total_price ?? 0) > 0) {
                $bill->status = 'paid';
            }

            $bill->save();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark receipt as paid: ' . $e->getMessage(), ['receipt_id' => $this->id ?? null]);
            return false;
        }
    }

    /**
     * Mark specific test as paid
     */
    public function markTestAsPaid($testName)
    {
        if (is_array($this->tests)) {
            foreach ($this->tests as &$test) {
                if ($test['test_name'] === $testName) {
                    $test['paid_status'] = 'paid';
                }
            }
            return $this->save();
        }
        return false;
    }
}
