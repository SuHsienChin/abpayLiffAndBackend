<?php
// API認證配置
define('API_USER_ID', 'test02');
define('API_PASSWORD', '3345678');

// API端點配置
define('ORDER_API_ENDPOINT', 'http://www.adp.idv.tw/api/Order');

// LIFF配置
define('LIFF_ID', '2000183731-BLmrAGPp');

// 系統配置
define('ORDER_RETRY_ATTEMPTS', 3);  // API請求重試次數
define('ORDER_RETRY_DELAY', 1000);  // 重試間隔(毫秒)

// 訂單狀態
define('ORDER_STATUS_PROCESSING', '訂單處理中');
define('ORDER_STATUS_COMPLETED', '已完成');
define('ORDER_STATUS_FAILED', '失敗');

// 安全配置
define('ANTI_REPLAY_WINDOW', 300);  // 防重放攻擊時間窗口(秒)
