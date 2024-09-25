<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $table = 'borrower_address';

    protected $fillable = [
        'borrower_id',
        'address1',
        'address2',
        'city',
        'province',
        'zipcode',
        'country',
        'occupation_landmark'
    ];

    public function borrower()
    {
        return $this->belongsTo(Borrower::class);
    }
}
