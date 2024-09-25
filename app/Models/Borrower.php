<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrower extends Model
{
    use HasFactory;

    protected $table = 'borrower';

    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'f_h_name',
        'gender',
        'birthdate',
        'contact',
        'occupation',
        'occupation_address',
        'remarks',
        'aadhaar',
        'pan',
        'voter',
        'collector',
        'avatar',
        'time'
    ];

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    // Define the relationship with borrower_address
    public function address()
    {
        return $this->hasOne(Address::class, 'borrower_id');
    }
}