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
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender');
            $table->string('dob');
            $table->string('email')->unique();
            $table->string('password');
            $table->integer('profile_photo')->nullable();
            $table->string('location');
            $table->string('verification_token')->nullable();
            $table->string('verification_token_expiry')->nullable();
            $table->string('token')->nullable();
            $table->string('token_expiry')->nullable();
            $table->boolean('email_verified')->default(0);
            $table->integer('business_id')->nullable();
            $table->dateTime('last_login')->nullable();
            $table->dateTime('prev_login')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
