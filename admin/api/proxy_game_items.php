<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

function fetchGameItems($sid) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://www.adp.idv.tw/api/GameItem?Sid=" . $sid);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return $response;
    }
    return false;
}

$sid = $_GET['sid'] ?? '';
if (!$sid) {
    echo json_encode(['error' => 'Missing Sid parameter']);
    exit;
}

$result = fetchGameItems($sid);
if ($result !== false) {
    echo $result;
} else {
    echo json_encode(['error' => 'Failed to fetch game items']);
}