<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Daily extends Model
{
    protected $table = 'daily';

    protected $fillable = [
        'market_in',
        'market_out',
        'market_exist',
        'member_add',
        'full_paid',
        'exist_member',
        'loan_amount',
        'interest',
        'realisable',
        'realised',
        'outstanding',
        'cash'
    ];

    public $timestamps = false; // Assuming no timestamps are in this table
}
