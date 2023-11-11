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
        Schema::create('orders', function (Blueprint $table) {
            $table->id()->bigIncrements();
            $table->string('lineId')->comment('客人LINE的ID唯一值');
            $table->string('customerId')->comment('官方LINE的客編');
            $table->string('orderId')->comment('訂單編號');
            $table->string('gameName')->comment('遊戲名稱');
            $table->string('gameItemsName')->comment('遊戲商品名稱複數');
            $table->string('gameItemCounts')->comment('遊戲商品數量複數');
            $table->string('logintype')->comment('登入方式');
            $table->string('acount')->comment('遊戲帳號');
            $table->string('password')->comment('遊戲密碼');
            $table->string('serverName')->comment('遊戲伺服器');
            $table->string('gameAccountName')->comment('遊戲角色名');
            $table->string('gameAccountId')->comment('遊戲裡面的ID');
            $table->string('gameAccountSid')->comment('對應系統的遊戲帳號ID');
            $table->string('customerSid')->comment('對應系統的客人ID'); 
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
        Schema::dropIfExists('orders');
    }
};
