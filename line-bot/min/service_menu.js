// LINE Bot 設定
const config = {
    channelAccessToken: '/tawKQINYfBLEp75MXH+HMsQ1Hw/IT1UZAnC0nxge0clIvgoBjBUE1Tr+LIhIhIpfa9TfYYgx1pTClW8z1UYK/iALlqXv6NDXe7G5PsemziQxAuDFOGpyHHqxP0b51gMjkz8Kmo0jCULhNm7A4P4VAdB04t89/1O/w1cDnyilFU=',
    channelSecret: '16b83aba6352dd5b9056bea95eee202d'
};

// 處理接收到的訊息
async function handleMessage(event) {
    if (event.message.text === '價目表') {
        const replyMessage = `毛孩形象全檔方案 NT.5980
毛孩親寫真 NT.600
毛孩與你親子寫真NT.1200
毛孩BOOM起來NT.800`;

        // 使用 LINE Messaging API
        const url = 'https://api.line.me/v2/bot/message/reply';
        const headers = {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${config.channelAccessToken}`
        };
        const body = {
            replyToken: event.replyToken,
            messages: [{
                type: 'text',
                text: replyMessage
            }]
        };

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify(body)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('Error:', error);
            return null;
        }
    }
}

// 處理 webhook 請求
async function handleWebhook(request) {
    const events = request.body.events;
    return Promise.all(events.map(event => {
        if (event.type === 'message' && event.message.type === 'text') {
            return handleMessage(event);
        }
        return Promise.resolve(null);
    }));
}

// 監聽 webhook 請求
document.addEventListener('DOMContentLoaded', () => {
    // 這裡需要根據您的伺服器環境來調整
    // 例如，如果您使用純 HTML/JS，可能需要設定一個表單或按鈕來觸發
    console.log('LINE Bot webhook handler ready');
});