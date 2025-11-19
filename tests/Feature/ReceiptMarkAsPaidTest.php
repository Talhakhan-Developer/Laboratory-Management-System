<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Patients;
use App\Models\PatientReceipt;
use App\Models\Bills;
use App\Models\Payments;

class ReceiptMarkAsPaidTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_receipt_as_paid_creates_bill_and_payment()
    {
        // Create a patient (minimal required fields)
        $patient = Patients::create([
            'name' => 'Test Patient',
            'mobile_phone' => '0123456789',
            'address' => 'Test Addr',
            'gender' => 'Male',
            'age' => '30Y',
            'receiving_date' => now(),
            'reporting_date' => now(),
            'registerd_by' => 'Tester',
        ]);

        // Create a simple receipt
        $receipt = PatientReceipt::createFromPatientWithPrices($patient, ['CBC'], [500], 'TestUser');
        $receipt->save();

        // Sanity check
        $this->assertEquals('draft', $receipt->status);

        // Mark as paid (this should create a bill and a payment)
        $result = $receipt->markAsPaid();
        $receipt->refresh();

        $this->assertTrue($result);
        $this->assertEquals('paid', $receipt->status);

        // Bill should exist for this patient
        $bill = Bills::where('patient_id', $patient->id)->first();
        $this->assertNotNull($bill);
        $this->assertEquals(500, (int)$bill->paid_amount);
        $this->assertEquals('paid', $bill->status);

        // Payment record should exist
        $paymentSum = Payments::where('bill_id', $bill->id)->sum('amount');
        $this->assertEquals(500, (int)$paymentSum);
    }
}
