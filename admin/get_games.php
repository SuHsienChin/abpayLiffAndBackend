<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../databaseConnection.php';
$dbConnection = new DatabaseConnection();
$pdo = $dbConnection->connect();

// DataTables 伺服器端處理
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length = isset($_GET['length']) ? intval($_GET['length']) : 10;
$search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

// 定義可排序的列
$columns = ['Sid', 'Name', 'flag', 'UpdateTime'];
$orderBy = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'Sid';


try {
    // 計算總記錄數
    $total_query = "SELECT COUNT(*) as count FROM switch_game_lists";
    $stmt = $pdo->prepare($total_query);
    $stmt->execute();
    $total_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_records = $total_row['count'];

    // 搜尋條件
    $where = '';
    $params = [];
    if (!empty($search)) {
        $where = " WHERE Name LIKE :search";
        $params[':search'] = "%$search%";
    }

    // 計算過濾後的記錄數
    $filtered_query = "SELECT COUNT(*) as count FROM switch_game_lists" . $where;
    $stmt = $pdo->prepare($filtered_query);
    if (!empty($params)) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    $filtered_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $filtered_records = $filtered_row['count'];

    // 獲取遊戲數據
    $query = "SELECT * FROM switch_game_lists" . $where . " LIMIT :start, :length";
    $stmt = $pdo->prepare($query);
    if (!empty($params)) {
        $stmt->bindValue(':search', $params[':search'], PDO::PARAM_STR);
    }
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();

    $data = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // 格式化狀態為可點擊按鈕
    $status_text = $row['flag'] ? '啟用' : '停用';
    $status_class = $row['flag'] ? 'success' : 'danger';
    $new_status = $row['flag'] ? 0 : 1;
    $status = '<button type="button" class="btn btn-sm btn-' . $status_class . ' toggle-status" data-id="' . $row['Sid'] . '" data-status="' . $new_status . '">' . $status_text . '</button>';

    // 格式化操作按鈕
    $actions = 
        '<button type="button" class="btn btn-sm btn-info mr-1" onclick="editGame(' . $row['Sid'] . ')">編輯</button>' .
        '<button type="button" class="btn btn-sm btn-danger" onclick="deleteGame(' . $row['Sid'] . ')">刪除</button>';

    $data[] = [
        'game_id' => $row['Sid'],
        'game_name' => $row['Name'],
        'status' => $status,
        'updated_at' => date('Y-m-d H:i:s', strtotime($row['updateTime'])),
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