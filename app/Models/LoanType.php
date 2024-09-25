<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanType extends Model
{
    use HasFactory;

    protected $table = 'loan_type';

    protected $fillable = [
        'name', 'interest', 'terms', 'terms2'
    ];

    public $timestamps = false;
}
