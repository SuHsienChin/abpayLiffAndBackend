<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ABPay 後台管理系統 - 戰略自動發單</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://adminlte.io/themes/v3/plugins/fontawesome-free/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://adminlte.io/themes/v3/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://adminlte.io/themes/v3/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://adminlte.io/themes/v3/dist/css/adminlte.min.css?v=3.2.0">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- SheetJS -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a href="logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> 登出
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="dashboard.php" class="brand-link">
            <span class="brand-text font-weight-light">ABPay 後台管理</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>儀表板</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="orders.php" class="nav-link">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>訂單管理</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="users.php" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>使用者管理</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="games.php" class="nav-link">
                            <i class="nav-icon fas fa-gamepad"></i>
                            <p>遊戲管理</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="strategy_order.php" class="nav-link active">
                            <i class="nav-icon fas fa-file-upload"></i>
                            <p>戰略自動發單</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">戰略自動發單</h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">上傳檔案</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="file-upload">選擇 Excel 或 CSV 檔案</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="file-upload" accept=".xlsx,.xls,.csv">
                                            <label class="custom-file-label" for="file-upload">選擇檔案</label>
                                        </div>
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button" id="upload-btn">上傳</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-info" role="alert" id="upload-info" style="display: none;">
                                    請上傳包含以下欄位的檔案：序號、符號、客編、系統客編、版本、商品編號、商品數量、商品名稱、備註
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row" id="data-container" style="display: none;">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">檔案資料</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <button type="button" class="btn btn-primary" id="startOrderBtn">
                                        <i class="fas fa-paper-plane"></i> 開始送單
                                    </button>
                                </div>
                                <table id="data-table" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>序號</th>
                                            <th>符號</th>
                                            <th>客編</th>
                                            <th>系統客編</th>
                                            <th>版本</th>
                                            <th>商品編號</th>
                                            <th>商品數量</th>
                                            <th>商品名稱</th>
                                            <th>備註</th>
                                            <th>客戶資料</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <strong>Copyright &copy; 2024 ABPay</strong>
        All rights reserved.
    </footer>
</div>

