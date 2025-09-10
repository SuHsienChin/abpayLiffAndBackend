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
    <title>ABPay 後台管理系統 - Redis 監控</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://adminlte.io/themes/v3/plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://adminlte.io/themes/v3/dist/css/adminlte.min.css?v=3.2.0">
    <style>
        .table-fixed { table-layout: fixed; }
        .monospace { font-family: monospace; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
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
                    <li class="nav-item"><a href="redis_refresh.php" class="nav-link"><i class="nav-icon fas fa-sync-alt"></i><p>Redis 快取刷新</p></a></li>
                    <li class="nav-item"><a href="redis_monitor.php" class="nav-link active"><i class="nav-icon fas fa-database"></i><p>Redis 監控</p></a></li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Redis 監控</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <div>鍵數量: <span id="dbsize">-</span></div>
                                <div>Redis 版本: <span id="redisVersion">-</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-2 align-items-end">
                            <div class="col-sm-5 col-md-4">
                                <label class="form-label">Key pattern</label>
                                <input id="pattern" class="form-control" placeholder="例如: queue:* 或 *">
                            </div>
                            <div class="col-sm-3 col-md-2">
                                <label class="form-label">Type</label>
                                <select id="type" class="form-select">
                                    <option value="all">全部</option>
                                    <option value="string">string</option>
                                    <option value="list">list</option>
                                    <option value="set">set</option>
                                    <option value="zset">zset</option>
                                    <option value="hash">hash</option>
                                </select>
                            </div>
                            <div class="col-sm-4 col-md-3">
                                <button id="refresh" class="btn btn-primary w-100">掃描</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-hover table-fixed">
                            <thead>
                                <tr>
                                    <th style="width:50%">Key</th>
                                    <th style="width:10%">Type</th>
                                    <th style="width:10%">Size</th>
                                    <th style="width:10%">TTL</th>
                                    <th style="width:20%">Action</th>
                                </tr>
                            </thead>
                            <tbody id="keysBody"></tbody>
                        </table>
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

<!-- Scripts -->
<script src="https://adminlte.io/themes/v3/plugins/jquery/jquery.min.js"></script>
<script src="https://adminlte.io/themes/v3/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://adminlte.io/themes/v3/dist/js/adminlte.js?v=3.2.0"></script>
<script>
    async function fetchJSON(url) {
        const res = await fetch(url);
        if (!res.ok) throw new Error(await res.text());
        return await res.json();
    }
    function esc(s){ return s.replace(/[&<>\"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;'}[c])); }
    async function loadStats(){
        const data = await fetchJSON('api/redis_monitor.php?action=stats');
        document.getElementById('dbsize').textContent = data.dbsize ?? '-';
        const ver = data.info && (data.info.Server?.redis_version || data.info.redis_version);
        document.getElementById('redisVersion').textContent = ver || '-';
    }
    async function scanKeys(){
        const pattern = document.getElementById('pattern').value || '*';
        const type = document.getElementById('type').value;
        const data = await fetchJSON(`api/redis_monitor.php?action=scan&pattern=${encodeURIComponent(pattern)}&type=${encodeURIComponent(type)}&count=300`);
        const tbody = document.getElementById('keysBody');
        tbody.innerHTML = '';
        for (const k of (data.keys||[])){
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="monospace text-break">${esc(k.key)}</td>
                <td><span class="badge bg-secondary">${k.type}</span></td>
                <td>${k.size ?? '-'}</td>
                <td>${k.ttl}</td>
                <td><button class="btn btn-sm btn-primary" onclick="viewKey('${encodeURIComponent(k.key)}','${k.type}')">查看</button></td>
            `;
            tbody.appendChild(tr);
        }
    }
    async function viewKey(encodedKey, type){
        const key = decodeURIComponent(encodedKey);
        document.getElementById('keyTitle').textContent = key + ` (${type})`;
        let html = '';
        if (type === 'string'){
            const data = await fetchJSON(`api/redis_monitor.php?action=get&key=${encodeURIComponent(key)}`);
            html = `<pre class=\"monospace\">${esc(String(data.value))}</pre>`;
        } else if (type === 'list' || type === 'zset'){
            const data = await fetchJSON(`api/redis_monitor.php?action=members&key=${encodeURIComponent(key)}&start=0&count=100`);
            html = '<ol class=\"monospace\">' + (Array.isArray(data.items) ? data.items : Object.entries(data.items).map(([m,s])=>`${esc(m)}  (score:${s})`)).map(v=>`<li class=\"text-break\">${typeof v === 'string' ? esc(v) : v}</li>`).join('') + '</ol>';
        } else if (type === 'hash'){
            const data = await fetchJSON(`api/redis_monitor.php?action=hash&key=${encodeURIComponent(key)}&start=0&count=100`);
            html = '<table class=\"table table-sm table-striped\"><thead><tr><th>Field</th><th>Value</th></tr></thead><tbody>' + Object.entries(data.items||{}).map(([f,v])=>`<tr><td class=\"monospace\">${esc(f)}</td><td class=\"monospace text-break\">${esc(String(v))}</td></tr>`).join('') + '</tbody></table>';
        } else if (type === 'set'){
            html = '<div class=\"text-muted\">暫不支援大型 set 的完整查看</div>';
        } else {
            html = '<div class=\"text-muted\">不支援的類型</div>';
        }
        document.getElementById('keyContent').innerHTML = html;
        const modal = new bootstrap.Modal(document.getElementById('keyModal'));
        modal.show();
    }
    document.addEventListener('DOMContentLoaded', () => {
        loadStats().catch(console.error);
        scanKeys().catch(console.error);
        document.getElementById('refresh').addEventListener('click', ()=>scanKeys().catch(console.error));
    });
</script>

<!-- Key modal -->
<div class="modal fade" id="keyModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="keyTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="keyContent"></div>
        </div>
    </div>
</div>
</body>
</html>


