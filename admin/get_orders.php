<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../databaseConnection.php';
$dbConnection = new DatabaseConnection();
$conn = $dbConnection->connect();

// DataTables 伺服器端處理
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length = isset($_GET['length']) ? intval($_GET['length']) : 10;
$search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

// 計算總記錄數
$total_query = "SELECT COUNT(*) as count FROM orders";
$stmt = $conn->prepare($total_query);
$stmt->execute();
$total_row = $stmt->fetch(PDO::FETCH_ASSOC);
$total_records = $total_row['count'];

// 搜尋條件
$where = '';
if (!empty($search)) {
    $where = " WHERE order_id LIKE '%$search%' OR game_name LIKE '%$search%'";
}

// 計算過濾後的記錄數
$filtered_query = "SELECT COUNT(*) as count FROM orders" . $where;
$stmt = $conn->prepare($filtered_query);
if (!empty($search)) {
    $searchValue = "%$search%";
    $stmt->bindParam(':search', $searchValue, PDO::PARAM_STR);
}
$stmt->execute();
$filtered_row = $stmt->fetch(PDO::FETCH_ASSOC);
$filtered_records = $filtered_row['count'];

// 獲取訂單數據
$query = "SELECT * FROM orders" . $where . " LIMIT :start, :length";
$stmt = $conn->prepare($query);
$stmt->bindParam(':start', $start, PDO::PARAM_INT);
$stmt->bindParam(':length', $length, PDO::PARAM_INT);
if (!empty($search)) {
    $stmt->bindParam(':search', $searchValue, PDO::PARAM_STR);
}
$stmt->execute();

$data = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // 格式化狀態
    $status = '';
    switch ($row['status']) {
        case 'pending':
            $status = '<span class="badge badge-warning">處理中</span>';
            break;
        case 'completed':
            $status = '<span class="badge badge-success">已完成</span>';
            break;
        case 'cancelled':
            $status = '<span class="badge badge-danger">已取消</span>';
            break;
    }

    // 格式化操作按鈕
    $actions = '<button type="button" class="btn btn-sm btn-info" onclick="viewOrder(' . $row['id'] . ')">查看</button>';

    $data[] = [
        'order_id' => $row['order_id'],
        'game_name' => $row['game_name'],
        'amount' => number_format($row['amount'], 2),
        'status' => $status,
        'created_at' => date('Y-m-d H:i:s', strtotime($row['created_at'])),
        'actions' => $actions
    ];
}

// 返回 JSON 響應
$response = [
    'draw' => $draw,
    'recordsTotal' => $total_records,
    'recordsFiltered' => $filtered_records,
    'data' => $data
];

header('Content-Type: application/json');
echo json_encode($response);