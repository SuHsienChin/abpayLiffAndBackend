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
                                    請上傳包含以下欄位的檔案：序號、客編、賽區未確認、系統客編要完整、版本、編號不用動、數量、下拉式選取商品、備註
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
                                <table id="data-table" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>序號</th>
                                            <th>客編</th>
                                            <th>賽區未確認</th>
                                            <th>系統客編</th>
                                            <th>版本</th>
                                            <th>編號</th>
                                            <th>數量</th>
                                            <th>商品</th>
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
                                客編: row[1] || '',
                                賽區未確認: row[2] || '',
                                系統客編: row[3] || '',
                                版本: row[4] || '',
                                編號: row[5] || '',
                                數量: row[6] || '',
                                商品: row[7] || '',
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
    function displayData(data) {
        const tableBody = $('#data-table tbody');
        tableBody.empty();
        
        data.forEach(item => {
            const row = $('<tr>');
            row.append(`<td>${item.序號}</td>`);
            row.append(`<td>${item.客編}</td>`);
            row.append(`<td>${item.賽區未確認}</td>`);
            row.append(`<td>${item.系統客編}</td>`);
            row.append(`<td>${item.版本}</td>`);
            row.append(`<td>${item.編號}</td>`);
            row.append(`<td>${item.數量}</td>`);
            row.append(`<td>${item.商品}</td>`);
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
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Chinese-traditional.json"
            }
        });
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
                    const customer = customers.find(c => c.customer_id === customerId);
                    
                    // 更新表格中的客戶資料單元格
                    const cell = $(`td[data-customer-id="${customerId}"]`);
                    
                    if (customer) {
                        console.log('找到對應客戶資料');
                        cell.html(`
                            <div>
                                <strong>ID:</strong> ${customer.id}<br>
                                <strong>客戶ID:</strong> ${customer.customer_id}<br>
                                <strong>SID:</strong> ${customer.customer_sid || '無'}<br>
                                <strong>建立時間:</strong> ${customer.created_at || '無'}
                            </div>
                        `);
                        cell.addClass('text-success');
                    } else {
                        cell.text('未找到對應客戶資料');
                        cell.addClass('text-danger');
                    }
                });
            })
            .catch(function(error) {
                console.error('獲取客戶資料失敗:', error);
                alert('獲取客戶資料失敗: ' + (error.response?.data?.error || error.message));
            });
    }
});
</script>
</body>
</html>