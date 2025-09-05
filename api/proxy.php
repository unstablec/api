<?php
// Основной файл прокси с логированием

// Настройки
define('TARGET_API_BASE', 'https://vpvpay.store/api/');
define('LOG_FILE', __DIR__ . '/../logs/api_log.json');

function handleApiProxy($path) {
    // Создаем директорию для логов если не существует
    $logDir = dirname(LOG_FILE);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Получаем данные запроса
    $method = $_SERVER['REQUEST_METHOD'];
    $headers = getallheaders();
    $queryString = $_SERVER['QUERY_STRING'];
    $requestBody = file_get_contents('php://input');
    
    // Формируем URL для целевого API
    $targetUrl = TARGET_API_BASE . ltrim($path, '/');
    if ($queryString) {
        $targetUrl .= '?' . $queryString;
    }
    
    // Подготавливаем заголовки для cURL
    $curlHeaders = [];
    foreach ($headers as $name => $value) {
        // Пропускаем заголовки хоста
        if (strtolower($name) !== 'host') {
            $curlHeaders[] = $name . ': ' . $value;
        }
    }
    
    // Выполняем запрос к целевому API
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $targetUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $curlHeaders,
        CURLOPT_HEADER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'API-Proxy/1.0'
    ]);
    
    // Устанавливаем тело запроса только для методов, которые его поддерживают
    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE']) && !empty($requestBody)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        http_response_code(500);
        echo json_encode(['error' => 'Proxy error: ' . $error]);
        return;
    }
    
    // Разделяем заголовки и тело ответа
    $responseHeaders = substr($response, 0, $headerSize);
    $responseBody = substr($response, $headerSize);
    
    // Логируем запрос и ответ
    logApiCall($method, $targetUrl, $headers, $requestBody, $httpCode, $responseHeaders, $responseBody);
    
    // Отправляем заголовки ответа клиенту
    $headerLines = explode("\r\n", $responseHeaders);
    foreach ($headerLines as $header) {
        if (strpos($header, ':') !== false && !preg_match('/^HTTP\//', $header)) {
            // Пропускаем некоторые заголовки, которые могут конфликтовать
            $headerName = strtolower(explode(':', $header)[0]);
            if (!in_array($headerName, ['transfer-encoding', 'connection'])) {
                header($header);
            }
        }
    }
    
    // Устанавливаем код ответа
    http_response_code($httpCode);
    
    // Отправляем тело ответа
    echo $responseBody;
}

function logApiCall($method, $url, $requestHeaders, $requestBody, $responseCode, $responseHeaders, $responseBody) {
    $timestamp = date('Y-m-d H:i:s');
    $requestHash = md5($method . $url . $requestBody);
    
    // Проверяем, есть ли уже такой запрос в логах (избегаем дублей)
    if (isDuplicateRequest($requestHash)) {
        return;
    }
    
    $logEntry = [
        'timestamp' => $timestamp,
        'request_hash' => $requestHash,
        'request' => [
            'method' => $method,
            'url' => $url,
            'headers' => $requestHeaders,
            'body' => $requestBody
        ],
        'response' => [
            'code' => $responseCode,
            'headers' => parseHeaders($responseHeaders),
            'body' => $responseBody
        ]
    ];
    
    // Читаем существующие логи
    $existingLogs = [];
    if (file_exists(LOG_FILE)) {
        $content = file_get_contents(LOG_FILE);
        if ($content) {
            $existingLogs = json_decode($content, true) ?: [];
        }
    }
    
    // Добавляем новую запись
    $existingLogs[] = $logEntry;
    
    // Ограничиваем количество записей (последние 1000)
    if (count($existingLogs) > 1000) {
        $existingLogs = array_slice($existingLogs, -1000);
    }
    
    // Сохраняем логи
    file_put_contents(LOG_FILE, json_encode($existingLogs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function isDuplicateRequest($requestHash) {
    if (!file_exists(LOG_FILE)) {
        return false;
    }
    
    $content = file_get_contents(LOG_FILE);
    if (!$content) {
        return false;
    }
    
    $logs = json_decode($content, true);
    if (!$logs) {
        return false;
    }
    
    // Проверяем последние 100 записей на дубли
    $recentLogs = array_slice($logs, -100);
    foreach ($recentLogs as $log) {
        if (isset($log['request_hash']) && $log['request_hash'] === $requestHash) {
            return true;
        }
    }
    
    return false;
}

function parseHeaders($headerString) {
    $headers = [];
    $lines = explode("\r\n", $headerString);
    
    foreach ($lines as $line) {
        if (strpos($line, ':') !== false) {
            list($name, $value) = explode(':', $line, 2);
            $headers[trim($name)] = trim($value);
        }
    }
    
    return $headers;
}
?>