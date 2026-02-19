<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'investment',
        'dividend_rate',
        'contract_date',
        'pay_date',
        'duration',
        'note',
    ];

    public function partners()
    {
        return $this->hasMany(Partner::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
