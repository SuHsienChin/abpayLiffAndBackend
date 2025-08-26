<?php
/**
 * Redis 快取手動刷新頁面
 * 提供按鈕操作即時更新各資源之快取
 * 使用 JSDoc 註解
 */
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redis 快取刷新工具</title>
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <style>
        .container{max-width:960px;margin:32px auto}
        .card{margin-bottom:16px}
        .small{color:#666}
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
<body class="hold-transition">
<div class="container">
    <h2 class="mb-3">Redis 快取刷新工具</h2>
    <p class="small">按下「立即更新」將直接從外部 API 拉取最新資料並覆寫 Redis 快取。</p>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Customer</h5>
            <div class="form-inline">
                <label class="mr-2">lineId</label>
                <input id="lineId" type="text" class="form-control mr-2" placeholder="輸入 lineId">
                <button id="btn_customer" class="btn btn-primary">立即更新</button>
            </div>
            <div class="small mt-2">快取鍵：customer_cache_{lineId}（TTL 300s）</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">GameAccount</h5>
            <div class="form-inline">
                <label class="mr-2">Sid</label>
                <input id="sid_account" type="text" class="form-control mr-2" placeholder="輸入 Sid">
                <button id="btn_game_account" class="btn btn-primary">立即更新</button>
            </div>
            <div class="small mt-2">快取鍵：game_account_cache_{Sid}（TTL 86400s）
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">GameItem</h5>
            <div class="form-inline">
                <label class="mr-2">Sid</label>
                <input id="sid_item" type="text" class="form-control mr-2" placeholder="輸入 Sid">
                <button id="btn_game_item" class="btn btn-primary">立即更新</button>
            </div>
            <div class="small mt-2">快取鍵：game_item_cache_{Sid}（TTL 300s）
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">GameList</h5>
            <button id="btn_game_list" class="btn btn-primary">立即更新</button>
            <div class="small mt-2">快取鍵：game_list_cache（TTL 86400s）</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Rate</h5>
            <button id="btn_rate" class="btn btn-primary">立即更新</button>
            <div class="small mt-2">快取鍵：rate_cache（TTL 5s）</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">結果</h5>
            <pre id="output" class="result">等待操作...</pre>
        </div>
    </div>

    <div class="mt-3 small text-muted">若需要權限控管，請將此頁納入現有後台登入驗證流程。</div>
</div>
</body>
</html>


