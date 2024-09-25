<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $table = 'loan';

    protected $fillable = [
        'borrower_id',
        'loan_type',
        'principal',
        'terms',
        'terms2',
        'interest',
        'penalty',
        'date_started',
        'maturity_date',
        'monthly',
        'total_amount',
        'notes',
        'status'
    ];

    public function borrower()
    {
        return $this->belongsTo(Borrower::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Define the relationship with transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'loan_id');
    }
}
