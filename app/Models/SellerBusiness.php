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
        'government_id_type',
        'government_id',
        'registration_certificate',
        'email',
        'phone'
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('business_name')
            ->saveSlugsTo('slug');
    }
}
