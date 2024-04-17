<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ActivateAccountRequest;
use App\Http\Requests\Admin\ChangePasswordRequest;
use App\Http\Requests\Admin\CheckPINRequest;
use App\Http\Requests\Admin\ForgotPasswordRequest;
use App\Http\Requests\Admin\LoginRequest;
use App\Http\Requests\Admin\ResetPasswordRequest;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Mail\Admin\AddAdminMail;
use App\Mail\Admin\ForgotPasswordMail;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private $errors;

    public static function user(){
        return auth('admin-api')->user();
    }

    public function storeAdmin(Request $request){
        $admin = Admin::create([
            'email' => $request->email,
            'name' => $request->name,
            'phone' => $request->phone,
            'role' => $request->role
        ]);

        $admin->verification_token = base64_encode($admin->id."PsychInsights".Str::random(20));
        $admin->verification_token_expiry = date('Y-m-d H:i:s', time() + (60 * 60 * 24));
        $admin->save();

        Mail::to($admin)->send(new AddAdminMail($admin->name, $admin->verification_token));
        return response([
            'status' => 'success',
            'message' => 'Admin added successfully',
            'data' => $admin
        ], 200);
    }

    public function byToken($token){
        $admin = Admin::where('verification_token', $token)->first();
        if(empty($admin)){
            return response([
                'status' => 'failed',
                'message' => 'No Admin Account was fetched'
            ], 404);
        }

        if($admin->verification_token_expiry < date('Y-m-d H:i:s')){
            $admin->verification_token = base64_encode($admin->id."OosriAdmin".Str::random(20));
            $admin->verification_token_expiry = date('Y-m-d H:i:s', time() + (60 * 60 * 24));
            $admin->save();

            Mail::to($admin)->send(new AddAdminMail($admin->name, $admin->verifcation_token));
            return response([
                'status' => 'failed',
                'message' => 'Link has expired. However another link has been sent to '.$admin->email
            ], 404);
        }

        return response([
            'status' => 'success',
            'message' => 'Admin Account fetched successfully',
            'data' => $admin
        ], 200);
    }

    public function activate_account(ActivateAccountRequest $request){
        $admin = Admin::where('verification_token', $request->token)->first();
        if(empty($admin)){
            return response([
                'status' => 'failed',
                'message' => 'No Admin Account was fetched'
            ], 404);
        }

        if($admin->verification_token_expiry < date('Y-m-d H:i:s')){
            $admin->verification_token = base64_encode($admin->id."OosriAdmin".Str::random(20));
            $admin->verification_token_expiry = date('Y-m-d H:i:s', time() + (60 * 60 * 24));
            $admin->save();

            Mail::to($admin)->send(new AddAdminMail($admin->name, $admin->verfication_token));
            return response([
                'status' => 'failed',
                'message' => 'Link has expired. However another link has been sent to '.$admin->email
            ], 404);
        }

        $admin->password = Hash::make($request->password);
        $admin->verification_status = 1;
        $admin->verification_token = null;
        $admin->verification_token_expiry = null;
        $admin->save();

        $auth = $this->login_function($admin->email, $request->password);
        if(!$auth){
            return response([
                'status' => 'failed',
                'message' => $this->errors
            ], 404);
        }

        return response([
            'status' => 'success',
            'message' => 'Account activated successfully',
            'data' => $auth
        ], 200);
    }

    public function login(LoginRequest $request){
        $auth = $this->login_function($request->email, $request->password);
        if(!$auth){
            return response([
                'status' => 'failed',
                'message' => $this->errors
            ], 409);
        }
        return response([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => $auth
        ], 200);
    }

    public function login_function($email, $password){
        $admin = Admin::where('email', $email)->first();
        if(empty($admin)){
            $this->errors = "Wrong Credentials";
            return false;
        }
        if($admin->status != 1){
            $this->errors = "Account is not yet activated";
            return false;
        }
        $token = auth('admin-api')->attempt([
            'email' => $email,
            'password' => $password
        ]);
        if(!$token){
            $this->errors = "Wrong Credentials";
            return false;
        }
        $admin->prev_login = !empty($admin->last_login) ? $admin->last_login : date('Y-m-d H:i:s');
        $admin->last_login = date('Y-m-d H:i:s');
        $admin->save();

        $auth = [
            'token' => $token,
            'type' => 'Bearer',
            'expires' => auth('admin-api')->factory()->getTTL()
        ];

        $admin->authorization = $auth;
        return $admin;
    }

    public function pin_check($email, $pin){
        $admin = Admin::where('email', $email)->first();
        if(empty($admin->token)){
            $this->errors = "Wrong PIN";
            $admin->token = NULL;
            $admin->token_expiry = NULL;
            $admin->save();
            return false;

        }
        if(empty($admin->token_expiry) or ($admin->token_expiry < date('Y-m-d H:i:s'))){           
            $this->errors = "Expired PIN";
            $admin->token = NULL;
            $admin->token_expiry = NULL;
            $admin->save();
            return false;
        }
        if(Crypt::decryptString($admin->token) != $pin){
            $this->errors = "Wrong PIN";
            $admin->token = NULL;
            $admin->token_expiry = NULL;
            $admin->save();
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

    public function forgot_password(ForgotPasswordRequest $request){
        $admin = Admin::where('email', $request->email)->first();
        if($admin->status != 1){
            return response([
                'status' => 'failed',
                'message' => 'Account is not yet activated'
            ], 404);
        }

        $token = mt_rand(100000, 999999);
        $admin->token = Crypt::encryptString($token);
        $admin->token_expiry = date('Y-m-d H:i:s', time() + (60 * 10));
        $admin->save();

        Mail::to($admin)->send(new ForgotPasswordMail($admin->name, $token));
        return response([
            'status' => 'success',
            'message' => 'Password Reset Link sent to '.$admin->email
        ], 200);
    }

    public function reset_password(ResetPasswordRequest $request){
        $admin = Admin::where('email', $request->email)->first();
        if(empty($admin)){
            return response([
                'status'=> 'failed',
                'message' => 'No Admin was fetched'
            ], 404);
        }
        if($admin->status != 1){
            return response([
                'status' => 'failed',
                'message' => 'Account not yet activated'
            ], 409);
        }

        if(!$this->pin_check($request->email, $request->pin)){
            return response([
                'status' => 'failed',
                'message' => $this->errors
            ], 409);
        }

        $admin->password = Hash::make($request->password);
        $admin->token = null;
        $admin->token_expiry = null;
        $admin->save();

        return response([
            'status' => 'success',
            'message' => 'Password reset successfully'
        ], 200);
    }

    public function change_password(ChangePasswordRequest $request){
        $user = self::user();
        if(!$this->login_function($user->email, $request->old_password)){
            return response([
                'status' => 'failed',
                'message' => 'Wrong Password'
            ], 409);
        }
        $admin = Admin::find($user->id);
        $admin->password = Hash::make($request->password);
        $admin->save();
        $admin = $this->login_function($admin->email, $request->password);

        return response([
            'status' => 'success',
            'message' => 'Password changed successfully',
            'data' => $admin
        ], 200);
    }

    public function me(){
        $user = self::user();
        return response([
            'status' => 'success',
            'message' => 'User details fetched successfully',
            'data' => $user
        ], 200);
    }

    public function logout(){
        auth('admin-api')->logout();

        return response([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ], 200);
    }

    public function refreshToken(){
        try {
            $token = auth('admin-api')->refresh();

            return response([
                'status' => 'success',
                'data' => [
                    'token' => $token,
                    'type' => 'Bearer',
                    'expires' => auth('admin-api')->factory()->getTTL() * 60
                ]
            ]);
        } catch(Exception $e){
            return response([
                'status' => 'failed',
                'message' => 'Login Expired'
            ], 410);
        }
    }
}