<!-- jQuery -->
<script src="https://adminlte.io/themes/v3/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://adminlte.io/themes/v3/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="https://adminlte.io/themes/v3/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="https://adminlte.io/themes/v3/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://adminlte.io/themes/v3/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="https://adminlte.io/themes/v3/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<!-- bs-custom-file-input -->
<script src="https://adminlte.io/themes/v3/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<!-- AdminLTE App -->
<script src="https://adminlte.io/themes/v3/dist/js/adminlte.min.js"></script>
<!-- Page specific script -->
<script>
$(document).ready(function() {
    bsCustomFileInput.init();
    
    $('#upload-info').show();
    
    // 檔案上傳處理
    $('#upload-btn').on('click', function() {
        sessionStorage.clear();
        const fileInput = document.getElementById('file-upload');
        const file = fileInput.files[0];
        
        if (!file) {
            alert('請選擇檔案');
            return;
        }
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            try {
                let data;
                const fileType = file.name.split('.').pop().toLowerCase();
                
                if (fileType === 'csv') {
                    // 處理 CSV 檔案
                    const csvData = e.target.result;
                    data = parseCSV(csvData);
                } else {
                    // 處理 Excel 檔案
                    const binaryData = e.target.result;
                    const workbook = XLSX.read(binaryData, { type: 'binary' });
                    const firstSheetName = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[firstSheetName];
                    data = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
                }
                
                if (data && data.length > 1) {
                    // 移除表頭，轉換為 JSON 格式
                    const headers = data[0];
                    const jsonData = [];
                    
                    for (let i = 1; i < data.length; i++) {
                        const row = data[i];
                        if (row.length > 0) {
                            const item = {
                                序號: row[0] || '',
                                符號: row[1] || '',
                                客編: row[2] || '',
                                系統客編: row[3] || '',
                                版本: row[4] || '',
                                商品編號: row[5] || '',
                                商品數量: row[6] || '',
                                商品名稱: row[7] || '',
                                備註: row[8] || ''
                            };
                            jsonData.push(item);
                        }
                    }
                    
                    // 存儲到 sessionStorage
                    sessionStorage.setItem('strategyOrderData', JSON.stringify(jsonData));
                    
                    // 顯示資料
                    displayData(jsonData);
                    console.log('顯示資料')
                    // 獲取客戶資料
                    fetchCustomerData();
                } else {
                    alert('檔案內容無效或為空');
                }
            } catch (error) {
                console.error('處理檔案時發生錯誤:', error);
                alert('處理檔案時發生錯誤: ' + error.message);
            }
        };
        
        reader.onerror = function() {
            alert('讀取檔案時發生錯誤');
        };
        
        if (file.name.endsWith('.csv')) {
            reader.readAsText(file);
        } else {
            reader.readAsBinaryString(file);
        }
    });
    
    // 解析 CSV 檔案
    function parseCSV(csvText) {
        const lines = csvText.split('\n');
        return lines.map(line => line.split(','));
    }
    
    // 顯示資料到表格
    // 在 displayData 函數後加入：
    function displayData(data) {
        const tableBody = $('#data-table tbody');
        tableBody.empty();
        
        data.forEach(item => {
            const row = $('<tr>');
            row.append(`<td>${item.序號}</td>`);
            row.append(`<td>${item.符號}</td>`);
            row.append(`<td>${item.客編}</td>`);
            row.append(`<td>${item.系統客編}</td>`);
            row.append(`<td>${item.版本}</td>`);
            row.append(`<td>${item.商品編號}</td>`);
            row.append(`<td>${item.商品數量}</td>`);
            row.append(`<td>${item.商品名稱}</td>`);
            row.append(`<td>${item.備註}</td>`);
            row.append(`<td data-customer-id="${item.系統客編}">載入中...</td>`);
            tableBody.append(row);
        });
        
        $('#data-container').show();
        
        // 初始化 DataTable
        if ($.fn.DataTable.isDataTable('#data-table')) {
            $('#data-table').DataTable().destroy();
        }
        
        $('#data-table').DataTable({
            "responsive": true,
            "autoWidth": false,
            "pageLength":'50',// 預設為'10'，若需更改初始每頁顯示筆數，才需設定
            "language": {
                "url": "plugins/datatables/i18n/Chinese-traditional.json"
            }
        });

        // 取得遊戲商品資料
        fetchGameItems();
    }
    
    // 新增取得遊戲商品資料的函數
    async function fetchGameItems() {
        try {
            // 取得 QOO 商品資料
            const qooResponse = await axios.get('api/proxy_game_items.php?sid=7');
            if (qooResponse.data) {
                sessionStorage.setItem('qoo_items', JSON.stringify(qooResponse.data));
                console.log('QOO 商品資料已儲存');
            }
    
            // 取得 GTW 商品資料
            const gtwResponse = await axios.get('api/proxy_game_items.php?sid=344');
            if (gtwResponse.data) {
                sessionStorage.setItem('gtw_items', JSON.stringify(gtwResponse.data));
                console.log('GTW 商品資料已儲存');
            }
    
        } catch (error) {
            console.error('取得遊戲商品資料失敗:', error);
            alert('取得遊戲商品資料失敗: ' + error.message);
        }
    }
    
    // 獲取客戶資料
    function fetchCustomerData() {
        axios.get('api/get_customers.php')
            .then(function(response) {
                const customers = response.data;
                // 將客戶資料存儲到 sessionStorage
                sessionStorage.setItem('customerData', JSON.stringify(customers));
                const fileData = JSON.parse(sessionStorage.getItem('strategyOrderData') || '[]');
                
                // 關聯客戶資料
                fileData.forEach(function(item) {
                    const customerId = item.系統客編;
                    let gameSid = '';
                    if (item.版本 === '戰略版(Qoo)') {
                        gameSid = '7';
                    } else if (item.版本 === '戰略版(青鳥)') {
                        gameSid = '344';
                    }
                    const customer = customers.find(c => c.customer_id === customerId && c.game_sid === gameSid);
                    
                    // 更新表格中的客戶資料單元格
                    const cell = $(`td[data-customer-id="${customerId}"]`);
                    
                    if (customer) {
                        console.log('找到對應客戶資料');
                        cell.html(`
                            <div>
                                <strong>ID:</strong> ${customer.id}<br>
                                <strong>客戶ID:</strong> ${customer.customer_id}<br>
                                <strong>SID:</strong> ${customer.customer_sid || '無'}<br>
                                <strong>建立時間:</strong> ${customer.created_at || '無'}<br>
                                <strong>版本:</strong> ${customer.game_sid === '7' ? '戰略版(Qoo)' : customer.game_sid === '344' ? '戰略版(青鳥)' : '未知版本'}
                            </div>
                        `);
                        cell.addClass('text-success');
                        
                        // 將客戶資料添加到原始數據中
                        item.customer_data = {
                            id: customer.id,
                            customer_id: customer.customer_id,
                            customer_sid: customer.customer_sid || '無',
                            created_at: customer.created_at || '無',
                            account_id: customer.account_id || '',
                            characters: customer.characters || '',
                            game_sid: customer.game_sid || '',
                            last_time: customer.last_time || '',
                            login_account: customer.login_account || '',
                            login_password: customer.login_password || '',
                            login_type: customer.login_type || '',
                            name: customer.name || '',
                            note1: customer.note1 || '',
                            server_name: customer.server_name || '',
                            sid: customer.sid || ''
                        };
                    } else {
                        cell.text('未找到對應客戶資料');
                        cell.addClass('text-danger');
                        item.customer_data = null;
                    }
                });
                
                // 更新 sessionStorage 中的數據
                sessionStorage.setItem('strategyOrderData', JSON.stringify(fileData));
                console.log('已更新 sessionStorage 中的關聯客戶資料');
            })
            .catch(function(error) {
                console.error('獲取客戶資料失敗:', error);
                alert('獲取客戶資料失敗: ' + (error.response?.data?.error || error.message));
            });
    }
});
</script>
<!-- 送單進度 Modal -->
<div class="modal fade" id="orderProgressModal" data-backdrop="static" data-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">送單進度</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- 進度記錄區 -->
                <div class="form-group">
                    <label>處理記錄：</label>
                    <div id="processLog" class="border p-3 bg-light" style="height: 300px; overflow-y: auto; font-family: monospace;">
                    </div>
                </div>
                
                <!-- 進度條 -->
                <div class="form-group">
                    <label>整體進度：</label>
                    <div class="progress">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            0%
                        </div>
                    </div>
                </div>
                
                <!-- 當前處理項目 -->
                <div class="form-group">
                    <label>當前處理：</label>
                    <div id="currentCustomerId" class="h5 text-primary">等待開始...</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="startSystemOrderBtn">
                    <i class="fas fa-upload"></i> 開始送單到系統
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // 開始送單按鈕點擊事件
    $('#startOrderBtn').click(function() {
        const orderData = JSON.parse(sessionStorage.getItem('strategyOrderData') || '[]');
        if (orderData.length === 0) {
            alert('沒有可處理的訂單資料');
            return;
        }
        $('#orderProgressModal').modal('show');
    });

    // 處理記錄函數
    function logProcess(message) {
        const timestamp = new Date().toLocaleTimeString();
        const logEntry = `[${timestamp}] ${message}`;
        const logElement = $('#processLog');
        logElement.append(logEntry + '\n</br>');
        logElement.scrollTop(logElement[0].scrollHeight);
    }

    // 更新進度條
    function updateProgress(current, total) {
        const percentage = Math.round((current / total) * 100);
        $('#progressBar').css('width', percentage + '%').text(percentage + '%');
    }

    // 更新當前處理客戶
    function updateCurrentCustomer(customerId) {
        $('#currentCustomerId').text(`處理中: ${customerId}`);
    }

    // 開始送單到系統按鈕點擊事件
    $('#startSystemOrderBtn').click(async function() {
        const orderData = JSON.parse(sessionStorage.getItem('strategyOrderData') || '[]');
        const customerData = JSON.parse(sessionStorage.getItem('customerData') || '[]');
        const totalOrders = orderData.length;
        let processedOrders = 0;
        let isProcessing = false;
    
        $(this).prop('disabled', true);
        logProcess('開始處理訂單...');
    
        const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));
    
        for (const order of orderData) {
            if (isProcessing) {
                await delay(1000); // 等待1秒
            }
            isProcessing = true;
            updateCurrentCustomer(order.系統客編);
            
            try {
                // 檢查必要資料
                if (!order.customer_data || !order.customer_data.customer_sid) {
                    throw new Error('無效的客戶資料');
                }

                // 準備送單資料
                const orderParams = {
                    item_id: order.商品編號,
                    quantity: order.商品數量,
                    customer_id: order.customer_data.customer_sid,
                    game_account: order.customer_data.sid
                };
                logProcess(`正在處理 ${order.系統客編} 的訂單...`);
                console.log('送單資料',orderParams);
                
                // 送單到系統
                const formData = new URLSearchParams();
                // 映射參數名稱
                const paramMapping = {
                    '商品代號': 'item_id',
                    '數量': 'quantity',
                    '系統客編': 'customer_id',
                    '遊戲帳號': 'game_account'
                };
                
                for (const key in orderParams) {
                    const mappedKey = paramMapping[key] || key;
                    formData.append(mappedKey, orderParams[key]);
                }
                const response = await axios.post('api/process_order.php', formData);
                
                if (response.data.success) {
                    logProcess(`${order.系統客編} 處理完成 - 訂單編號: ${response.data.data.order_id}`);
                    console.log('url:', response.data.data);
                } else {
                    throw new Error(response.data.message || '送單失敗');
                }
                
                processedOrders++;
                updateProgress(processedOrders, totalOrders);
                isProcessing = false;
                
            } catch (error) {
                logProcess(`錯誤: ${order.系統客編} 處理失敗 - ${error.message}`);
            }
        }
    
        logProcess('所有訂單處理完成');
        $(this).prop('disabled', false);
    });
});
</script>
</body>
</html>