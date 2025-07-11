function logUserAction(page, action, data = null) {
    try {
        const lineId = sessionStorage.getItem('lineId');
        const customerData = JSON.parse(sessionStorage.getItem('customerData'));
        
        axios.post('saveUserActionLog.php', {
            line_id: lineId,
            customer_id: customerData?.Id || '',
            page: page,
            action: action,
            data: JSON.stringify(data)
        })
        .then(response => {
            console.log('行為記錄成功:', action);
        })
        .catch(error => {
            console.error('行為記錄失敗:', error);
        });
    } catch (e) {
        console.error('記錄行為時發生錯誤:', e);
    }
}

/**
 * 將使用者操作紀錄存入 MySQL
 * @param {string} action 操作描述
 * @param {object} data 相關資料
 */
function saveLogsToMysql(action, data) {
    axios.post('log_user_action.php', {
        action: action,
        data: data
    })
    .then(function(response) {
        // 可以根據需要處理回應
        // console.log('Log saved:', response.data);
    })
    .catch(function(error) {
        console.error('Log 儲存失敗:', error);
    });
}