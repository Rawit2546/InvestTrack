<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'partner_id',
        'type',
        'amount',
        'date',
        'note',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
