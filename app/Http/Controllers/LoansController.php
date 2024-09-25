<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Borrower;
use App\Models\PaymentPerDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoansController extends Controller
{
    // Display a list of loans
    public function index()
    {
        $borrowers = Borrower::all();
        $loanTypes = Loan::select('loan_type')->distinct()->get();
        $loans = Loan::with('borrower')->where('status', 1)->orderByRaw('CAST(id AS UNSIGNED) DESC')->get();
        $markets = DB::table('market')->get();  // Assuming 'market' is a separate table

        return response()->json([
            'borrowers' => $borrowers,
            'loan_types' => $loanTypes,
            'loans' => $loans,
            'market' => $markets
        ]);
    }

    // Approve a loan and generate daily payment schedule
    public function approve(Request $request)
    {
        $id = $request->input('loan_id');
        $loan = Loan::findOrFail($id);

        $loanAmount = (float)$loan->total_amount;
        $interest = (float)$loan->interest;
        $interestRate = $interest / 100;
        $termInDays = (int)$loan->terms;

        $perDayPayment = $loanAmount / $termInDays;
        $ratePerDay = $interestRate / 365;

        $totalRepayment = $loanAmount;
        $currentDate = $request->input('date_started');
        $date = date('Y-m-d', strtotime($currentDate . ' +1 day'));

        $dataToInsert = [];
        $day = 1;

        while ($day <= $termInDays) {
            if (date('w', strtotime($date)) === '0') { // Skip Sundays
                $date = date('Y-m-d', strtotime($date . ' +1 day'));
                continue;
            }

            if ($totalRepayment < $perDayPayment) {
                $perDayPayment = $totalRepayment;
            }

            $remainingBalance = $totalRepayment - $perDayPayment;

            $dataToInsert[] = [
                'loan_id' => $id,
                'date' => $date,
                'perdaypayment' => $perDayPayment,
                'remainingbalance' => $remainingBalance
            ];

            $totalRepayment = $remainingBalance;
            $date = date('Y-m-d', strtotime($date . ' +1 day'));
            $day++;
        }

        // Insert daily payment schedule
        PaymentPerDay::insert($dataToInsert);

        $maturityDate = date('Y-m-d', strtotime($currentDate . " + $termInDays days"));
        $loanNo = (int)$loan->loan_no + 1;

        $loan->update([
            'date_started' => $currentDate,
            'maturity_date' => $maturityDate,
            'status' => 1,
            'loan_no' => $loanNo
        ]);

        // Fetch current daily loan data
        $currentData = DB::table('daily')->where('date', date('Y-m-d'))->first();
        $principal = $loan->principal;
        $interestAmount = DB::table('payment')->where('loan_id', $id)->value('p_interest');
        $outstanding = $loan->total_amount;

        // Increment values in the 'daily' table
        $updateData = [
            'member_add' => $currentData->member_add + 1,
            'exist_member' => $currentData->exist_member + 1,
            'loan_amount' => $currentData->loan_amount + $principal,
            'interest' => $currentData->interest + $interestAmount,
            'outstanding' => $currentData->outstanding + $outstanding
        ];

        DB::table('daily')->where('date', date('Y-m-d'))->update($updateData);

        return response()->json(['message' => 'Loan has been approved successfully']);
    }

    // Mark a borrower and their loan as dropped out
    public function borrowerDropOut($id)
    {
        $loan = Loan::findOrFail($id);
        $loan->update(['status' => 2]);

        return response()->json(['message' => 'Borrower and Loan have been dropped out']);
    }

    // Delete a loan and reset its fields
    public function delete($id)
    {
        $loan = Loan::findOrFail($id);
        PaymentPerDay::where('loan_id', $id)->delete();

        $loan->update([
            'date_started' => null,
            'maturity_date' => null,
            'status' => 0
        ]);

        return response()->json(['message' => 'Borrower and Loan have been deleted']);
    }
}
