<?php
/**
 * 訂單佇列狀態監控頁面
 * 此腳本用於顯示 Redis 訂單佇列的狀態和統計信息
 */

// 設置頁面標題
$pageTitle = "訂單佇列狀態監控";

// 引入 Redis 連接類
require_once 'RedisConnection.php';

// 獲取 Redis 連接
try {
    $redis = RedisConnection::getInstance()->getConnection();
    $redisConnected = true;
} catch (Exception $e) {
    $redisConnected = false;
    $redisError = $e->getMessage();
}

// 獲取佇列統計信息
$queueStats = [];
if ($redisConnected) {
    // 獲取佇列長度
    $queueStats['queue_length'] = $redis->lLen('order_queue');
    
    // 獲取已處理訂單數量
    $queueStats['processed_orders'] = $redis->lLen('order_history');
    
    // 獲取最近處理的訂單
    $recentOrders = $redis->lRange('order_history', -10, -1);
    $queueStats['recent_orders'] = [];
    foreach ($recentOrders as $order) {
        $orderData = json_decode($order, true);
        if ($orderData) {
            $queueStats['recent_orders'][] = $orderData;
        }
    }
    
    // 獲取佇列中的訂單
    $pendingOrders = $redis->lRange('order_queue', 0, -1);
    $queueStats['pending_orders'] = [];
    foreach ($pendingOrders as $order) {
        $orderData = json_decode($order, true);
        if ($orderData) {
            $queueStats['pending_orders'][] = $orderData;
        }
    }
    
    // 獲取處理器狀態
    $queueStats['processor_running'] = file_exists('queue_processor.pid');
    if ($queueStats['processor_running']) {
        $pid = file_get_contents('queue_processor.pid');
        $queueStats['processor_pid'] = $pid;
        
        // 檢查進程是否真的在運行
        if (function_exists('posix_kill')) {
            $queueStats['processor_alive'] = posix_kill($pid, 0);
        } else {
            // Windows 環境或 posix 擴展未安裝
            $queueStats['processor_alive'] = 'unknown';
        }
    }
    
    // 獲取日誌文件信息
    $logDir = __DIR__ . '/logs';
    $queueStats['logs'] = [];
    if (is_dir($logDir)) {
        $logFiles = glob($logDir . '/order_queue_*.log');
        foreach ($logFiles as $logFile) {
            $queueStats['logs'][] = [
                'name' => basename($logFile),
                'size' => filesize($logFile),
                'modified' => date('Y-m-d H:i:s', filemtime($logFile))
            ];
        }
        
        // 獲取最新的日誌內容
        $todayLogFile = $logDir . '/order_queue_' . date('Y-m-d') . '.log';
        if (file_exists($todayLogFile)) {
            $queueStats['latest_log'] = tailCustom($todayLogFile, 20);
        }
    }
}

/**
 * 獲取文件最後幾行
 * @param string $filepath 文件路徑
 * @param int $lines 行數
 * @param bool $adaptive 是否自適應行數
 * @return string 文件內容
 */
function tailCustom($filepath, $lines = 1, $adaptive = true) {
    // 打開文件
    $f = @fopen($filepath, "rb");
    if ($f === false) return false;
    
    // 設置緩衝區大小
    $buffer = ($adaptive) ? 4096 : 8192;
    
    // 跳到文件末尾
    fseek($f, -1, SEEK_END);
    
    // 讀取到的行數
    $output = '';
    $chunk = '';
    $linecounter = 0;
    
    // 從文件末尾開始讀取
    while (ftell($f) > 0 && $linecounter < $lines) {
        $seek = min(ftell($f), $buffer);
        fseek($f, -$seek, SEEK_CUR);
        $chunk = fread($f, $seek);
        fseek($f, -$seek, SEEK_CUR);
        
        // 計算換行符數量
        $output = $chunk . $output;
        $linecounter += substr_count($chunk, "\n");
    }
    
    // 關閉文件
    fclose($f);
    
    // 返回最後 $lines 行
    $arr = explode("\n", $output);
    $arr = array_slice($arr, -$lines);
    return implode("\n", $arr);
}

// 檢查是否為 AJAX 請求
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // 返回 JSON 格式的佇列統計信息
    header('Content-Type: application/json');
    echo json_encode($queueStats);
    exit;
}

// 格式化時間
function formatTime($timestamp) {
    return date('Y-m-d H:i:s', $timestamp);
}

// 格式化大小
function formatSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

