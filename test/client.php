<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Check</title>
</head>
<body>



<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    const checkBtn = document.getElementById('checkBtn');


    function checkForNewData() {
        axios.get('server.php')  // 請更換成實際的伺服器端路徑
            .then(response => {
                const newData = response.data.newData;

                if (newData) {
                    console.log('New data found:', newData);

                    // 在這裡處理新資料，例如跳通知
                    alert('New data found: ' + newData);

                    // 更新頁面或執行其他操作...
                } else {
                    console.log('No new data');
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // 定期執行檢查
    setInterval(checkForNewData, 5000);  // 每 5 秒檢查一次，請根據實際需求設定間隔時間

    console.log('123');
</script>

</body>
</html>
