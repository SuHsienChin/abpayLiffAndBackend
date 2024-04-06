<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LIFF</title>
</head>
<body>
    <div>
        <div id="h"></div>
        <div>
            <div id="result"></div>
        </div>
    </div>

</body>
<script>
    var currentUrl = window.location.href;
    alert("現在的網址是：" + currentUrl);

    // 將 alert 的內容複製到剪貼板中
function copyAlertContent() {
    // 創建一個臨時的 <textarea> 元素
    var tempTextArea = document.createElement("textarea");
    tempTextArea.value = arguments[0]; // 將內容設置為 alert 的內容
    document.body.appendChild(tempTextArea); // 將元素添加到頁面中
    tempTextArea.select(); // 選中文本內容
    document.execCommand("copy"); // 執行複製操作
    //document.body.removeChild(tempTextArea); // 刪除臨時元素
}

// 調用函數，將 alert 的內容複製到剪貼板中
copyAlertContent("現在的網址是：" + currentUrl);

</script>
</html>