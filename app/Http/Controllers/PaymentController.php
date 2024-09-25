<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Daily;
use App\Models\Loan;
use App\Models\PaymentPerDay;
use App\Models\Payment;
use App\Models\Market;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function transactions()
    {
        // Get the transaction history
        $transactions = Payment::all();

        // Get the market data
        $marketData = Market::all();

        // Return the data as a JSON response
        return response()->json([
            'status' => 'success',
            'transactions' => $transactions,
            'market' => $marketData,
            'title' => 'Transactions History'
        ]);
    }

    public function savePayment(Request $request)
    {
        $request->validate([
            'paymentData.*.amount' => 'required|numeric',
        ]);

        $paymentData = $request->input('paymentData');
        $totalAmount = 0;
        $fullyPaidLoans = [];

        if ($paymentData) {
            foreach ($paymentData as $customerId => $data) {
                $selectedCustomer = $data['selectedCustomer'] ?? null;
                $amount = $data['amount'] ?? null;

                if ($selectedCustomer && $amount) {
                    $loan_id = PaymentPerDay::where('id', $selectedCustomer)->value('loan_id');
                    $date = now()->toDateString();
                    $user = Auth::user()->username;
                    $loan_no = Loan::where('id', $loan_id)->value('loan_no');

                    $this->processPayment($selectedCustomer, $loan_id, $amount, $date, $user, $loan_no);

                    // Check if loan is fully paid
                    $completedLoans = $this->fullPaidCheck($loan_id);
                    if (!empty($completedLoans)) {
                        $fullyPaidLoans[] = $loan_id;
                    }
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'loan_ids' => $fullyPaidLoans
        ]);
    }

    private function processPayment($selectedCustomer, $loan_id, $amount, $date, $user, $loan_no)
    {
        // Insert payment data
        Payment::create([
            'total_amount' => $amount,
            'loan_id' => $loan_id,
            'trans_date' => $date,
            'collector' => $user,
            'loan_no' => $loan_no,
        ]);

        // Update loan data in 'daily' table
        $currentData = Daily::where('date', now()->toDateString())->first();
        Daily::where('date', now()->toDateString())->update([
            'realised' => $currentData->realised + $amount,
            'outstanding' => $currentData->outstanding - $amount,
            'cash' => $currentData->cash + $amount,
        ]);

        // Handle amount comparison
        $emi_amount = PaymentPerDay::where('id', $selectedCustomer)->value('perdaypayment');
        $extra_amount = Loan::where('id', $loan_id)->value('extra_amount');

        if ($emi_amount == $amount) {
            $this->handleEqualAmount($selectedCustomer, $amount);
        } elseif ($emi_amount > $amount) {
            $this->handleGreaterAmount($loan_id, $amount, $extra_amount, $emi_amount);
        } else {
            $this->handleLessAmount($loan_id, $amount, $emi_amount, $extra_amount);
        }
    }

    private function handleEqualAmount($id, $amount)
    {
        PaymentPerDay::where('id', $id)->update(['daily_collect' => $amount]);
    }

    private function handleGreaterAmount($loan_id, $amount, $extra_amount, $emi_amount)
    {
        $now_amount = $extra_amount + $amount;

        if ($emi_amount <= $now_amount) {
            $id = PaymentPerDay::where('loan_id', $loan_id)->whereNull('daily_collect')->value('id');
            PaymentPerDay::where('id', $id)->update(['daily_collect' => $emi_amount]);

            $final_biyog = $now_amount - $emi_amount;
            Loan::where('id', $loan_id)->update(['extra_amount' => $final_biyog]);
        } else {
            Loan::where('id', $loan_id)->update(['extra_amount' => $now_amount]);
        }
    }

    private function handleLessAmount($loan_id, $amount, $emi_amount, $extra_amount)
    {
        $main_amount = $extra_amount + $amount;
        $vag = intval($main_amount / $emi_amount);

        for ($i = 0; $i < $vag; $i++) {
            $id = PaymentPerDay::where('loan_id', $loan_id)->whereNull('daily_collect')->value('id');
            PaymentPerDay::where('id', $id)->update(['daily_collect' => $emi_amount]);
        }

        $gun_A = $vag * $emi_amount;
        $bigog = $main_amount - $gun_A;

        Loan::where('id', $loan_id)->update(['extra_amount' => $bigog]);
    }

    private function fullPaidCheck($loan_id)
    {
        $result = PaymentPerDay::where('loan_id', $loan_id)
            ->whereNotNull('daily_collect')
            ->groupBy('loan_id')
            ->havingRaw('COUNT(id) >= 116')
            ->get();

        return !empty($result) ? ['loan_id' => $loan_id] : [];
    }
}
