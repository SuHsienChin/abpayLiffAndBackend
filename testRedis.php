<?php
require_once 'RedisConnection.php';

// 設置頁面標題和樣式
echo "<html>\n<head>\n<title>Redis 連接測試</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }\n";
echo ".success { color: green; font-weight: bold; }\n";
echo ".error { color: red; font-weight: bold; }\n";
echo ".section { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }\n";
echo ".section h2 { margin-top: 0; color: #333; }\n";
echo "pre { background-color: #f5f5f5; padding: 10px; border-radius: 3px; overflow: auto; }\n";
echo "table { border-collapse: collapse; width: 100%; }\n";
echo "table, th, td { border: 1px solid #ddd; }\n";
echo "th, td { padding: 8px; text-align: left; }\n";
echo "th { background-color: #f2f2f2; }\n";
echo "</style>\n";
echo "</head>\n<body>\n";

echo "<h1>Redis 連接測試</h1>\n";

// 測試 Redis 連接
echo "<div class='section'>\n";
echo "<h2>連接測試</h2>\n";

try {
    $redis = RedisConnection::getInstance();
    $redisInstance = $redis->getRedis();
    
    if ($redisInstance) {
        echo "<p class='success'>Redis 連接成功！</p>\n";
        
        // 顯示 Redis 信息
        echo "<h3>Redis 服務器信息</h3>\n";
        echo "<pre>";
        print_r($redisInstance->info());
        echo "</pre>\n";
    } else {
        echo "<p class='error'>Redis 連接失敗！請檢查配置和服務狀態。</p>\n";
    }
} catch (Exception $e) {
    echo "<p class='error'>Redis 連接異常：" . $e->getMessage() . "</p>\n";
}

echo "</div>\n";

// 測試基本操作
echo "<div class='section'>\n";
echo "<h2>基本操作測試</h2>\n";

if (isset($redis) && $redis->getRedis()) {
    // 測試數據
    $testKey = 'test_key_' . time();
    $testValue = 'Hello Redis! 測試時間: ' . date('Y-m-d H:i:s');
    
    echo "<table>\n";
    echo "<tr><th>操作</th><th>結果</th></tr>\n";
    
    // 設置值
    $setResult = $redis->set($testKey, $testValue, 300); // 5分鐘過期
    echo "<tr><td>設置值 ($testKey)</td><td>" . ($setResult ? "<span class='success'>成功</span>" : "<span class='error'>失敗</span>") . "</td></tr>\n";
    
    // 獲取值
    $getValue = $redis->get($testKey);
    echo "<tr><td>獲取值 ($testKey)</td><td>" . ($getValue ? "<span class='success'>$getValue</span>" : "<span class='error'>未找到</span>") . "</td></tr>\n";
    
    // 檢查鍵是否存在
    $existsResult = $redis->exists($testKey);
    echo "<tr><td>檢查鍵是否存在 ($testKey)</td><td>" . ($existsResult ? "<span class='success'>存在</span>" : "<span class='error'>不存在</span>") . "</td></tr>\n";
    
    // 刪除鍵
    $deleteResult = $redis->delete($testKey);
    echo "<tr><td>刪除鍵 ($testKey)</td><td>" . ($deleteResult ? "<span class='success'>成功</span>" : "<span class='error'>失敗</span>") . "</td></tr>\n";
    
    // 再次檢查鍵是否存在
    $existsResult = $redis->exists($testKey);
    echo "<tr><td>再次檢查鍵是否存在 ($testKey)</td><td>" . ($existsResult ? "<span class='success'>存在</span>" : "<span class='error'>不存在</span>") . "</td></tr>\n";
    
    echo "</table>\n";
} else {
    echo "<p class='error'>無法執行基本操作測試，因為 Redis 連接失敗。</p>\n";
}

echo "</div>\n";

// 測試緩存功能
echo "<div class='section'>\n";
echo "<h2>緩存功能測試</h2>\n";

if (isset($redis) && $redis->getRedis()) {
    // 測試緩存鍵
    $cacheKeys = [
        'game_list_cache',
        'customer_cache_*',
        'game_account_cache_*',
        'game_item_cache_*'
    ];
    
    echo "<p>檢查已實現的緩存鍵：</p>\n";
    echo "<table>\n";
    echo "<tr><th>緩存鍵模式</th><th>匹配的鍵</th></tr>\n";
    
    foreach ($cacheKeys as $keyPattern) {
        echo "<tr><td>$keyPattern</td><td>";
        
        try {
            if (strpos($keyPattern, '*') !== false) {
                // 使用模式匹配查找鍵
                $keys = $redisInstance->keys($keyPattern);
                if (!empty($keys)) {
                    echo "<span class='success'>找到 " . count($keys) . " 個匹配的鍵</span><br>";
                    foreach ($keys as $key) {
                        echo "$key<br>";
                    }
                } else {
                    echo "<span class='error'>未找到匹配的鍵</span>";
                }
            } else {
                // 直接檢查特定鍵
                if ($redis->exists($keyPattern)) {
                    echo "<span class='success'>鍵存在</span>";
                } else {
                    echo "<span class='error'>鍵不存在</span>";
                }
            }
        } catch (Exception $e) {
            echo "<span class='error'>查詢錯誤：" . $e->getMessage() . "</span>";
        }
        
        echo "</td></tr>\n";
    }
    
    echo "</table>\n";
} else {
    echo "<p class='error'>無法執行緩存功能測試，因為 Redis 連接失敗。</p>\n";
}

echo "</div>\n";

// 環境信息
echo "<div class='section'>\n";
echo "<h2>環境信息</h2>\n";

echo "<table>\n";
echo "<tr><th>項目</th><th>值</th></tr>\n";
echo "<tr><td>PHP 版本</td><td>" . phpversion() . "</td></tr>\n";
echo "<tr><td>Redis 擴展</td><td>" . (extension_loaded('redis') ? "<span class='success'>已安裝</span>" : "<span class='error'>未安裝</span>") . "</td></tr>\n";

// 檢查 Redis 配置
echo "<tr><td>Redis 配置</td><td>";
if (file_exists(__DIR__ . '/.env')) {
    $envContent = file_get_contents(__DIR__ . '/.env');
    if (preg_match('/REDIS_HOST=(.*)/', $envContent, $matches)) {
        echo "主機: " . $matches[1] . "<br>";
    }
    if (preg_match('/REDIS_PORT=(.*)/', $envContent, $matches)) {
        echo "端口: " . $matches[1] . "<br>";
    }
    if (preg_match('/REDIS_PASSWORD=(.*)/', $envContent, $matches)) {
        echo "密碼: " . (empty($matches[1]) ? "<i>未設置</i>" : "<i>已設置</i>") . "<br>";
    }
} else {
    echo "<span class='error'>.env 文件不存在</span>";
}
echo "</td></tr>\n";

echo "</table>\n";
echo "</div>\n";

echo "</body>\n</html>";