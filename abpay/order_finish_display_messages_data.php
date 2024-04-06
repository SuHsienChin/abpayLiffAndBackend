<?php

// 連接資料庫
require_once 'databaseConnection.php';

$connection = new DatabaseConnection();
$pdo = $connection->connect();

$title = $_POST['title'];
$editor = $_POST['editor'];
$i = 0;
// 接收表單數據
foreach ($title as $value) {
    echo $editor[$i];
    try {

        // 設置 PDO 錯誤模式為例外
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 檢查資料表是否已有資料
        $stmtCheck = $pdo->prepare("SELECT * FROM order_finish_display_messages WHERE `title` = '$value' ");
        $stmtCheck->execute();
        $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        $editor[$i] = json_encode($editor[$i]);

        if ($result) {
            // 資料表已有資料，執行更新
            // $sql = "UPDATE order_finish_display_messages SET `content` = '$editor[$i]' WHERE `title` = '$value' ";
            $sql = "UPDATE order_finish_display_messages SET `content` = :content WHERE `title` = :title ";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':title', $value, PDO::PARAM_STR);
            $stmt->bindParam(':content', $editor[$i], PDO::PARAM_STR);
        } else {
            // 資料表沒有資料，執行新增
            // $columns = implode(", ", array_keys($editors));
            // $values = ":" . implode(", :", array_keys($editors));

            // $sql = "INSERT INTO order_finish_display_messages ($columns) VALUES ($values)";
            // 使用 prepared statement 插入或更新數據
            $stmt = $pdo->prepare($sql);
        }

        // 執行插入或更新
        $stmt->execute();

        echo "表單數據成功處理！";
    } catch (PDOException $e) {
        echo "處理失敗：" . $e->getMessage();
    } finally {
        // 關閉資料庫連接
        $conn = null;
    }
    $i++;
}
echo "<script type='text/javascript'>";
echo "alert('資料修改成功');";
echo "window.location.href='order_finish_display_messages.php'";
echo "</script>"; 

?>