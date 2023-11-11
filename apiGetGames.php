<!DOCTYPE html>
<html>
<head>
    <title>使用JavaScript顯示JSON資料</title>
</head>
<body>
    <button onclick="fetchData()">取得API資料</button>

    <script>
        function fetchData() {
            // 使用fetch API來取得API資料
            fetch('getGameList.php')
                .then(response => response.json())
                .then(data => {
                    // 將取得的JSON資料顯示在console中
                    console.log(data);
                })
                .catch(error => {
                    console.error('取得API資料時發生錯誤:', error);
                });
        }
    </script>
</body>
</html>
