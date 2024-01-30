<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('name');
            $table->string('username', 20)->unique();
            $table->string('email', 180)->unique();
            $table->string('unique_code', 180)->unique();
            $table->tinyInteger('role_module')->default(3);
            $table->bigInteger('role')->default(0);
            $table->integer('status')->default(1);
            $table->string('phone')->nullable();
            $table->tinyInteger('phone_verified')->default(0);
            $table->string('photo')->nullable();
            $table->tinyInteger('g2f_enabled')->default(0);
            $table->string('google2fa_secret')->nullable();
            $table->tinyInteger('email_verified')->default(0);
            $table->string('password');
            $table->string('language')->default('en');
            $table->tinyInteger('email_enabled')->default(0);
            $table->tinyInteger('phone_enabled')->default(0);
            $table->tinyInteger('push_notification_status')->default(1);
            $table->tinyInteger('email_notification_status')->default(1);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
