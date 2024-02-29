<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MediaFileController;
use App\Mail\Admin\BusinessVerificationMail;
use App\Models\BusinessAccountDetail;
use App\Models\Seller;
use App\Models\SellerBusiness;
use App\Models\SellerBusinessSeller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SellerBusinessController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth:admin-api');
        $this->user = AuthController::user();
    }

    public static function business(SellerBusiness $business) : SellerBusiness
    {
        $business->registration_certificate = !empty($business->registration_certificate) ? MediaFileController::fetch_file($business->registration_certificate)->url : "";
        $business->set_account_details = BusinessAccountDetail::where('seller_business_id', $business->id)->first();
        $business->seller = self::seller(Seller::find(SellerBusinessSeller::where('seller_business_id', $business->id)->orderBy('created_at', 'asc')->first()->seller_id));

        return $business;
    }

    public static function seller(Seller $seller) : Seller 
    {
        $seller->profile_photo = !empty($seller->profile_photo) ? MediaFileController::fetch_file($seller->profile_photo)->url : "";
        $seller->government_id = !empty($seller->government_id) ? MediaFileController::fetch_file($seller->government_id)->url : "";

        return $seller;
    }

    public function index(){
        $limit = !empty($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $sortBy = !empty($_GET['sort']) ? (string)$_GET['sort'] : "business_name";
        $order = !empty($_GET['order']) ? (string)$_GET['order'] : "asc";
        $search = !empty($_GET['search']) ? (string)$_GET['search'] : "";

        if(($sortBy != 'country') and ($sortBy != 'email') and ($sortBy != 'business_name')){
            return response([
                'status' => 'failed',
                'message' => 'Wrong Sorting Parametre'
            ], 409);
        }
        if(($order != 'asc') and ($order != 'desc')){
            return response([
                'status' => 'failed',
                'message' => 'Wrong Sorting Order'
            ], 409);
        }

        $businesses = SellerBusiness::orderBy($sortBy, $order);
        if(!empty($search)){
            $businesses = $businesses->where('business_name', 'like', '%'.$search.'%')->orWhere('country', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%');
        }

        $businesses = $businesses->paginate($limit);
        if(!empty($businesses)){
            foreach($businesses as $business){
                $business = self::business($business);
            }
        }

        return response([
            'status' => 'success',
            'message' => 'Businesses fetched successfully',
            'data' => $businesses
        ], 200);
    }

    public function new_businesses(){
        $limit = !empty($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $search = !empty($_GET['search']) ? (string)$_GET['search'] : "";

        $businesses = SellerBusiness::orderBy('created_at', 'desc');
        if(!empty($search)){
            $businesses = $businesses->where('business_name', 'like', '%'.$search.'%')->orWhere('country', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%');
        }

        $businesses = $businesses->paginate($limit);
        if(!empty($businesses)){
            foreach($businesses as $business){
                $business = self::business($business);
            }
        }

        return response([
            'status' => 'success',
            'message' => 'Businesses fetched successfully',
            'data' => $businesses
        ], 200);
    }

    public function pending_businesses(){
        $limit = !empty($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $search = !empty($_GET['search']) ? (string)$_GET['search'] : "";

        $businesses = SellerBusiness::where('verification_status', 0);
        if(!empty($search)){
            $businesses = $businesses->where('business_name', 'like', '%'.$search.'%')->orWhere('country', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%');
        }
        $businesses = $businesses->orderBy('created_at', 'asc');

        $businesses = $businesses->paginate($limit);
        if(!empty($businesses)){
            foreach($businesses as $business){
                $business = self::business($business);
            }
        }

        return response([
            'status' => 'success',
            'message' => 'Businesses fetched successfully',
            'data' => $businesses
        ], 200);
    }

    public function verification(SellerBusiness $business){
        $business->verification_status = ($business->verification_status == 0) ? 1 : 0;
        $business->save();
        $seller = Seller::find(SellerBusinessSeller::where('seller_business_id', $business->id)->orderBy('created_at', 'asc')->first()->seller_id);
        $seller->name = $seller->first_name.' '.$seller->last_name;

        Mail::to($seller)->send(new BusinessVerificationMail($seller->name, $business->business_name, $business->verification_status));

        return response([
            'status' => 'success',
            'message' => 'Operation successful'
        ], 200);
    }
}
