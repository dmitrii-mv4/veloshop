<?php
/**
 * Визуальный интерфейс для получения товаров из API ИМ
 * Сохранение файла: save как filter_products.php
 * Доступ по URL: http://ваш_сервер/filter_products.php
 */

// Базовый URL API
define('BASE_URL', 'http://176.62.189.27:62754/im/4371601201/');

// Список параметров API
$api_params = [
    'deep' => ['label' => 'Глубина погружения (0-7)', 'type' => 'number', 'placeholder' => '0'],
    'sklad' => ['label' => 'Коды складов (через запятую)', 'type' => 'text', 'placeholder' => '001,002'],
    'code' => ['label' => 'Коды товаров ИМ (через запятую)', 'type' => 'text'],
    'ncode' => ['label' => 'Коды товаров номенклатуры', 'type' => 'text'],
    'cena' => ['label' => 'Тип цены (ID из 1С)', 'type' => 'number'],
    'cena2' => ['label' => 'Доп. тип цены (старая)', 'type' => 'number'],
    'shop' => ['label' => 'Код магазина ИМ', 'type' => 'text', 'placeholder' => '00001'],
    'instore' => ['label' => 'Только с остатками', 'type' => 'checkbox', 'value' => '1'],
    'parent' => ['label' => 'Коды групп товаров', 'type' => 'text'],
    'updater' => ['label' => 'Учитывать остатки партнеров', 'type' => 'checkbox', 'value' => '1'],
    'noprops' => ['label' => 'Не показывать свойства', 'type' => 'checkbox', 'value' => '1'],
    'updater-sklad' => ['label' => 'Склады партнеров', 'type' => 'text'],
    'simple' => ['label' => 'Только простые товары', 'type' => 'checkbox', 'value' => '1'],
    'f-price' => ['label' => 'Только с ценами > 0', 'type' => 'checkbox', 'value' => '1'],
    'z-price' => ['label' => 'Показывать товары без цен', 'type' => 'checkbox', 'value' => '1'],
];

/**
 * Функция для выполнения HTTP-запроса
 */
function makeRequest($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => ['Accept: application/json']
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($response === false) {
        return ['error' => "Ошибка cURL: $error"];
    }
    
    if ($httpCode !== 200) {
        return ['error' => "HTTP ошибка: код $httpCode"];
    }
    
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => "Ошибка JSON: " . json_last_error_msg()];
    }
    
    return $data;
}

/**
 * Рекурсивно извлекает товары из структуры API
 */
function extractProducts($data) {
    $products = [];
    
    if (is_array($data)) {
        // Если это список товаров
        if (isset($data[0]) && is_array($data[0])) {
            foreach ($data as $item) {
                if (isset($item['code']) || isset($item['id'])) {
                    $products[] = $item;
                }
            }
        }
        // Если есть ключ 'products'
        elseif (isset($data['products']) && is_array($data['products'])) {
            $products = extractProducts($data['products']);
        }
        // Если есть ключ 'groups'
        elseif (isset($data['groups']) && is_array($data['groups'])) {
            foreach ($data['groups'] as $group) {
                $products = array_merge($products, extractProducts($group));
            }
        }
        // Рекурсивный поиск
        else {
            foreach ($data as $value) {
                if (is_array($value)) {
                    $products = array_merge($products, extractProducts($value));
                }
            }
        }
    }
    
    return $products;
}

/**
 * Форматирует цену
 */
function formatPrice($price) {
    if (is_numeric($price)) {
        return number_format($price, 2, '.', ' ') . ' ₽';
    }
    return $price ?? '—';
}

/**
 * Получает значение параметра из POST или GET
 */
function getParam($name, $default = '') {
    return $_POST[$name] ?? $_GET[$name] ?? $default;
}

