<?php

namespace App\Http\Controllers;

use App\Services\APIKiteService;
use Illuminate\Http\Request;

class APIPlayGroudController extends Controller
{
    private $api_service;

    public function __construct() {
       $this->api_service = new APIKiteService();
    }

    public function phone_brands(){
        if(!$brands = $this->api_service->fetch_phone_brands()){
            return response([
                'status' => 'failed',
                'message' => $this->api_service->errors
            ], 500);
        }

        return response([
            'status' => 'success',
            'message' => 'Phone Brands fetched successfully',
            'data' => $brands
        ], 200);
    }

    public function phones_by_brand($brand_id){
        if(!$phones = $this->api_service->phone_by_brand($brand_id)){
            return response([
                'status' => 'failed',
                'message' => $this->api_service->errors
            ], 500);
        }

        return response([
            'status' => 'success',
            'message' => 'Phones fetched successfully',
            'data' => $phones
        ], 200);
    }

    public function phone_details($phone_id){
        if(!$details = $this->api_service->phone_details($phone_id)){
            return response([
                'status' => 'failed',
                'message' => $this->api_service->errors
            ], 500);
        }

        return response([
            'status' => 'success',
            'message' => 'Phones fetched successfully',
            'data' => $details
        ], 200);
    }
}
