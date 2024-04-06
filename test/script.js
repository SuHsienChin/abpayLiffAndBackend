let arrayA = [];
let arrayB = [];

function addData() {
    const field1Value = document.getElementById('field1').value;
    const field2Value = document.getElementById('field2').value;

    axios.get('save_data.php?column1=' + field1Value + '&column2=' + field2Value)
    .then(response => {
        console.log(response.data);
        alert('資料新增成功！');
    })
    .catch(error => {
        console.error('資料新增失敗：', error);
    });
}

function drawWinner() {
    axios.get('get_all_data.php')
    .then(response => {
        // 將資料存放到陣列中
        arrayA = response.data.map(item => item.column1);
        arrayB = response.data.map(item => item.column2);

        // 選擇並顯示隨機資料
        const result1 = getRandomElement(arrayA);
        const result2 = getRandomElement(arrayB);

        updateResult('result1', result1);
        updateResult('result2', result2);
    })
    .catch(error => {
        console.error('無法獲取資料：', error);
    });
}

function getRandomElement(array) {
    const randomIndex = Math.floor(Math.random() * array.length);
    return array[randomIndex];
}

function updateResult(elementId, result) {
    const element = document.getElementById(elementId);
    element.textContent = ` 結果：${result}`;
}
