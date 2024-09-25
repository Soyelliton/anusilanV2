<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'loan_id',
        'trans_date',
        'total_amount',
        'remaining',
        'type',
        'collector',
        'loan_no',
    ];

    // Relationship with Loan
    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }
}
