<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MediaFileController;
use App\Http\Requests\Seller\ActivateAccountRequest;
use App\Http\Requests\Seller\CheckPINRequest;
use App\Http\Requests\Seller\ForgotPasswordRequest;
use App\Http\Requests\Seller\LoginRequest;
use App\Http\Requests\Seller\ResetPasswordRequest;
use App\Http\Requests\Seller\SignupRequest;
use App\Mail\Seller\ForgotPasswordMail;
use App\Mail\Seller\ResendActivationMail;
use App\Mail\Seller\SendActivationMail;
use App\Models\MediaFile;
use App\Models\Seller;
use App\Models\SellerBusiness;
use App\Models\SellerBusinessSeller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public $errors;

    public static function seller(Seller $seller): Seller{
        $seller = Seller::find($seller->id);
        $seller->profile_photo = !empty($seller->profile_photo) ? MediaFileController::fetch_file($seller->profile_photo)->url : "";
        $seller->government_id = !empty($seller->government_id) ? MediaFileController::fetch_file($seller->government_id)->url : "";
        if(!empty($seller->business_id)){
            $seller->current_business = SellerBusinessController::business(SellerBusiness::find($seller->business_id));
        }

        $businesses = [];
        $sel_businesses = SellerBusinessSeller::where('seller_id', $seller->id)->orderBy('created_at', 'desc');
        if($sel_businesses->count() > 0){
            foreach($sel_businesses->get() as $business){
                $businesses[] = SellerBusinessController::business(SellerBusiness::find($business->id));
            }
        }
        $seller->businesses = $businesses;
        return $seller;
    }

    public function store(SignupRequest $request){
        $all = $request->except(['profile_photo']);

        if(!$seller = Seller::create($all)){
            return response([
                'status' => 'failed',
                'message' => 'Account creation failed'
            ], 500);
        }

        if(isset($request->profile_photo) and !empty($request->profile_photo)){
            if(!$file = MediaFileController::upload_file($request->profile_photo)){
                $seller->delete();

                return response([
                    'status' => 'failed',
                    'message' => 'Account creation failed'
                ], 500);
            }

            $seller->profile_photo = $file->id;
            $seller->save();
        }

        $token = mt_rand(100000, 999999);
        $seller->verification_token = Crypt::encryptString($token);
        $seller->verification_token_expiry = date('Y-m-d H:i:s', time() + (60 * 30));
        $seller->save();

        $seller->name = $seller->first_name.' '.$seller->last_name;
        Mail::to($seller)->send(new SendActivationMail($seller->name, $token));
        
        $token = auth('seller-api')->login($seller);

        $new_seller = self::seller($seller);
        $new_seller->authorization = [
            'token' => $token,
            'type' => 'Bearer',
            'expires' => date('Y-m-d H:i:s', time() + 60 * 60 * 24)
        ];

        return response([
            'status' => 'success',
            'message' => 'Signup successful',
            'data' => $new_seller
        ], 200);
    }

    public function old_store(SignupRequest $request){
        if(!$seller = Seller::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified' => 0
        ])){
            return response([
                'status' => 'failed',
                'message' => 'Seller Registration Failed'
            ], 500);
        }

        if(!empty($request->government_id_type) and !empty($request->government_id)){
            if($upload = MediaFileController::upload_file($request->government_id)){
                $seller->government_id_type = $request->government_id_type;
                $seller->government_id = $upload->id;
                $seller->save();
            }
        }
        if($request->business_type == "personal"){
            if(!$business = SellerBusiness::create([
                'email' => $seller->email,
                'phone' => $request->phone,
                'business_name' => $seller->first_name.' '.$seller->last_name,
                'business_type' => 'personal',
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => !empty($request->zip_code) ? $request->zip_code : "",
                'description' => !empty($request->description) ? $request->description : ""
            ])){
                $seller->delete();
                return response([
                    'status' => 'failed',
                    'message' => 'Business failed to register'
                ]);
            }

            SellerBusinessSeller::create([
                'seller_id' => $seller->id,
                'seller_business_id' => $business->id
            ]);
            $seller->business_id = $business->id;
            $seller->save();
        }

        $token = mt_rand(100000, 999999);
        $seller->verification_token = Crypt::encryptString($token);
        $seller->verification_token_expiry = date('Y-m-d H:i:s', time() + (60 * 30));
        $seller->save();

        $seller->name = $seller->first_name.' '.$seller->last_name;
        Mail::to($seller)->send(new SendActivationMail($seller->name, $token));
        
        $token = auth('seller-api')->login($seller);

        $new_seller = self::seller($seller);
        $new_seller->authorization = [
            'token' => $token,
            'type' => 'Bearer',
            'expires' => date('Y-m-d H:i:s', time() + 60 * 60 * 24)
        ];

        return response([
            'status' => 'success',
            'message' => 'Signup successful',
            'data' => $new_seller
        ], 200);
    }

    public function login(LoginRequest $request){
        $token = auth('seller-api')->attempt([
            'email' => $request->email,
            'password' => $request->password
        ]);
        if(!$token){
            return response([
                'status' => 'failed',
                'message' => 'Wrong credentials'
            ], 401);
        }

        $seller = self::seller(Seller::where('email', $request->email)->first());
        $seller->authorization = [
            'token' => $token,
            'type' => 'Bearer',
            'expires' => date('Y-m-d H:i:s', time() + 60 * 60 * 24)
        ];

        return response([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => $seller
        ], 200);
    }

    public static function user(){
        return auth('seller-api')->user();
    }

    public function me(){
        $seller = self::seller(Seller::find(self::user()->id));

        return response([
            'status' => 'success',
            'message' => 'User details fetched successfully',
            'data' => $seller
        ], 200);
    }

    public function resend_pin(){
        $seller = Seller::find(self::user()->id);
        if($seller->email_verified == 1){
            return response([
                'status' => 'failed',
                'message' => 'Email already verified'
            ], 409);
        }

        $token = mt_rand(100000, 999999);
        $seller->verification_token = Crypt::encryptString($token);
        $seller->verification_token_expiry = date('Y-m-d H:i:s', time() + (60 * 30));
        $seller->save();

        $seller->name = $seller->first_name.' '.$seller->last_name;
        Mail::to($seller->name)->send(new ResendActivationMail($seller->name, $token));

        return response([
            'status' => 'success',
            'message' => 'Activation PIN has been sent to '.$seller->email
        ], 200);
    }

    public function activate_account(ActivateAccountRequest $request){
        $seller = Seller::find(self::user()->id);
        $present = date('Y-m-d H:i:s');
        if(empty($seller->verification_token_expiry) or ($seller->verification_token_expiry < $present)){
            return response([
                'status' => 'failed',
                'message' => 'Expired Link'
            ], 409);
        }
        $token = Crypt::decryptString($seller->verification_token);
        if($token != $request->pin){
            return response([
                'status' => 'failed',
                'message' => 'Wrong PIN'
            ], 409);
        }

        $seller->email_verified = 1;
        $seller->verification_token = NULL;
        $seller->verification_token_expiry = NULL;
        $seller->save();

        return response([
            'status' => 'success',
            'message' => 'Account activated successfully'
        ], 200);
    }

    public function forgot_password(ForgotPasswordRequest $request){
        $seller = Seller::where('email', $request->email)->first();

        $token = mt_rand(100000, 999999);
        $seller->token = Crypt::encryptString($token);
        $seller->token_expiry = date('Y-m-d H:i:s', time() + 300);
        $seller->save();

        $seller->name = $seller->first_name.' '.$seller->last_name;
        Mail::to($seller)->send(new ForgotPasswordMail($seller->name, $token));

        return response([
            'status' => 'success',
            'message' => 'Password reset PIN sent to '.$seller->email
        ], 200);
    }

    public function pin_check($email, $pin){
        $seller = Seller::where('email', $email)->first();
        if(empty($seller->token)){
            $this->errors = "Wrong PIN";
            $seller->token = NULL;
            $seller->token_expiry = NULL;
            $seller->save();
            return false;

        }
        if(empty($seller->token_expiry) or ($seller->token_expiry < date('Y-m-d H:i:s'))){           
            $this->errors = "Expired PIN";
            $seller->token = NULL;
            $seller->token_expiry = NULL;
            $seller->save();
            return false;
        }
        if(Crypt::decryptString($seller->token) != $pin){
            $this->errors = "Wrong PIN";
            $seller->token = NULL;
            $seller->token_expiry = NULL;
            $seller->save();
            return false;
        }

        return true;
    }

    public function check_pin(CheckPINRequest $request){
        if(!$this->pin_check($request->email, $request->pin)){
            return response([
                'status' => 'failed',
                'message' => $this->errors
            ], 409);
        }

        return response([
            'status' => 'success',
            'message' => 'Valid PIN'
        ], 200);
    }

    public function reset_password(ResetPasswordRequest $request){
        if(!$this->pin_check($request->email, $request->pin)){
            return response([
                'status' => 'failed',
                'message' => $this->errors
            ], 409);
        }

        $seller = Seller::where('email', $request->email)->first();
        $seller->password = Hash::make($request->password);
        $seller->token = NULL;
        $seller->token_expiry = NULL;
        $seller->save();

        return response([
            'status' => 'success',
            'message' => 'Password reset successful'
        ], 200);
    }
}
