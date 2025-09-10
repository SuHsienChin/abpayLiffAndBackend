<?php
// Admin UI for Redis monitoring
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redis 監控</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body { padding: 16px; }
        .key-badge { font-family: monospace; }
        .table-fixed { table-layout: fixed; }
        .monospace { font-family: monospace; }
        .cursor-pointer { cursor: pointer; }
    </style>
    <script>
        async function fetchJSON(url) {
            const res = await fetch(url);
            if (!res.ok) throw new Error(await res.text());
            return await res.json();
        }

        function esc(s){
            return s.replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
        }

        async function loadStats(){
            const data = await fetchJSON('api/redis_monitor.php?action=stats');
            document.getElementById('dbsize').textContent = data.dbsize;
            document.getElementById('redisVersion').textContent = data.info.Server?.redis_version || '-';
        }

        async function scanKeys(){
            const pattern = document.getElementById('pattern').value || '*';
            const type = document.getElementById('type').value;
            const data = await fetchJSON(`api/redis_monitor.php?action=scan&pattern=${encodeURIComponent(pattern)}&type=${encodeURIComponent(type)}&count=300`);
            const tbody = document.getElementById('keysBody');
            tbody.innerHTML = '';
            for (const k of data.keys){
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
                html = `<pre class="monospace">${esc(String(data.value))}</pre>`;
            } else if (type === 'list' || type === 'zset'){
                const data = await fetchJSON(`api/redis_monitor.php?action=members&key=${encodeURIComponent(key)}&start=0&count=100`);
                html = '<ol class="monospace">' + (Array.isArray(data.items) ? data.items : Object.entries(data.items).map(([m,s])=>`${esc(m)}  (score:${s})`)).map(v=>`<li class="text-break">${typeof v === 'string' ? esc(v) : v}</li>`).join('') + '</ol>';
            } else if (type === 'hash'){
                const data = await fetchJSON(`api/redis_monitor.php?action=hash&key=${encodeURIComponent(key)}&start=0&count=100`);
                html = '<table class="table table-sm table-striped"><thead><tr><th>Field</th><th>Value</th></tr></thead><tbody>' + Object.entries(data.items).map(([f,v])=>`<tr><td class="monospace">${esc(f)}</td><td class="monospace text-break">${esc(String(v))}</td></tr>`).join('') + '</tbody></table>';
            } else if (type === 'set'){
                // sets: show as list via SSCAN is not implemented; fall back by members (careful for big sets)
                html = '<div class="text-muted">暫不支援大型 set 的完整查看</div>';
            } else {
                html = '<div class="text-muted">不支援的類型</div>';
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
</head>
<body>
    <div class="container-fluid">
        <h3 class="mb-3">Redis 監控</h3>
        <div class="row g-3 mb-3">
            <div class="col-auto">
                <div class="card p-3">
                    <div>鍵數量: <span id="dbsize">-</span></div>
                    <div>Redis 版本: <span id="redisVersion">-</span></div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-sm-4">
                        <label class="form-label">Key pattern</label>
                        <input id="pattern" class="form-control" placeholder="例如: queue:* 或 *">
                    </div>
                    <div class="col-sm-3">
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
                    <div class="col-sm-3">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


