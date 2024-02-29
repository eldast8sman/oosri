<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MediaFileController;
use App\Http\Requests\Seller\StoreBusinessRequest;
use App\Models\BusinessAccountDetail;
use App\Models\BusinessSocialMedia;
use App\Models\MediaFile;
use App\Models\Seller;
use App\Models\SellerBusiness;
use App\Models\SellerBusinessSeller;
use Illuminate\Http\Request;

class SellerBusinessController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth:seller-api');
        $this->user = AuthController::user();
    }

    public static function business(SellerBusiness $business) : SellerBusiness
    {
        $business->registration_certificate = !empty($business->registration_certificate) ? MediaFile::find($business->registration_certificate)->url : "";
        $business->account_details = BusinessAccountDetail::where('seller_business_id', $business->id)->first();
        $business->social_media = BusinessSocialMedia::where('seller_business_id', $business->id)->get();
        return $business;
    }

    public function store(StoreBusinessRequest $request){
        $all = $request->except(['registration_certificate']);
        $all['seller_id'] = $this->user->id;

        if(!$business = SellerBusiness::create($all)){
            return response([
                'status' => 'failed',
                'message' => 'Business upload failed'
            ], 409);
        }

        $seller = Seller::find($this->user->id);
        $seller->business_id = $business->id;
        $seller->save();

        if(!empty($request->registration_certificate)){
            if($upload = MediaFileController::upload_file($request->registration_certificate)){
                $business->registration_certificate = $upload->id;
                $business->save();
            }
        }

        SellerBusinessSeller::create([
            'seller_id' => $this->user->id,
            'seller_business_id' => $business->id
        ]);

        return response([
            'status' => 'success',
            'message' => 'Business added successfully',
            'data' => self::business($business)
        ], 200);
    }

    public function index(){
        $businesses = [];
        $sell_businesses = SellerBusinessSeller::where('seller_id', $this->user->id)->get();
        if(empty($sell_businesses)){
            return response([
                'status' => 'failed',
                'message' => 'No Business data has been added yet',
                'data' => []
            ], 200);
        }
        foreach($sell_businesses as $sell_business){
            $business = SellerBusiness::find($sell_business->seller_business_id);
            $businesses[] = self::business($business);
        }

        return response([
            'status' => 'success',
            'message' => 'Businesses fetched successfully',
            'data' => $businesses
        ], 200);
    }

    public function show($slug){
        $business = SellerBusiness::where('slug', $slug)->first();
        if(empty($business)){
            return response([
                'status' => 'failed',
                'message' => 'No Business was fetched'
            ], 404);
        }
        if(SellerBusinessSeller::where("seller_id", $this->user->id)->where('seller_business_id', $business->id)->count() < 1){
            return response([
                'status' => 'failed',
                'message' => 'No Business was fetched'
            ], 404);
        }
        return response([
            'status' => "success",
            'message' => 'Businesses fetched successfully',
            'data' => $business
        ], 200);
    }

    public function switch_business($slug){
        $business = SellerBusiness::where('slug', $slug)->first();
        if(empty($business)){
            return response([
                'status' => 'failed',
                'message' => 'No Business was fetched'
            ], 404);
        }
        if(SellerBusinessSeller::where("seller_id", $this->user->id)->where('seller_business_id', $business->id)->count() < 1){
            return response([
                'status' => 'failed',
                'message' => 'No Business was fetched'
            ], 404);
        }

        $seller = Seller::find($this->user->id);
        $seller->business_id = $business->id;
        $seller->save();

        return response([
            'status' => 'success',
            'message' => "Business switched successfully",
            'data' => AuthController::seller($seller)
        ], 200);
    }
}
