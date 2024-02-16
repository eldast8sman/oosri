<?php

use App\Models\Seller;
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
        Schema::create('business_social_media', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Seller::class, 'seller_id');
            $table->integer('seller_business_id')->nullable();
            $table->string('platform');
            $table->string('url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_social_media');
    }
};
