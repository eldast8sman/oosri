<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessAccountDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'seller_business_id',
        'bank',
        'account_number',
        'account_name',
        'swift_code',
        'bank_address',
        'balance',
        'total_in',
        'total_out'
    ];
}
