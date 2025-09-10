<?php
// Simple Redis monitor API
// Endpoints (GET):
// action=stats
// action=scan&pattern=prefix*&type=list|set|zset|string|hash|all&count=100
// action=info&key=KEY
// action=members&key=KEY&start=0&count=50 (list/zset)
// action=hash&key=KEY&start=0&count=50
// action=get&key=KEY (string)

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/../../RedisConnection.php';

function json_error($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $conn = RedisConnection::getInstance();
    $redis = $conn->getRedis();

    if (!($redis instanceof Redis)) {
        // 降級：僅提供連線資訊，因模擬器無法列出原始 keys
        $status = $conn->getConnectionStatus();
        echo json_encode([
            'success' => true,
            'simulator' => true,
            'message' => '目前使用 Redis 模擬器，無法列出所有 keys。',
            'connection' => $status,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $action = $_GET['action'] ?? 'stats';

    switch ($action) {
        case 'stats': {
            $info = $redis->info();
            $dbsize = $redis->dbSize();
            echo json_encode([
                'success' => true,
                'dbsize' => $dbsize,
                'info' => $info,
            ], JSON_UNESCAPED_UNICODE);
            break;
        }

        case 'scan': {
            $pattern = isset($_GET['pattern']) && $_GET['pattern'] !== '' ? $_GET['pattern'] : '*';
            $wantType = $_GET['type'] ?? 'all';
            $count = max(10, min(1000, intval($_GET['count'] ?? 200)));
            $cursor = '0';
            $keys = [];
            $seen = 0;
            // Limit total returned keys to avoid huge payloads
            $maxReturn = 500;

            do {
                $result = $redis->scan($cursor, $pattern, $count);
                if ($result === false) {
                    break;
                }
                foreach ($result as $key) {
                    // filter by type if needed
                    $type = $redis->type($key);
                    $typeName = match ($type) {
                        Redis::REDIS_STRING => 'string',
                        Redis::REDIS_SET => 'set',
                        Redis::REDIS_LIST => 'list',
                        Redis::REDIS_ZSET => 'zset',
                        Redis::REDIS_HASH => 'hash',
                        default => 'other'
                    };
                    if ($wantType !== 'all' && $typeName !== $wantType) {
                        continue;
                    }

                    // size per type
                    $size = null;
                    switch ($typeName) {
                        case 'string': $size = $redis->strlen($key); break;
                        case 'list': $size = $redis->lLen($key); break;
                        case 'set': $size = $redis->sCard($key); break;
                        case 'zset': $size = $redis->zCard($key); break;
                        case 'hash': $size = $redis->hLen($key); break;
                        default: $size = null; break;
                    }
                    $ttl = $redis->ttl($key);

                    $keys[] = [
                        'key' => $key,
                        'type' => $typeName,
                        'size' => $size,
                        'ttl' => $ttl,
                    ];
                    $seen++;
                    if (count($keys) >= $maxReturn) break 2;
                }
            } while ($cursor !== '0');

            echo json_encode(['success' => true, 'keys' => $keys], JSON_UNESCAPED_UNICODE);
            break;
        }

        case 'info': {
            $key = $_GET['key'] ?? null;
            if (!$key) json_error('缺少 key');
            $exists = $redis->exists($key);
            if (!$exists) json_error('Key 不存在', 404);
            $type = $redis->type($key);
            $typeName = match ($type) {
                Redis::REDIS_STRING => 'string',
                Redis::REDIS_SET => 'set',
                Redis::REDIS_LIST => 'list',
                Redis::REDIS_ZSET => 'zset',
                Redis::REDIS_HASH => 'hash',
                default => 'other'
            };
            $ttl = $redis->ttl($key);
            $size = null;
            switch ($typeName) {
                case 'string': $size = $redis->strlen($key); break;
                case 'list': $size = $redis->lLen($key); break;
                case 'set': $size = $redis->sCard($key); break;
                case 'zset': $size = $redis->zCard($key); break;
                case 'hash': $size = $redis->hLen($key); break;
            }
            echo json_encode(['success' => true, 'key' => $key, 'type' => $typeName, 'ttl' => $ttl, 'size' => $size], JSON_UNESCAPED_UNICODE);
            break;
        }

        case 'members': {
            $key = $_GET['key'] ?? null;
            $start = intval($_GET['start'] ?? 0);
            $count = max(1, min(200, intval($_GET['count'] ?? 50)));
            if (!$key) json_error('缺少 key');
            $type = $redis->type($key);
            $typeName = match ($type) {
                Redis::REDIS_LIST => 'list',
                Redis::REDIS_ZSET => 'zset',
                default => 'other'
            };
            if ($typeName === 'other') json_error('僅支援 list/zset');

            if ($typeName === 'list') {
                $end = $start + $count - 1;
                $values = $redis->lRange($key, $start, $end);
                echo json_encode(['success' => true, 'items' => $values, 'start' => $start, 'count' => count($values)], JSON_UNESCAPED_UNICODE);
            } else { // zset
                $end = $start + $count - 1;
                // withscores true
                $values = $redis->zRange($key, $start, $end, true);
                echo json_encode(['success' => true, 'items' => $values, 'start' => $start, 'count' => count($values)], JSON_UNESCAPED_UNICODE);
            }
            break;
        }

        case 'hash': {
            $key = $_GET['key'] ?? null;
            $start = intval($_GET['start'] ?? 0);
            $count = max(1, min(200, intval($_GET['count'] ?? 50)));
            if (!$key) json_error('缺少 key');
            if ($redis->type($key) !== Redis::REDIS_HASH) json_error('僅支援 hash');
            $all = $redis->hGetAll($key);
            $slice = array_slice($all, $start, $count, true);
            echo json_encode(['success' => true, 'items' => $slice, 'total' => count($all), 'start' => $start], JSON_UNESCAPED_UNICODE);
            break;
        }

        case 'get': {
            $key = $_GET['key'] ?? null;
            if (!$key) json_error('缺少 key');
            if ($redis->type($key) !== Redis::REDIS_STRING) json_error('僅支援 string');
            $val = $redis->get($key);
            echo json_encode(['success' => true, 'value' => $val], JSON_UNESCAPED_UNICODE);
            break;
        }

        default:
            json_error('未知 action');
    }
} catch (Throwable $e) {
    json_error($e->getMessage(), 500);
}


