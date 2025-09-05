<?php
// Главный файл для обработки запросов к прокси
require_once 'api/proxy.php';

// Если запрос идет к /api/, перенаправляем на прокси
$requestUri = $_SERVER['REQUEST_URI'];
if (strpos($requestUri, '/api/') === 0) {
    // Убираем /api/ из начала пути для передачи в прокси
    $apiPath = substr($requestUri, 4);
    handleApiProxy($apiPath);
} else {
    // Показываем простую страницу статуса
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>API Proxy Status</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .status { background: #e8f5e8; padding: 20px; border-radius: 5px; }
            .info { background: #f0f8ff; padding: 15px; border-radius: 5px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <h1>API Proxy Server</h1>
        <div class="status">
            <h2>✅ Прокси сервер работает</h2>
            <p>Целевой API: <strong>https://vpvpay.store/api/</strong></p>
        </div>
        <div class="info">
            <h3>Использование:</h3>
            <p>Отправляйте запросы на <code>/api/[endpoint]</code></p>
            <p>Пример: <code>/api/users</code> будет проксирован на <code>https://vpvpay.store/api/users</code></p>
            <p>Все запросы и ответы логируются в файл <code>logs/api_log.json</code></p>
        </div>
    </body>
    </html>
    <?php
}
?>