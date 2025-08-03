<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redis 訂單佇列狀態</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            font-weight: bold;
            background-color: #f1f8ff;
        }
        .refresh-btn {
            margin-bottom: 20px;
        }
        .queue-info {
            margin-bottom: 20px;
        }
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        .status-processing {
            color: #17a2b8;
            font-weight: bold;
        }
        .status-success {
            color: #28a745;
            font-weight: bold;
        }
        .status-failed, .status-error {
            color: #dc3545;
            font-weight: bold;
        }
        .timestamp {
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="my-4">Redis 訂單佇列狀態</h1>
        
        <div class="d-flex gap-2 mb-4">
            <button id="refreshBtn" class="btn btn-primary">
                <i class="bi bi-arrow-clockwise"></i> 重新整理
            </button>
            <button id="processBtn" class="btn btn-success">
                <i class="bi bi-play-fill"></i> 處理下一個訂單
            </button>
        </div>
        
        <div class="queue-info alert alert-info" id="queueInfo">
            正在載入佇列資訊...
        </div>
        
        <div id="queueItems" class="row">
            <!-- 佇列項目將在這裡動態生成 -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 頁面載入時獲取佇列資訊
        document.addEventListener('DOMContentLoaded', function() {
            fetchQueueData();
            
            // 設置重新整理按鈕事件
            document.getElementById('refreshBtn').addEventListener('click', fetchQueueData);
            
            // 設置處理訂單按鈕事件
            document.getElementById('processBtn').addEventListener('click', processNextOrder);
        });
        
        // 處理下一個訂單
        function processNextOrder() {
            const processBtn = document.getElementById('processBtn');
            processBtn.disabled = true;
            processBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 處理中...';
            
            fetch('processQueueManually.php')
                .then(response => response.json())
                .then(data => {
                    if (data.processed) {
                        showAlert('success', `成功處理一個訂單 (ID: ${data.queue_item.id})`);
                    } else {
                        showAlert('warning', data.message);
                    }
                    fetchQueueData(); // 重新載入佇列資料
                })
                .catch(error => {
                    console.error('處理訂單時出錯:', error);
                    showAlert('danger', `處理訂單時出錯: ${error.message}`);
                })
                .finally(() => {
                    processBtn.disabled = false;
                    processBtn.innerHTML = '<i class="bi bi-play-fill"></i> 處理下一個訂單';
                });
        }
        
        // 顯示提示訊息
        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            const container = document.querySelector('.container');
            container.insertBefore(alertDiv, document.getElementById('queueInfo'));
            
            // 5秒後自動關閉
            setTimeout(() => {
                alertDiv.classList.remove('show');
                setTimeout(() => alertDiv.remove(), 150);
            }, 5000);
        }
        
        // 獲取佇列資料
        function fetchQueueData() {
            fetch('viewRedisQueue.php')
                .then(response => response.json())
                .then(data => {
                    updateQueueInfo(data);
                    renderQueueItems(data.queue_items);
                })
                .catch(error => {
                    console.error('獲取佇列資料時出錯:', error);
                    document.getElementById('queueInfo').innerHTML = 
                        `<div class="alert alert-danger">獲取佇列資料時出錯: ${error.message}</div>`;
                });
        }
        
        // 更新佇列資訊
        function updateQueueInfo(data) {
            const queueInfo = document.getElementById('queueInfo');
            queueInfo.innerHTML = `
                <strong>佇列長度:</strong> ${data.queue_length} 個訂單
                <br>
                <strong>最後更新時間:</strong> ${new Date().toLocaleString()}
            `;
        }
        
        // 渲染佇列項目
        function renderQueueItems(items) {
            const queueItemsContainer = document.getElementById('queueItems');
            queueItemsContainer.innerHTML = '';
            
            if (items.length === 0) {
                queueItemsContainer.innerHTML = '<div class="col-12"><div class="alert alert-warning">佇列中沒有訂單</div></div>';
                return;
            }
            
            items.forEach((item, index) => {
                const statusClass = getStatusClass(item.status);
                const card = document.createElement('div');
                card.className = 'col-md-6 col-lg-4';
                card.innerHTML = `
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>訂單 #${index + 1}</span>
                            <span class="${statusClass}">${getStatusText(item.status)}</span>
                        </div>
                        <div class="card-body">
                            <p><strong>佇列 ID:</strong> <small>${item.id}</small></p>
                            <p><strong>建立時間:</strong> <span class="timestamp">${item.created_at}</span></p>
                            ${item.processed_at ? `<p><strong>處理時間:</strong> <span class="timestamp">${item.processed_at}</span></p>` : ''}
                            <p><strong>URL 參數:</strong></p>
                            <pre class="bg-light p-2"><code>${formatUrlParams(item.url_params)}</code></pre>
                        </div>
                    </div>
                `;
                queueItemsContainer.appendChild(card);
            });
        }
        
        // 獲取狀態對應的 CSS 類別
        function getStatusClass(status) {
            switch(status) {
                case 'pending': return 'status-pending';
                case 'processing': return 'status-processing';
                case 'success': return 'status-success';
                case 'failed': return 'status-failed';
                case 'error': return 'status-error';
                default: return '';
            }
        }
        
        // 獲取狀態對應的文字
        function getStatusText(status) {
            switch(status) {
                case 'pending': return '等待處理';
                case 'processing': return '處理中';
                case 'success': return '處理成功';
                case 'failed': return '處理失敗';
                case 'error': return '處理錯誤';
                default: return status;
            }
        }
        
        // 格式化 URL 參數
        function formatUrlParams(urlParams) {
            return urlParams.split('&').join('\n');
        }
    </script>
</body>
</html>