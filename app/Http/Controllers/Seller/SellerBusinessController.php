<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\BusinessSocialMedia;
use App\Models\MediaFile;
use App\Models\SellerBusiness;
use Illuminate\Http\Request;

class SellerBusinessController extends Controller
{
    public static function business(SellerBusiness $business) : SellerBusiness
    {
        $business->registration_certificate = !empty($business->registration_certificate) ? MediaFile::find($business->registration_certificate)->url : "";
        $business->social_media = BusinessSocialMedia::where('seller_business_id', $business->id)->get();
        return $business;
    }
}
