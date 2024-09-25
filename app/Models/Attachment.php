<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $table = 'attachments';

    protected $fillable = [
        'borrower_id',
        'file'
    ];

    public function borrower()
    {
        return $this->belongsTo(Borrower::class);
    }
}
