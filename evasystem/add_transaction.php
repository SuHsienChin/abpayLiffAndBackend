<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];
    $item = $_POST['item'];
    $amount = $_POST['amount'];
    $signature = $_POST['signature']; // Base64 簽名數據

    $pdo = getConnection();
    try {
        $pdo->beginTransaction();

        // 查詢客戶
        $stmt = $pdo->prepare("SELECT id, balance FROM customers WHERE phone = ?");
        $stmt->execute([$phone]);
        $customer = $stmt->fetch();

        if (!$customer) {
            echo "客戶不存在！";
            exit;
        }

        // 檢查餘額
        if ($customer['balance'] < $amount) {
            echo "餘額不足！";
            exit;
        }

        // 更新餘額
        $new_balance = $customer['balance'] - $amount;
        $stmt = $pdo->prepare("UPDATE customers SET balance = ? WHERE id = ?");
        $stmt->execute([$new_balance, $customer['id']]);

        // 處理簽名圖片
        $signature = explode(',', $signature)[1]; // 移除 Base64 的 data URI 頭
        $signature_binary = base64_decode($signature);

        // 新增交易記錄
        $stmt = $pdo->prepare("INSERT INTO transactions (customer_id, status, item, date, amount, balance, signature) VALUES (?, '消費', ?, NOW(), ?, ?, ?)");
        $stmt->execute([$customer['id'], $item, $amount, $new_balance, $signature_binary]);

        $pdo->commit();
        echo "消費記錄新增成功！";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "消費記錄新增失敗：" . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>消費</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad/dist/signature_pad.min.js"></script>
    <style>
        #signature-pad {
            border: 1px solid #ced4da;
            border-radius: 5px;
            height: 200px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h2 class="text-center mb-4">消費</h2>
        <form action="add_transaction.php" method="POST" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm">
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
                <label for="signature" class="form-label">簽名</label>
                <div id="signature-pad"></div>
                <button type="button" id="clear-signature" class="btn btn-secondary mt-2">清除簽名</button>
                <input type="hidden" id="signature-data" name="signature">
            </div>
            <button type="submit" class="btn btn-primary w-100">提交消費</button>
        </form>
    </div>

    <script>
        const canvas = document.getElementById('signature-pad');
        const signaturePad = new SignaturePad(canvas);
        const clearButton = document.getElementById('clear-signature');
        const signatureData = document.getElementById('signature-data');

        // 清除簽名
        clearButton.addEventListener('click', () => {
            signaturePad.clear();
        });

        // 表單提交時，將簽名數據存到隱藏輸入欄位
        document.querySelector('form').addEventListener('submit', (e) => {
            if (signaturePad.isEmpty()) {
                alert('請提供簽名');
                e.preventDefault();
            } else {
                signatureData.value = signaturePad.toDataURL();
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
