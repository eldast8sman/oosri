<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class SellerBusiness extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'business_name',
        'slug',
        'business_type',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'registration_number',
        'description',
        'tin',
        'registration_certificate',
        'email',
        'phone',
        'activation_status'
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('business_name')
            ->saveSlugsTo('slug');
    }
}
