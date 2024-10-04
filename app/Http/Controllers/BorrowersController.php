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
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BorrowersController extends Controller
{
    public function index()
    {
        // Load borrowers with their addresses and loans where any loan has a status of 0
        $borrowers = Borrower::whereHas('loans', function ($query) {
            $query->where('status', 0);
        })->with(['addresses', 'loans' => function ($query) {
            $query->where('status', 0); // Only include loans with status 0 in the response
        }])->get();

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
        // Validation rules
        $request->validate([
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'f_h_name' => 'nullable|string|max:255',
            'gender' => 'required|string|in:male,female,other',  // Assuming gender values
            'birthdate' => 'required|date',
            'contact' => 'required|string|max:15',
            'occupation' => 'nullable|string|max:255',
            'occupation_address' => 'nullable|string|max:500',
            'remarks' => 'nullable|string|max:500',
            'aadhaar' => 'nullable|string|max:12|min:12',  // Aadhaar validation
            'pan' => 'nullable|string|max:10|min:10',  // PAN validation
            'voter' => 'nullable|string|max:10|min:10',  // Voter ID validation
            'address1' => 'required|string|max:255',
            'address2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'zipcode' => 'required|string|max:10',
            'country' => 'required|string|max:100',
            'occupation_landmark' => 'nullable|string|max:255',
            'loan_type' => 'required|string|max:100',
            'principal' => 'required|numeric|min:0',
            'terms' => 'required|integer|min:1',
            'terms2' => 'required|string|in:day/s,month/s',
            'interest' => 'required|numeric|min:0|max:100',
            'penalty' => 'nullable|numeric|min:0|max:100',
            'date_started' => 'required|date',
            'maturity_date' => 'required|date|after_or_equal:date_started',
            'monthly' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'profileimg' => 'nullable|image|mimes:jpg,png,jpeg,gif|max:2048',
            'attachedfile.*' => 'nullable|file|mimes:jpg,png,jpeg,gif|max:2048',
        ]);

        $data = $request->except(['profileimg', 'attachedfile']);
        $data['collector'] = "AdminBD";
        $data['time'] = now()->format('Y-m-d');

        // Store profile image
        if ($request->hasFile('profileimg')) {
            $data['avatar'] = $request->file('profileimg')->store('borrowers', 'public');
        }

        // Store borrower data
        $borrower = Borrower::create($data);

        // Store address data
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

        // Store loan data
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

        Log::info('terms value: ' . $request->terms);
        Log::info('terms data type: ' . gettype($request->terms));

        // Handle payments for day/s
        if ($request->terms2 == 'day/s') {
            $terms = (int)$request->terms; // Ensure terms is an integer
            Payment::create([
                'loan_id' => $loan->id,
                'due_date' => Carbon::parse($request->date_started)->addDays($terms)->format('Y-m-d'),
                'due' => $request->principal,
                'p_interest' => $request->principal * ($request->interest / 100),
                'status' => 'Processing',
            ]);
        } 
        // Handle payments for month/s
        else {
            $terms = (int)$request->terms; // Ensure terms is an integer
            for ($i = 1; $i <= $terms; $i++) {
                if ($i == 1) {
                    Payment::create([
                        'loan_id' => $loan->id,
                        'due_date' => Carbon::parse($request->date_started)->addMonths(1)->format('Y-m-d'),
                        'due' => $request->principal / $terms,
                        'p_interest' => $request->principal * ($request->interest / 100),
                        'status' => 'Processing',
                    ]);
                } else {
                    Payment::create([
                        'loan_id' => $loan->id,
                        'due_date' => Carbon::parse($request->date_started)->addMonths($i)->format('Y-m-d'),
                        'due' => $request->principal / $terms,
                        'p_interest' => $request->principal * ($request->interest / 100),
                        'status' => 'Processing',
                    ]);
                }
            }
        }

        // Store attachments
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
