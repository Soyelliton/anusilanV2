<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\Address;
use App\Models\Market;
use App\Models\Transaction;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class BorrowersController extends Controller
{
    // Display all borrowers
    public function index()
    {
        $borrowers = Borrower::all();
        return response()->json(['borrowers' => $borrowers], 200);
    }

    // Show the form for creating a new borrower
    public function create()
    {
        $markets = Market::all();
        $loan_types = Loan::distinct('loan_type')->get();
        
        return response()->json([
            'markets' => $markets,
            'loan_types' => $loan_types,
        ], 200);
    }

    // Store a newly created borrower
    public function store(Request $request)
    {
        $request->validate([
            'firstname' => 'required',
            'profileimg' => 'nullable|image|mimes:jpg,png,jpeg,gif|max:2048',
            'attachedfile.*' => 'nullable|file|mimes:jpg,png,jpeg,gif|max:2048',
            // Add other validation rules as necessary
        ]);

        $data = $request->except(['profileimg', 'attachedfile']);
        $data['collector'] = Auth::user()->username;
        $data['time'] = now()->format('Y-m-d');

        if ($request->hasFile('profileimg')) {
            $data['avatar'] = $request->file('profileimg')->store('borrowers', 'public');
        }

        // Store Borrower
        $borrower = Borrower::create($data);

        // Handle address
        $address = [
            'borrower_id' => $borrower->id,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'city' => $request->city,
            'province' => $request->province,
            'zipcode' => $request->zipcode,
            'country' => $request->country,
            'occupation_landmark' => $request->occupation_landmark,
        ];
        Address::create($address);

        // Handle loan creation
        $loanData = [
            'borrower_id' => $borrower->id,
            'loan_type' => $request->loan_type,
            'principal' => $request->principal,
            'terms' => $request->terms,
            'terms2' => $request->terms2,
            'interest' => $request->interest,
            'penalty' => $request->penalty,
            'date_started' => $request->date_started,
            'maturity_date' => $request->maturity_date,
            'monthly' => $request->monthly,
            'total_amount' => $request->total,
            'notes' => $request->notes,
            'status' => 0,
        ];
        $loan = Loan::create($loanData);

        // Handle payments
        if ($request->terms2 == 'day/s') {
            Payment::create([
                'loan_id' => $loan->id,
                'due_date' => now()->addDays($request->terms),
                'due' => $request->principal,
                'p_interest' => $request->principal * ($request->interest / 100),
                'status' => 'Processing',
            ]);
        } else {
            for ($i = 1; $i <= $request->terms; $i++) {
                Payment::create([
                    'loan_id' => $loan->id,
                    'due_date' => now()->addMonths($i),
                    'due' => $request->principal / $request->terms,
                    'p_interest' => $request->principal * ($request->interest / 100),
                    'status' => 'Processing',
                ]);
            }
        }

        // Handle attachments
        if ($request->hasFile('attachedfile')) {
            foreach ($request->file('attachedfile') as $file) {
                $path = $file->store('attachments', 'public');
                $borrower->attachments()->create(['file' => $path]);
            }
        }

        return response()->json(['message' => 'Borrower created successfully'], 201);
    }

    // Edit a borrower's details
    public function edit($id)
    {
        $borrower = Borrower::findOrFail($id);
        $markets = Market::all();
        $loan_types = Loan::distinct('loan_type')->get();

        return response()->json([
            'borrower' => $borrower,
            'markets' => $markets,
            'loan_types' => $loan_types,
        ], 200);
    }

    // Update a borrower
    public function update(Request $request, $id)
    {
        $borrower = Borrower::findOrFail($id);

        $data = $request->except(['profileimg', 'attachedfile']);
        if ($request->hasFile('profileimg')) {
            // Delete old image if exists
            if ($borrower->avatar) {
                Storage::disk('public')->delete($borrower->avatar);
            }
            $data['avatar'] = $request->file('profileimg')->store('borrowers', 'public');
        }

        // Update borrower
        $borrower->update($data);

        // Update address
        $address = $borrower->address;
        $address->update([
            'address1' => $request->address1,
            'address2' => $request->address2,
            'city' => $request->city,
            'province' => $request->province,
            'zipcode' => $request->zipcode,
            'country' => $request->country,
            'occupation_landmark' => $request->occupation_landmark,
        ]);

        // Update loan
        $loan = Loan::where('borrower_id', $borrower->id)->first();
        $loan->update([
            'principal' => $request->principal,
            'monthly' => $request->monthly,
            'total_amount' => $request->total,
        ]);

        // Handle attachments
        if ($request->hasFile('attachedfile')) {
            foreach ($request->file('attachedfile') as $file) {
                $path = $file->store('attachments', 'public');
                $borrower->attachments()->create(['file' => $path]);
            }
        }

        return response()->json(['message' => 'Borrower updated successfully'], 200);
    }

    // Delete an attachment
    public function attachment_delete($id)
    {
        $attachment = Attachment::findOrFail($id);

        // Delete the file from storage
        Storage::disk('public')->delete($attachment->file);

        $attachment->delete();

        return response()->json(['message' => 'Attachment deleted successfully'], 200);
    }

    public function show($id)
    {
        // Fetch the borrower profile
        $borrower = Borrower::with('address')->findOrFail($id);

        // Fetch the loans associated with this borrower
        $loans = Loan::where('borrower_id', $id)->get();

        // Fetch the files associated with this borrower
        $files = $borrower->attachments;

        // Fetch the transactions associated with this borrower
        $transactions = Transaction::whereHas('loan', function($query) use ($id) {
            $query->where('borrower_id', $id);
        })->get();

        // Combine the data and return as JSON
        return response()->json([
            'borrower' => $borrower,
            'loans' => $loans,
            'files' => $files,
            'transactions' => $transactions,
        ]);
    }
}