// 格式化訂單數據
function formatOrderData($orderData) {
    $result = '';
    if (isset($orderData['order_id'])) {
        $result .= '訂單 ID: ' . htmlspecialchars($orderData['order_id']) . '<br>';
    }
    if (isset($orderData['timestamp'])) {
        $result .= '時間: ' . formatTime($orderData['timestamp']) . '<br>';
    }
    if (isset($orderData['status'])) {
        $result .= '狀態: ' . htmlspecialchars($orderData['status']) . '<br>';
    }
    return $result;
}

// 啟動或停止處理器
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'start_processor') {
        // 啟動處理器
        $output = shell_exec('./start_queue_processor.sh 2>&1');
        $actionResult = '啟動處理器: ' . $output;
    } elseif ($_POST['action'] === 'stop_processor') {
        // 停止處理器
        $output = shell_exec('./stop_queue_processor.sh 2>&1');
        $actionResult = '停止處理器: ' . $output;
    } elseif ($_POST['action'] === 'clear_queue') {
        // 清空佇列
        if ($redisConnected) {
            $redis->del('order_queue');
            $actionResult = '佇列已清空';
        } else {
            $actionResult = '無法連接到 Redis';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 20px;
            padding-bottom: 20px;
        }
        .header {
            padding-bottom: 20px;
            margin-bottom: 30px;
            border-bottom: 1px solid #e5e5e5;
        }
        .queue-status {
            margin-bottom: 20px;
        }
        .log-container {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 400px;
            overflow-y: auto;
        }
        .card {
            margin-bottom: 20px;
        }
        .refresh-btn {
            margin-bottom: 20px;
        }
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
        }
        .status-running {
            background-color: #28a745;
        }
        .status-stopped {
            background-color: #dc3545;
        }
        .status-unknown {
            background-color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo $pageTitle; ?></h1>
            <p class="lead">監控 Redis 訂單佇列的狀態和統計信息</p>
        </div>
        
        <?php if (isset($actionResult)): ?>
        <div class="alert alert-info">
            <?php echo $actionResult; ?>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-12">
                <button id="refreshBtn" class="btn btn-primary refresh-btn">
                    <i class="bi bi-arrow-clockwise"></i> 刷新數據
                </button>
                
                <form method="post" class="d-inline">
                    <?php if (!$redisConnected || !isset($queueStats['processor_running']) || !$queueStats['processor_running']): ?>
                    <button type="submit" name="action" value="start_processor" class="btn btn-success">
                        啟動處理器
                    </button>
                    <?php else: ?>
                    <button type="submit" name="action" value="stop_processor" class="btn btn-danger">
                        停止處理器
                    </button>
                    <?php endif; ?>
                    
                    <button type="submit" name="action" value="clear_queue" class="btn btn-warning" onclick="return confirm('確定要清空佇列嗎？')">
                        清空佇列
                    </button>
                </form>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Redis 連接狀態</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($redisConnected): ?>
                        <div class="alert alert-success">
                            <strong>已連接到 Redis 伺服器</strong>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-danger">
                            <strong>無法連接到 Redis 伺服器</strong><br>
                            <?php echo $redisError; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($redisConnected): ?>
                <div class="card">
                    <div class="card-header">
                        <h5>佇列處理器狀態</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($queueStats['processor_running']) && $queueStats['processor_running']): ?>
                            <?php if ($queueStats['processor_alive'] === true): ?>
                                <div class="alert alert-success">
                                    <span class="status-indicator status-running"></span>
                                    <strong>處理器正在運行</strong><br>
                                    PID: <?php echo $queueStats['processor_pid']; ?>
                                </div>
                            <?php elseif ($queueStats['processor_alive'] === 'unknown'): ?>
                                <div class="alert alert-warning">
                                    <span class="status-indicator status-unknown"></span>
                                    <strong>處理器狀態未知</strong><br>
                                    PID: <?php echo $queueStats['processor_pid']; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    <span class="status-indicator status-stopped"></span>
                                    <strong>處理器已停止</strong><br>
                                    PID 文件存在但進程不存在
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <span class="status-indicator status-stopped"></span>
                                <strong>處理器未運行</strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-6">
                <?php if ($redisConnected): ?>
                <div class="card">
                    <div class="card-header">
                        <h5>佇列統計</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th>佇列長度</th>
                                    <td><span id="queueLength"><?php echo $queueStats['queue_length']; ?></span> 個訂單</td>
                                </tr>
                                <tr>
                                    <th>已處理訂單</th>
                                    <td><span id="processedOrders"><?php echo $queueStats['processed_orders']; ?></span> 個訂單</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($redisConnected && !empty($queueStats['logs'])): ?>
                <div class="card">
                    <div class="card-header">
                        <h5>日誌文件</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>文件名</th>
                                    <th>大小</th>
                                    <th>修改時間</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($queueStats['logs'] as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['name']); ?></td>
                                    <td><?php echo formatSize($log['size']); ?></td>
                                    <td><?php echo $log['modified']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($redisConnected && !empty($queueStats['pending_orders'])): ?>
        <div class="card">
            <div class="card-header">
                <h5>待處理訂單 (<span id="pendingOrdersCount"><?php echo count($queueStats['pending_orders']); ?></span>)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="pendingOrdersTable">
                        <thead>
                            <tr>
                                <th>訂單 ID</th>
                                <th>時間</th>
                                <th>詳細信息</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($queueStats['pending_orders'] as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['order_id'] ?? 'N/A'); ?></td>
                                <td><?php echo isset($order['timestamp']) ? formatTime($order['timestamp']) : 'N/A'; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#orderModal" 
                                            data-order='<?php echo htmlspecialchars(json_encode($order)); ?>'>
                                        查看詳情
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($redisConnected && !empty($queueStats['recent_orders'])): ?>
        <div class="card">
            <div class="card-header">
                <h5>最近處理的訂單 (<span id="recentOrdersCount"><?php echo count($queueStats['recent_orders']); ?></span>)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="recentOrdersTable">
                        <thead>
                            <tr>
                                <th>訂單 ID</th>
                                <th>時間</th>
                                <th>狀態</th>
                                <th>詳細信息</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($queueStats['recent_orders'] as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['order_id'] ?? 'N/A'); ?></td>
                                <td><?php echo isset($order['timestamp']) ? formatTime($order['timestamp']) : 'N/A'; ?></td>
                                <td>
                                    <?php if (isset($order['status'])): ?>
                                        <?php if ($order['status'] === 'success'): ?>
                                            <span class="badge bg-success">成功</span>
                                        <?php elseif ($order['status'] === 'error'): ?>
                                            <span class="badge bg-danger">失敗</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($order['status']); ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">未知</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#orderModal" 
                                            data-order='<?php echo htmlspecialchars(json_encode($order)); ?>'>
                                        查看詳情
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($redisConnected && isset($queueStats['latest_log'])): ?>
        <div class="card">
            <div class="card-header">
                <h5>最新日誌</h5>
            </div>
            <div class="card-body">
                <div class="log-container" id="latestLog">
                    <?php echo nl2br(htmlspecialchars($queueStats['latest_log'])); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- 訂單詳情模態框 -->
        <div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="orderModalLabel">訂單詳情</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <pre id="orderDetails" class="bg-light p-3"></pre>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 訂單詳情模態框
        document.addEventListener('DOMContentLoaded', function() {
            const orderModal = document.getElementById('orderModal');
            if (orderModal) {
                orderModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const orderData = JSON.parse(button.getAttribute('data-order'));
                    const orderDetails = document.getElementById('orderDetails');
                    orderDetails.textContent = JSON.stringify(orderData, null, 2);
                });
            }
            
            // 刷新按鈕
            const refreshBtn = document.getElementById('refreshBtn');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function() {
                    location.reload();
                });
            }
            
            // 自動刷新
            setInterval(function() {
                fetch(window.location.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // 更新佇列統計
                    document.getElementById('queueLength').textContent = data.queue_length;
                    document.getElementById('processedOrders').textContent = data.processed_orders;
                    
                    // 更新日誌
                    if (data.latest_log && document.getElementById('latestLog')) {
                        document.getElementById('latestLog').innerHTML = data.latest_log.replace(/\n/g, '<br>');
                    }
                    
                    // 更新待處理訂單數量
                    if (data.pending_orders && document.getElementById('pendingOrdersCount')) {
                        document.getElementById('pendingOrdersCount').textContent = data.pending_orders.length;
                    }
                    
                    // 更新最近處理的訂單數量
                    if (data.recent_orders && document.getElementById('recentOrdersCount')) {
                        document.getElementById('recentOrdersCount').textContent = data.recent_orders.length;
                    }
                })
                .catch(error => console.error('刷新數據失敗:', error));
            }, 10000); // 每 10 秒刷新一次
        });
    </script>
</body>
</html>