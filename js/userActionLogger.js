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