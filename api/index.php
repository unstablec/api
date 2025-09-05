<?php
// Файл-заглушка для папки api
require_once 'proxy.php';

// Получаем путь после /api/
$requestUri = $_SERVER['REQUEST_URI'];
$apiPath = '';

// Убираем /api/ из начала пути
if (strpos($requestUri, '/api/') === 0) {
    $apiPath = substr($requestUri, 4);
} else {
    $apiPath = $requestUri;
}

// Обрабатываем запрос через прокси
handleApiProxy($apiPath);
?>