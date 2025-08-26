<?php
/**
 * Redis 快取手動刷新頁面
 * 提供按鈕操作即時更新各資源之快取
 * 使用 JSDoc 註解
 */
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
    <title>ABPay 後台管理系統 - Redis 快取刷新</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://adminlte.io/themes/v3/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="https://adminlte.io/themes/v3/dist/css/adminlte.min.css?v=3.2.0">
    <style>
        .result{white-space:pre-wrap}
    </style>
    <script>
    /**
     * 呼叫後端刷新 API
     * @param {string} type - 資源類型
     * @param {Record<string,string>} extra - 額外參數
     */
    async function refreshCache(type, extra) {
        const params = new URLSearchParams({ type, ...extra });
        const btnId = `btn_${type}`;
        const btn = document.getElementById(btnId);
        const out = document.getElementById('output');
        btn.disabled = true;
        btn.innerText = '更新中...';
        try {
            const res = await fetch('api/refresh_cache.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            });
            const json = await res.json();
            out.textContent = JSON.stringify(json, null, 2);
        } catch (e) {
            out.textContent = '請求失敗: ' + (e && e.message ? e.message : String(e));
        } finally {
            btn.disabled = false;
            btn.innerText = '立即更新';
        }
    }

    /**
     * 綁定所有按鈕事件
     */
    function bindEvents() {
        document.getElementById('btn_customer').addEventListener('click', () => {
            const lineId = document.getElementById('lineId').value.trim();
            if (!lineId) { alert('請輸入 lineId'); return; }
            refreshCache('customer', { lineId });
        });

        document.getElementById('btn_game_account').addEventListener('click', () => {
            const sid = document.getElementById('sid_account').value.trim();
            if (!sid) { alert('請輸入 Sid'); return; }
            refreshCache('game_account', { sid });
        });

        document.getElementById('btn_game_item').addEventListener('click', () => {
            const sid = document.getElementById('sid_item').value.trim();
            if (!sid) { alert('請輸入 Sid'); return; }
            refreshCache('game_item', { sid });
        });

        document.getElementById('btn_game_list').addEventListener('click', () => {
            refreshCache('game_list', {});
        });

        document.getElementById('btn_rate').addEventListener('click', () => {
            refreshCache('rate', {});
        });
    }

    document.addEventListener('DOMContentLoaded', bindEvents);
    </script>
    </head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> 登出</a></li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="dashboard.php" class="brand-link">
            <span class="brand-text font-weight-light">ABPay 後台管理</span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>儀表板</p></a></li>
                    <li class="nav-item"><a href="orders.php" class="nav-link"><i class="nav-icon fas fa-shopping-cart"></i><p>訂單管理</p></a></li>
                    <li class="nav-item"><a href="users.php" class="nav-link"><i class="nav-icon fas fa-users"></i><p>使用者管理</p></a></li>
                    <li class="nav-item"><a href="games.php" class="nav-link"><i class="nav-icon fas fa-gamepad"></i><p>遊戲管理</p></a></li>
                    <li class="nav-item"><a href="strategy_order.php" class="nav-link"><i class="nav-icon fas fa-file-upload"></i><p>戰略自動發單</p></a></li>
                    <li class="nav-item"><a href="redis_refresh.php" class="nav-link active"><i class="nav-icon fas fa-sync-alt"></i><p>Redis 快取刷新</p></a></li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1 class="m-0">Redis 快取刷新</h1></div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted">按下「立即更新」將直接從外部 API 拉取最新資料並覆寫 Redis 快取。</p>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header"><h3 class="card-title">Customer</h3></div>
                                    <div class="card-body">
                                        <div class="form-inline">
                                            <label class="mr-2">lineId</label>
                                            <input id="lineId" type="text" class="form-control mr-2" placeholder="輸入 lineId">
                                            <button id="btn_customer" class="btn btn-primary">立即更新</button>
                                        </div>
                                        <div class="small mt-2">快取鍵：customer_cache_{lineId}（TTL 300s）</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header"><h3 class="card-title">GameAccount</h3></div>
                                    <div class="card-body">
                                        <div class="form-inline">
                                            <label class="mr-2">Sid</label>
                                            <input id="sid_account" type="text" class="form-control mr-2" placeholder="輸入 Sid">
                                            <button id="btn_game_account" class="btn btn-primary">立即更新</button>
                                        </div>
                                        <div class="small mt-2">快取鍵：game_account_cache_{Sid}（TTL 86400s）</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header"><h3 class="card-title">GameItem</h3></div>
                                    <div class="card-body">
                                        <div class="form-inline">
                                            <label class="mr-2">Sid</label>
                                            <input id="sid_item" type="text" class="form-control mr-2" placeholder="輸入 Sid">
                                            <button id="btn_game_item" class="btn btn-primary">立即更新</button>
                                        </div>
                                        <div class="small mt-2">快取鍵：game_item_cache_{Sid}（TTL 300s）</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header"><h3 class="card-title">GameList</h3></div>
                                    <div class="card-body">
                                        <button id="btn_game_list" class="btn btn-primary">立即更新</button>
                                        <div class="small mt-2">快取鍵：game_list_cache（TTL 86400s）</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header"><h3 class="card-title">Rate</h3></div>
                                    <div class="card-body">
                                        <button id="btn_rate" class="btn btn-primary">立即更新</button>
                                        <div class="small mt-2">快取鍵：rate_cache（TTL 5s）</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header"><h3 class="card-title">結果</h3></div>
                                    <div class="card-body">
                                        <pre id="output" class="result">等待操作...</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2024 ABPay</strong>
        All rights reserved.
    </footer>
</div>

<script src="https://adminlte.io/themes/v3/plugins/jquery/jquery.min.js"></script>
<script src="https://adminlte.io/themes/v3/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://adminlte.io/themes/v3/dist/js/adminlte.js?v=3.2.0"></script>
</body>
</html>


