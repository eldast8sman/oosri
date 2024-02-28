<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\SetAccountDetailsRequest;
use App\Models\BusinessAccountDetail;
use App\Models\SellerBusiness;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth:seller_api');
        $this->user = AuthController::user();
    }

    public function set_account_details(SetAccountDetailsRequest $request){
        $business = SellerBusiness::find($this->user->business_id);
        if($business->activation_status == 1){
            return response([
                'status' => 'failed',
                'message' => 'You can no longer change the details of a Business when it has been approved. Please reach out to Admin'
            ], 409);
        }

        $all = $request->except(['seller_id', 'seller_business_id']);
        $account = BusinessAccountDetail::where('seller_business_id', $business->id)->first();
        if(!empty($account)){
            $account->update($all);
        } else {
            $all['seller_id'] = $this->user->id;
            $all['seller_business_id'] = $business->id;
            $account = BusinessAccountDetail::create($all);
        }

        return response([
            'status' => 'success',
            'message' => 'Account Details updated successfully',
            'data' => $account
        ], 200);
    }
}
