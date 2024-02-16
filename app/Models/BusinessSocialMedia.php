<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessSocialMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'seller_business_id',
        'platform',
        'url'
    ];
}
