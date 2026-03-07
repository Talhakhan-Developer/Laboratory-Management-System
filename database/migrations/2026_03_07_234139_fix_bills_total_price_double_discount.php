<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixBillsTotalPriceDoubleDiscount extends Migration
{
    /**
     * Fix bills where markAsPaid incorrectly set total_price = amount - discount.
     * Since amount already has discount subtracted, total_price should equal amount.
     *
     * Also fix any bills where paid_amount > total_price/amount (cap to bill total).
     */
    public function up()
    {
        // Fix 1: Bills where total_price was wrongly set to (amount - discount)
        // These are bills where total_price ≈ amount - discount (double-subtracted)
        $fixed = DB::table('bills')
            ->whereNotNull('total_price')
            ->whereNotNull('discount')
            ->where('discount', '>', 0)
            ->whereRaw('ABS(total_price - (amount - discount)) < 0.01')
            ->whereRaw('total_price < amount')
            ->update([
                'total_price' => DB::raw('amount'),
            ]);

        Log::info("Fix bills double-discount: corrected total_price on {$fixed} bills");

        // Fix 2: Bills where paid_amount exceeds what's owed (total_price or amount)
        // Cap paid_amount to the bill total
        $overpaid = DB::table('bills')
            ->whereRaw('paid_amount > COALESCE(total_price, amount)')
            ->update([
                'paid_amount' => DB::raw('COALESCE(total_price, amount)'),
                'due_amount' => DB::raw('0'),
            ]);

        Log::info("Fix bills overpayment: capped paid_amount on {$overpaid} bills");

        // Fix 3: Recalculate due_amount for all bills
        DB::table('bills')
            ->whereNotNull('total_price')
            ->update([
                'due_amount' => DB::raw('GREATEST(0, total_price - COALESCE(paid_amount, 0))'),
            ]);
    }

    public function down()
    {
        // Data fix - cannot be cleanly reversed
    }
}
