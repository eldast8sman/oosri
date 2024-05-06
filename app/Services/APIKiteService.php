<?php

namespace App\Services;

class APIKiteService 
{
    protected $base_url;
    protected $api_host;
    protected $api_key;

    public $errors;

    public function __construct() {
        $this->base_url = env('APIKITE_BASE_URL');
        $this->api_host = env('APIKITE_API_HOST');
        $this->api_key = env('APIKITE_API_KEY');
    }

    public function make_post_curl($url, $data){

    }

    private function make_get_curl($url){
        $url = $this->base_url.$url;
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "X-RapidAPI-Host: ".$this->api_host,
                'Accept: application/json',
                "X-RapidAPI-Key: ".$this->api_key
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $this->errors = "first ".$err;
            return false;
        }

        if(!$data = json_decode($response, true)){
            $this->errors = "second ".$response;
            return false;
        }

        return $data;
    }

    public function fetch_phone_brands(){
        $url = "/brands";

        if(!$brands = $this->make_get_curl($url)){
            return false;
        }

        return $brands;
    }

    public function phone_by_brand($brand_id){
        $url = "/".$brand_id."/phones";
        if(!$phones = $this->make_get_curl($url)){
            return false;
        }

        return $phones;
    }

    public function phone_details($phone_id){
        $url = "/phones/".$phone_id;

        if(!$phone_details = $this->make_get_curl($url)){
            return false;
        }

        return $phone_details;
    }
}