// Проверяем, отправлена ли форма
$formSubmitted = !empty($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET');

// Собираем параметры для запроса
$requestParams = ['type' => 'json'];
foreach ($api_params as $key => $config) {
    $value = getParam($key);
    if ($value !== '' && $value !== null) {
        $requestParams[$key] = $value;
    }
}

// Выполняем запрос, если есть параметры
$products = [];
$apiError = null;
$requestUrl = '';

if ($formSubmitted && !empty($requestParams)) {
    $queryString = http_build_query($requestParams);
    $requestUrl = BASE_URL . '?' . $queryString;
    
    $response = makeRequest($requestUrl);
    
    if (isset($response['error'])) {
        $apiError = $response['error'];
    } else {
        $allProducts = extractProducts($response);
        $products = array_slice($allProducts, 0, 3); // Берем первые 3 товара
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Фильтр товаров API</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
            padding-bottom: 40px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #2c3e50;
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        .form-section {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .filter-group {
            margin-bottom: 15px;
        }
        .product-card {
            transition: transform 0.2s;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .product-image {
            height: 180px;
            object-fit: contain;
        }
        .price {
            font-size: 1.25rem;
            font-weight: bold;
            color: #2c3e50;
        }
        .stock {
            font-size: 0.9rem;
        }
        .badge-custom {
            background-color: #3498db;
        }
        .json-view {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            max-height: 400px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        .btn-custom {
            background-color: #2c3e50;
            color: white;
            padding: 10px 30px;
            font-weight: 500;
        }
        .btn-custom:hover {
            background-color: #1a252f;
            color: white;
        }
        .param-badge {
            background-color: #e9ecef;
            color: #495057;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.85rem;
            margin: 2px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Заголовок -->
        <div class="text-center mb-4">
            <h1 class="display-5 fw-bold text-primary">
                <i class="bi bi-filter-circle"></i> Фильтр товаров API
            </h1>
            <p class="lead text-muted">Настройте параметры для получения товаров из системы</p>
        </div>

        <!-- Форма фильтров -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-sliders"></i> Параметры фильтрации</h5>
                <button type="button" class="btn btn-sm btn-light" onclick="resetForm()">
                    <i class="bi bi-arrow-clockwise"></i> Сбросить
                </button>
            </div>
            <div class="card-body">
                <form method="get" id="filterForm">
                    <div class="row">
                        <?php foreach ($api_params as $key => $config): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="filter-group">
                                <label for="<?= $key ?>" class="form-label small">
                                    <strong><?= $config['label'] ?></strong>
                                </label>
                                
                                <?php if ($config['type'] == 'checkbox'): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           name="<?= $key ?>" 
                                           id="<?= $key ?>" 
                                           value="<?= $config['value'] ?>"
                                           <?= getParam($key) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="<?= $key ?>">
                                        Включить
                                    </label>
                                </div>
                                <?php else: ?>
                                <input type="<?= $config['type'] ?>" 
                                       class="form-control form-control-sm" 
                                       name="<?= $key ?>" 
                                       id="<?= $key ?>"
                                       value="<?= htmlspecialchars(getParam($key)) ?>"
                                       placeholder="<?= $config['placeholder'] ?? '' ?>"
                                       <?php if ($key == 'deep'): ?>
                                       min="0" max="7"
                                       <?php endif; ?>>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="submit" class="btn btn-custom me-md-2">
                            <i class="bi bi-search"></i> Получить товары
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="showAdvanced()">
                            <i class="bi bi-code-slash"></i> Ссылка API
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Результаты -->
        <?php if ($formSubmitted): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> Результаты (первые 3 товара)</h5>
            </div>
            <div class="card-body">
                
                <?php if ($apiError): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <strong>Ошибка:</strong> <?= htmlspecialchars($apiError) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Статистика -->
                <?php if (!$apiError): ?>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <strong>Найдено товаров:</strong> <?= count($products) ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-secondary">
                            <i class="bi bi-link-45deg"></i>
                            <strong>Запрос API:</strong>
                            <a href="<?= htmlspecialchars($requestUrl) ?>" target="_blank" class="small d-block text-truncate">
                                <?= htmlspecialchars(substr($requestUrl, 0, 100)) ?>...
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Карточки товаров -->
                <?php if (count($products) > 0): ?>
                <div class="row g-4 mb-4">
                    <?php foreach ($products as $index => $product): ?>
                    <div class="col-md-4">
                        <div class="card product-card h-100">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary">#<?= $index + 1 ?></span>
                                    <?php if (isset($product['stock']) && $product['stock'] > 0): ?>
                                    <span class="badge bg-success">В наличии</span>
                                    <?php elseif (isset($product['stock'])): ?>
                                    <span class="badge bg-warning text-dark">Под заказ</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if (isset($product['image']) && $product['image']): ?>
                            <img src="<?= htmlspecialchars($product['image']) ?>" 
                                 class="card-img-top product-image p-3" 
                                 alt="Изображение товара"
                                 onerror="this.src='https://via.placeholder.com/300x180?text=Нет+изображения'">
                            <?php else: ?>
                            <div class="text-center p-3">
                                <i class="bi bi-box-seam" style="font-size: 100px; color: #6c757d;"></i>
                                <p class="text-muted small mt-2">Нет изображения</p>
                            </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h6 class="card-title">
                                    <?= htmlspecialchars($product['name'] ?? $product['title'] ?? 'Без названия') ?>
                                </h6>
                                
                                <?php if (isset($product['code'])): ?>
                                <p class="card-text small text-muted">
                                    <i class="bi bi-upc-scan"></i> Код: <?= htmlspecialchars($product['code']) ?>
                                </p>
                                <?php endif; ?>
                                
                                <?php if (isset($product['article'])): ?>
                                <p class="card-text small text-muted">
                                    <i class="bi bi-tag"></i> Артикул: <?= htmlspecialchars($product['article']) ?>
                                </p>
                                <?php endif; ?>
                                
                                <div class="mt-3">
                                    <?php if (isset($product['price'])): ?>
                                    <div class="price"><?= formatPrice($product['price']) ?></div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($product['stock'])): ?>
                                    <div class="stock mt-2">
                                        <i class="bi bi-shop"></i> Остаток: 
                                        <span class="fw-bold"><?= $product['stock'] ?> шт.</span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (isset($product['category'])): ?>
                                <div class="mt-2">
                                    <span class="badge bg-secondary"><?= htmlspecialchars($product['category']) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- JSON данные -->
                <div class="mt-4">
                    <h6 class="mb-3"><i class="bi bi-code-square"></i> Данные в формате JSON:</h6>
                    <pre class="json-view"><?= json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                </div>

                <!-- Параметры запроса -->
                <div class="mt-4">
                    <h6 class="mb-3"><i class="bi bi-gear"></i> Использованные параметры:</h6>
                    <div>
                        <?php foreach ($requestParams as $key => $value): ?>
                        <?php if ($value !== ''): ?>
                        <span class="param-badge">
                            <strong><?= $key ?>:</strong> <?= htmlspecialchars($value) ?>
                        </span>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 60px; color: #6c757d;"></i>
                    <h5 class="mt-3 text-muted">Товары не найдены</h5>
                    <p class="text-muted">Попробуйте изменить параметры фильтрации</p>
                </div>
                <?php endif; ?>
                
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Инструкция -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-question-circle"></i> Инструкция</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Основные параметры:</h6>
                        <ul class="small">
                            <li><strong>deep:</strong> Глубина вложенности групп товаров (0-7)</li>
                            <li><strong>sklad:</strong> Коды складов через запятую (001,002)</li>
                            <li><strong>instore:</strong> Показывать только товары в наличии</li>
                            <li><strong>f-price:</strong> Только товары с ценой > 0</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Примеры использования:</h6>
                        <ul class="small">
                            <li>Для получения всех товаров: установите deep=5</li>
                            <li>Для фильтра по складам: укажите коды складов</li>
                            <li>Для поиска конкретного товара: используйте code или ncode</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно для ссылки API -->
    <div class="modal fade" id="apiModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-link-45deg"></i> Ссылка API запроса</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if ($formSubmitted && !$apiError): ?>
                    <div class="mb-3">
                        <label class="form-label">Полная ссылка:</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($requestUrl) ?>" readonly>
                    </div>
                    <button class="btn btn-outline-primary w-100" onclick="copyToClipboard('<?= htmlspecialchars($requestUrl) ?>')">
                        <i class="bi bi-clipboard"></i> Скопировать ссылку
                    </button>
                    <?php else: ?>
                    <p class="text-muted">Сначала выполните поиск товаров, чтобы получить ссылку</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Скрипты -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Сброс формы
        function resetForm() {
            document.getElementById('filterForm').reset();
        }
        
        // Показать модальное окно с ссылкой API
        function showAdvanced() {
            const modal = new bootstrap.Modal(document.getElementById('apiModal'));
            modal.show();
        }
        
        // Копирование в буфер обмена
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Ссылка скопирована в буфер обмена!');
            });
        }
        
        // Автоматическая отправка формы при изменении checkbox
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        this.value = '1';
                    }
                });
            });
        });
    </script>
</body>
</html>