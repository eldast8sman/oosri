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
        $seller = Seller::find($this->user->id);
        if($request->business_type == 'personal'){
            $pers_businesses = SellerBusiness::where('business_type', 'personal')->where('seller_id', $this->user->id)->first();
            if(!empty($pers_businesses)){
                return response([
                    'status' => 'failed',
                    'message' => 'You cannot set up more than one Personal Business'
                ], 409);
            }
            $all = [
                'seller_id' => $this->user->id,
                'business_type' => 'personal',
                'business_name' => $this->user->first_name.' '.$this->user->last_name,
                'email' => $this->user->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'zip_code' => $request->zip_code
            ];

            $business = SellerBusiness::create($all);
            if(!$business){
                return response([
                    'status' => 'failed',
                    'message' => 'Business Addition failed'
                ], 500);
            }

            $seller->government_id_type = $request->government_id_type;
            $upload = MediaFileController::upload_file($request->government_id);
            $seller->government_id = $upload->id;
            $seller->save();            
        } elseif($request->business_type == 'corporate'){
            $all = $request->except(['registration_certificate']);
            $all['seller_id'] = $this->user->id;

            if(!$business = SellerBusiness::create($all)){
                return response([
                    'status' => 'failed',
                    'message' => 'Business upload failed'
                ], 409);
            }

            if($upload = MediaFileController::upload_file($request->registration_certificate)){
                $business->registration_certificate = $upload->id;
                $business->save();
            }
        }

        $seller->business_id = $business->id;
        $seller->business_type = null;
        $seller->save();

        return response([
            'status' => 'success',
            'message' => 'BUsiness added successfully',
            'data' => self::business($business)
        ], 200);
    }

    public function old_store(StoreBusinessRequest $request){
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
        $limit = !empty($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $businesses = SellerBusiness::where('seller_id', $this->user->id)->paginate($limit);
        foreach($businesses as $business){
            $business = self::business($business);
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
