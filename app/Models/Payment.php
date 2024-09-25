<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payment';

    protected $fillable = [
        'loan_id',
        'due_date',
        'due',
        'p_interest',
        'status'
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
