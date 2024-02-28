<?php

use App\Models\Seller;
use App\Models\SellerBusiness;
use App\Models\User;
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
        Schema::create('business_account_details', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Seller::class, 'seller_id');
            $table->foreignIdFor(SellerBusiness::class, 'seller_business_id');
            $table->string('bank');
            $table->string('account_number');
            $table->string('account_name')->nullable();
            $table->string('swift_code')->nullable();
            $table->text('bank_address')->nullable();
            $table->double('balance')->default(0);
            $table->double('total_in')->default(0);
            $table->double('total_out')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_account_details');
    }
};
