<?php

use App\Models\Seller;
use App\Models\SellerBusiness;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seller_business_sellers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Seller::class, 'seller_id');
            $table->foreignIdFor(SellerBusiness::class, 'seller_business_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_business_sellers');
    }
};
