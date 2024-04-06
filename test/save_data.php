<?php
$host = 'localhost';
$database = 'abpay';
$username = 'abpay';
$password = 'Aa.730216';

$connection = new mysqli($host, $username, $password, $database);

if ($connection->connect_error) {
    die("連接失敗：" . $connection->connect_error);
}

$field1 = $_GET['column1'];
$field2 = $_GET['column2'];


$sql = "INSERT INTO humandesign (column1, column2) VALUES ('$field1', '$field2')";

if ($connection->query($sql) === TRUE) {
    echo "資料新增成功";
} else {
    echo "Error: " . $sql . "<br>" . $connection->error;
}

$connection->close();
?>
