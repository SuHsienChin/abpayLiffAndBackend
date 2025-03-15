<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../databaseConnection.php';

// DataTables 伺服器端處理
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length = isset($_GET['length']) ? intval($_GET['length']) : 10;
$search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

// 計算總記錄數
$total_query = "SELECT COUNT(*) as count FROM games";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['count'];

// 搜尋條件
$where = '';
if (!empty($search)) {
    $where = " WHERE game_name LIKE '%$search%'";
}

// 計算過濾後的記錄數
$filtered_query = "SELECT COUNT(*) as count FROM games" . $where;
$filtered_result = $conn->query($filtered_query);
$filtered_row = $filtered_result->fetch_assoc();
$filtered_records = $filtered_row['count'];

// 獲取遊戲數據
$query = "SELECT * FROM games" . $where . " LIMIT $start, $length";
$result = $conn->query($query);

$data = [];
while ($row = $result->fetch_assoc()) {
    // 格式化狀態
    $status = $row['status'] ? 
        '<span class="badge badge-success">啟用</span>' : 
        '<span class="badge badge-danger">停用</span>';

    // 格式化操作按鈕
    $actions = 
        '<button type="button" class="btn btn-sm btn-info mr-1" onclick="editGame(' . $row['id'] . ')">編輯</button>' .
        '<button type="button" class="btn btn-sm btn-danger" onclick="deleteGame(' . $row['id'] . ')">刪除</button>';

    $data[] = [
        'game_id' => $row['id'],
        'game_name' => $row['game_name'],
        'status' => $status,
        'updated_at' => date('Y-m-d H:i:s', strtotime($row['updated_at'])),
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