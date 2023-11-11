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
        Schema::create('game_account', function (Blueprint $table) {
            $table->id();
            $table->string('sid')->unique();
            $table->string('customer_id');
            $table->string('game_sid');
            $table->string('login_account');
            $table->string('login_password');
            $table->string('login_type');
            $table->string('characters');
            $table->string('server_name');
            $table->string('update_time');
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
        Schema::dropIfExists('game_account');
    }
};
