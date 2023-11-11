<?php
header("Access-Control-Allow-Origin:*");

原文網址：https://kknews.cc/code/pb88nlz.html
// require_once 'getApiJsonClass.php';

// $url = 'http://www.adp.idv.tw/apiTest/GameList';
// $curlRequest = new CurlRequest($url);
// $response = $curlRequest->sendRequest();
// echo $response;
// $data = json_decode($response, true);

// if ($data === null) {
//     die("無法取得API資料");
// }

// header('Content-Type: application/json');
// echo json_encode($data);

echo (geturl('http://www.adp.idv.tw/apiTest/GameList'));

function geturl($url){
    $headerArray =array("Content-type:application/json;","Accept:application/json");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_HTTPHEADER,$headerArray);
    $output = curl_exec($ch);
    curl_close($ch);
    $output = json_decode($output,true);
    return $output;
}