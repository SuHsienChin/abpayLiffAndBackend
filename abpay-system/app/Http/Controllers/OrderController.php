<?php

namespace App\Http\Controllers;

use App\Models\Order;


use Illuminate\Http\Request;
use GuzzleHttp\Client; 



class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::all();
        return view('orders.index', compact('orders'));
    }

    public function updateStatus(Request $request)
    {

        $selectedOrders = $request->selected_orders;
        // 處理訂單狀態的更新
        // 檢查是否有選中的訂單
        if ($selectedOrders > 0) {
            foreach ($selectedOrders as $orderId) {
                $newStatus = $request->input("order_status_$orderId");
                // 根據訂單 ID 更新狀態
                Order::where('orderId', $orderId)->update(['status' => $newStatus]);
            }

            return redirect()->route('orders.index')->with('success', '訂單狀態已更新');
        } else {
            return redirect()->route('orders.index')->with('error', '未選擇要更新的訂單');
        }


    }

    public function getOrderDetails($orderId)
    {
        // $order = Order::find($orderId);
        $order = Order::where('orderId', $orderId)->first();
        ;


        $orderDetails = "<fieldset class=\"border p-4\">";
        $orderDetails .= "<legend class=\"w-auto\">下單資料</legend>";
        $orderDetails .= "➤下單日期：<label>" . $order->created_at . "</label></br>";
        $orderDetails .= "➤遊戲名稱：<label id='gameNameText'>" . $order->gameName . "</label></br>";
        $orderDetails .= "➤登入方式：<label id='loginType'>" . $order->logintype . "</label></br>";
        $orderDetails .= "➤遊戲帳號：<label id='gameAccount'>" . $order->acount . "</label></br>";
        $orderDetails .= "➤遊戲密碼：<label id='loginPassword'>" . $order->password . "</label></br>";
        $orderDetails .= "➤伺服器：<label id='serverName'>" . $order->serverName . "</label></br>";
        $orderDetails .= "➤角色名稱：<label id='characters'>" . $order->gameAccountName . "</label></br>";
        $orderDetails .= "➤ID編號：<label>" . $order->gameAccountId . "</label></br>";
        $orderDetails .= "➤下單商品確認：</br></br>";
        $orderDetails .= "<label id='gameItems'>" . $this->processItemsTxt($order->gameItemsName, $order->gameItemCounts, $order->itemsMoney) . "</label></br></br>";
        $orderDetails .= "總計: <label id='sumMoney'>" . $order->sumMoney . "</label> ";
        $orderDetails .= "</fieldset>";

        return response()->json(['orderDetails' => $orderDetails]);
    }

    public function processItemsTxt($gameItemsNames, $gameItemCounts, $itemsMoney)
    {
        $gameItemsNames = explode(',', $gameItemsNames);
        $gameItemCounts = explode(',', $gameItemCounts);
        $itemsMoney = explode(',', $itemsMoney);
        $items = '';
        for ($i = 0; $i < count($gameItemsNames); $i++) {
            $items .= $gameItemsNames[$i] . ' X ' . $gameItemCounts[$i] . ' = ' . $itemsMoney[$i] . '<br />';
        }

        return $items;
    }



    public function orderLists()
    {
        $url = 'http://abpay.tw/get_order_list.php?lineId=U628aae282e484f49fb905ac0d17dd860';
        $client = new \GuzzleHttp\Client(); 
        $request = $client->get($url); 
        $response = $request->getBody()->getContents();
        return $response;

    }


}