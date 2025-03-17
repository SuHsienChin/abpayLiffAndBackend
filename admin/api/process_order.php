<?php
header('Content-Type: application/json');

/*'''
itemId: itemId,
itemCount: itemCount,
itemName: itemName,
version: version,
customerSid: customerData.customer_sid
'''
*/

try {
    // 獲取訂單資料
    $version = $_GET['version'] ?? '';
    $itemId = $_GET['itemId'] ?? '';
    $itemCount = $_GET['itemCount']?? '';
    $itemName = $_GET['itemName'] ?? '';
    $customerSid = $_GET['customerSid']?? '';
    $GameAccount = '';
    $url='http://www.adp.idv.tw/api/Order?UserId=test02&Password=3345678';

    if (!$version ||!$itemId ||!$itemCount ||!$itemName ||!$customerSid) {
        throw new Exception('缺少必要參數');
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'url' => $url . '&Customer=' . $customerSid . '&GameAccount=' . $customerSid . '&Item=' . $itemId . '&Count=' . $itemCount
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}