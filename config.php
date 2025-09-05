<?php
// Конфигурационный файл для прокси

// Настройки целевого API
define('DEFAULT_TARGET_API', 'https://vpvpay.store/api/');

// Настройки логирования
define('ENABLE_LOGGING', true);
define('MAX_LOG_ENTRIES', 1000);
define('LOG_DUPLICATES', false);

// Настройки безопасности
define('ALLOWED_METHODS', ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS']);
define('TIMEOUT_SECONDS', 30);

// Заголовки, которые не нужно передавать
define('EXCLUDED_HEADERS', ['host', 'transfer-encoding', 'connection']);

// Настройки для разработки
define('DEBUG_MODE', false);
?>