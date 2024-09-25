<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentPerDay extends Model
{
    protected $table = 'paymentperday';
    
    protected $fillable = [
        'loan_id', 'date', 'perdaypayment', 'remainingbalance', 'daily_collect'
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
