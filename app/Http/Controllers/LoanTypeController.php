<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoanType;

class LoanTypeController extends Controller
{
    // Get all loan types (except id=1)
    public function index()
    {
        $loanTypes = LoanType::where('id', '!=', 1)->get();
        return response()->json([
            'loan_types' => $loanTypes,
        ], 200);
    }

    // Create a new loan type
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'interest' => 'required|integer',
            'terms' => 'required|integer',
            'terms2' => 'nullable|string',
        ]);

        $loanType = LoanType::create($request->all());

        return response()->json([
            'message' => 'Loan type has been created!',
            'loan_type' => $loanType,
        ], 201);
    }

    // Update an existing loan type
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'interest' => 'required|integer',
            'terms' => 'required|integer',
            'terms2' => 'nullable|string',
        ]);

        $loanType = LoanType::find($id);

        if (!$loanType) {
            return response()->json(['message' => 'Loan type not found!'], 404);
        }

        $loanType->update($request->all());

        return response()->json([
            'message' => 'Loan type has been updated!',
            'loan_type' => $loanType,
        ], 200);
    }

    // Delete a loan type
    public function destroy($id)
    {
        $loanType = LoanType::find($id);

        if (!$loanType) {
            return response()->json(['message' => 'Loan type not found!'], 404);
        }

        $loanType->delete();

        return response()->json(['message' => 'Loan type has been deleted!'], 200);
    }
}
