<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Market extends Model
{
    protected $table = 'market';

    protected $fillable = ['mname'];

    public $timestamps = false;  // Assuming you don't have created_at and updated_at columns
}