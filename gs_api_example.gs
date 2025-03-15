function getCustomers() {
  // API 設定
  const API_KEY = 'k3345678';
  const API_URL = 'https://abpay.tw/api_customers.php?X_API_KEY=' + API_KEY;
  
  // 設置請求選項
  const options = {
    'method': 'get',
    'muteHttpExceptions': true
  };
  
  try {
    // 發送請求
    const response = UrlFetchApp.fetch(API_URL, options);
    const responseCode = response.getResponseCode();
    Logger.log('API 響應狀態碼：' + responseCode);
    Logger.log('API 響應內容：' + response.getContentText());
    
    // 檢查回應狀態
    if (responseCode === 200) {
      const jsonResponse = JSON.parse(response.getContentText());
      Logger.log('解析後的 JSON 數據：' + JSON.stringify(jsonResponse));
      return jsonResponse;
    } else if (responseCode === 401) {
      throw new Error('API 認證失敗，請檢查 API Key');
    } else if (responseCode === 429) {
      throw new Error('請求頻率過高，請稍後再試');
    } else {
      throw new Error('API 請求失敗：HTTP ' + responseCode);
    }
  } catch (error) {
    Logger.log('錯誤：' + error.toString());
    throw error;
  }
}

// 使用範例：在 Google Sheets 中顯示客戶數據
function displayCustomersInSheet() {
  try {
    const customers = getCustomers();
    const sheet = SpreadsheetApp.getActiveSheet();
    
    // 設置表頭
    if (customers.length > 0) {
      const headers = Object.keys(customers[0]);
      sheet.getRange(1, 1, 1, headers.length).setValues([headers]);
      
      // 寫入數據
      const data = customers.map(customer => headers.map(header => customer[header]));
      sheet.getRange(2, 1, data.length, headers.length).setValues(data);
    }
  } catch (error) {
    SpreadsheetApp.getUi().alert('錯誤：' + error.toString());
  }
}