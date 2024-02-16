<?php

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
        Schema::create('seller_businesses', function (Blueprint $table) {
            $table->id();
            $table->string('business_name');
            $table->string('slug');
            $table->string('business_type');
            $table->string('address');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->nullable();
            $table->string('registration_number')->nullable();
            $table->longText('description')->nullable();
            $table->string('tin')->nullable();
            $table->string('government_id_type')->nullable();
            $table->integer('government_id')->nullable();
            $table->integer('registration_certificate')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_businesses');
    }
};
