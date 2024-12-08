<?php
require 'config.php'; // 引入資料庫連線設定

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];
    $item = $_POST['item'];
    $amount = $_POST['amount'];
    $signatureData = $_POST['signature']; // 接收簽名的 Base64 資料

    $pdo = getConnection();

    try {
        $pdo->beginTransaction();

        // 查詢客戶資料
        $stmt = $pdo->prepare("SELECT id, balance FROM customers WHERE phone = ?");
        $stmt->execute([$phone]);
        $customer = $stmt->fetch();

        if (!$customer) {
            echo "客戶不存在！";
            exit;
        }

        // 檢查餘額是否足夠
        if ($customer['balance'] < $amount) {
            echo "餘額不足！";
            exit;
        }

        // 更新餘額
        $new_balance = $customer['balance'] - $amount;
        $stmt = $pdo->prepare("UPDATE customers SET balance = ? WHERE id = ?");
        $stmt->execute([$new_balance, $customer['id']]);

        // 儲存簽名圖片
        $signaturePath = null;
        if (!$signatureData) {
            throw new Exception("未接收到簽名資料");
        }

        if (!str_starts_with($signatureData, 'data:image/png;base64,')) {
            throw new Exception("簽名資料格式錯誤，應為 Base64 的 PNG 資料");
        }
        if ($signatureData) {
            $signaturePath = saveSignatureToFile($signatureData, $customer['id']);
        }

        // 新增交易記錄
        $stmt = $pdo->prepare("INSERT INTO transactions (customer_id, status, item, date, amount, balance, signature) VALUES (?, '消費', ?, NOW(), ?, ?, ?)");
        $stmt->execute([$customer['id'], $item, $amount, $new_balance, $signaturePath]);

        $pdo->commit();
        echo "消費記錄新增成功！";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "消費記錄新增失敗：" . $e->getMessage();
    }
}

/**
 * 將簽名的 Base64 資料儲存為 JPG 檔案
 *
 * @param string $base64Data 簽名的 Base64 資料
 * @param int $customerId 客戶 ID，用於生成唯一檔案名稱
 * @return string 儲存的檔案路徑
 */
function saveSignatureToFile($base64Data, $customerId)
{
    // 檔案存放目錄
    $uploadDir = __DIR__ . '/signatures/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // 解碼 Base64 資料
    $base64Data = str_replace('data:image/png;base64,', '', $base64Data);
    $decodedData = base64_decode($base64Data);

    if ($decodedData === false) {
        throw new Exception("無效的簽名資料");
    }

    // 生成唯一檔案名稱
    $filename = 'signature_' . $customerId . '_' . time() . '.jpg';
    $filePath = $uploadDir . $filename;

    // 儲存圖片檔案
    file_put_contents($filePath, $decodedData);

    // 返回檔案相對路徑
    return 'signatures/' . $filename;
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新增消費記錄</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://szimek.github.io/signature_pad/js/signature_pad.umd.min.js"></script>
    <style>
        .signature-pad {
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_home.php">後台管理</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="admin_home.php">首頁</a></li>
                    <li class="nav-item"><a class="nav-link" href="add_customer.php">建立客戶資料</a></li>
                    <li class="nav-item"><a class="nav-link" href="add_balance.php">增加餘額</a></li>
                    <li class="nav-item"><a class="nav-link" href="add_transaction.php">消費</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_transactions.php">消費記錄查詢</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="logout.php">登出</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container py-5">
        <h2 class="text-center mb-4">新增消費記錄</h2>
        <form id="transactionForm" action="add_transaction.php" method="POST">
            <div class="mb-3">
                <label for="phone" class="form-label">客戶電話</label>
                <input type="text" class="form-control" id="phone" name="phone" required>
            </div>
            <div class="mb-3">
                <label for="item" class="form-label">使用項目</label>
                <input type="text" class="form-control" id="item" name="item" required>
            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">金額 (台幣)</label>
                <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
            </div>
            <div class="mb-3">
                <label for="signatureCanvas" class="form-label">簽名</label>
                <canvas id="signatureCanvas" class="signature-pad" width="400" height="200"></canvas>
                <button type="button" id="clearSignature" class="btn btn-secondary mt-2">清除簽名</button>
                <input type="hidden" name="signature" id="signatureInput">
            </div>
            <button type="submit" class="btn btn-primary w-100">提交記錄</button>
        </form>
    </div>

    <script>
        const canvas = document.getElementById('signatureCanvas');
        const signaturePad = new SignaturePad(canvas);
        const clearButton = document.getElementById('clearSignature');
        const form = document.getElementById('transactionForm');
        const signatureInput = document.getElementById('signatureInput');

        // 清除簽名
        clearButton.addEventListener('click', () => {
            signaturePad.clear();
        });

        // 提交表單時存入簽名的 Base64 資料
        form.addEventListener('submit', (e) => {
            if (!signaturePad.isEmpty()) {
                signatureInput.value = signaturePad.toDataURL('image/png');
            } else {
                e.preventDefault();
                alert('請提供簽名！');
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>