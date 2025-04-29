<?php
// public/api/products.php
require_once BASE_PATH . 'config/db.php';
require_once BASE_PATH . 'includes/functions.php'

header('Content-Type: application/json');

try {
    // Логирование запроса
    error_log("Запрос к api/products.php: " . print_r($_GET, true));

    $draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
    $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
    $length = isset($_GET['length']) ? intval($_GET['length']) : 100; // По умолчанию 100
    $search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';
    $orderColumnIndex = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0;
    $orderDir = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'asc';
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

    // Маппинг индексов столбцов
    $columns = [
        1 => 'wc_id',
        4 => 'wc_price',
        12 => 'last_updated'
    ];
    $orderColumn = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'wc_id';

    // Подготовка запроса
    $sql = "SELECT wc_id, title, wc_price, wc_image, wc_link, ovoko_search_url, last_updated, ovoko, ovoko_thumbs
            FROM product_prices";
    $countSql = "SELECT COUNT(*) as total FROM product_prices";
    $where = [];
    $params = [];

    if (!empty($search)) {
        $where[] = "(wc_id LIKE ? OR title LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($filter !== 'all') {
        if ($filter === 'high') {
            $where[] = "wc_price - (
                SELECT AVG(CAST(REPLACE(JSON_UNQUOTE(JSON_EXTRACT(ovoko, '$[*]')), ' €', '') AS DECIMAL))
                FROM product_prices p2
                WHERE p2.wc_id = product_prices.wc_id
            ) > 10";
        } elseif ($filter === 'low') {
            $where[] = "wc_price - (
                SELECT AVG(CAST(REPLACE(JSON_UNQUOTE(JSON_EXTRACT(ovoko, '$[*]')), ' €', '') AS DECIMAL))
                FROM product_prices p2
                WHERE p2.wc_id = product_prices.wc_id
            ) < -5";
        } elseif ($filter === 'inactive') {
            $where[] = "last_updated < NOW() - INTERVAL 14 DAY";
        }
    }

    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
        $countSql .= " WHERE " . implode(" AND ", $where);
    }

    $sql .= " ORDER BY $orderColumn " . ($orderDir === 'desc' ? 'DESC' : 'ASC');
    $sql .= " LIMIT ?, ?";
    $params[] = $start;
    $params[] = $length;

    // Логирование SQL-запроса
    error_log("SQL запрос: $sql");
    error_log("Параметры: " . print_r($params, true));

    // Получение общего количества записей
    $stmt = $pdo->prepare($countSql);
    $stmt->execute(array_slice($params, 0, count($params) - 2));
    $totalRecords = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Получение записей
    $stmt = $pdo->prepare($sql);
    foreach ($params as $index => $param) {
        $stmt->bindValue($index + 1, $param, is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Подготовка данных
    $data = [];
    foreach ($products as $p) {
        $ovRaw = json_decode($p['ovoko'], true) ?: [];
        $stats = calculatePriceStats($ovRaw);
        if (empty($stats['avg'])) continue;

        $idealPrice = $stats['median'] * 1.05;
        $diff = round($p['wc_price'] - $stats['avg'], 2);
        $status = getStatus($diff);
        $lastUpdateTime = strtotime($p['last_updated']);
        $isOld = (time() - $lastUpdateTime) > (14 * 24 * 60 * 60);

        $data[] = [
            '<input type="checkbox" class="product-select">',
            htmlspecialchars($p['wc_id']),
            $p['wc_image'] ? '<img src="' . htmlspecialchars($p['wc_image']) . '" class="wc-thumb rounded shadow-sm" alt="">' :
                '<div class="wc-thumb rounded d-flex align-items-center justify-content-center bg-light"><i class="fas fa-image text-muted"></i></div>',
            '<strong>' . htmlspecialchars($p['title']) . '</strong>' . ($isOld ? '<span class="tag-pill bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i> Устаревшие данные</span>' : ''),
            number_format($p['wc_price'], 2, '.', '') . ' €',
            number_format($stats['avg'], 2, '.', '') . ' €',
            number_format($stats['median'], 2, '.', '') . ' €',
            number_format($stats['min'], 2, '.', '') . ' € - ' . number_format($stats['max'], 2, '.', '') . ' €',
            '<span class="ovoko-prices" data-bs-toggle="modal" data-bs-target="#priceModal" ' .
            'data-product-id="' . $p['wc_id'] . '" ' .
            'data-product-title="' . htmlspecialchars($p['title']) . '" ' .
            'data-product-prices="' . htmlspecialchars(json_encode($ovRaw, JSON_HEX_QUOT | JSON_HEX_APOS)) . '" ' .
            'data-wc-price="' . $p['wc_price'] . '" ' .
            'data-product-thumbs="' . htmlspecialchars(json_encode(json_decode($p['ovoko_thumbs'], true) ?: [], JSON_HEX_QUOT | JSON_HEX_APOS)) . '" ' .
            'data-search-url="' . htmlspecialchars($p['ovoko_search_url']) . '">' .
            '<i class="fas fa-list-ul me-1"></i> Показать (' . count($ovRaw) . ')</span>',
            '<span class="' . $status['class'] . '">' . number_format($diff, 2, '.', '') . ' €</span>',
            '<span class="tag-pill ' . $status['badge'] . '">' . $status['text'] . '</span>',
            '<div class="d-flex justify-content-around">' .
            '<a href="' . htmlspecialchars($p['wc_link']) . '" target="_blank" class="action-btn" title="Открыть в WooCommerce"><i class="fas fa-external-link-alt"></i></a>' .
            '<a href="' . htmlspecialchars($p['ovoko_search_url']) . '" target="_blank" class="action-btn" title="Поиск в Ovoko"><i class="fas fa-search"></i></a>' .
            '<span class="action-btn" title="Редактировать цену" data-bs-toggle="modal" data-bs-target="#editPriceModal" ' .
            'data-product-id="' . $p['wc_id'] . '" ' .
            'data-product-title="' . htmlspecialchars($p['title']) . '" ' .
            'data-product-price="' . $p['wc_price'] . '" ' .
            'data-market-avg="' . $stats['avg'] . '" ' .
            'data-market-min="' . $stats['min'] . '" ' .
            'data-market-max="' . $stats['max'] . '" ' .
            'data-ideal-price="' . number_format($idealPrice, 2, '.', '') . '"><i class="fas fa-edit"></i></span>' .
            '<span class="action-btn delete-product" title="Удалить товар" ' .
            'data-product-id="' . htmlspecialchars($p['wc_id']) . '" ' .
            'data-product-title="' . htmlspecialchars($p['title']) . '"><i class="fas fa-trash-alt"></i></span>' .
            '</div>',
            '<span class="last-updated" title="' . $p['last_updated'] . '">' . date('d.m.Y', strtotime($p['last_updated'])) . '</span>',
            number_format($idealPrice, 2, '.', '') . ' €',
            $diff,
            $lastUpdateTime
        ];
    }

    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $totalRecords,
        'data' => $data
    ]);
} catch (Exception $e) {
    error_log("Ошибка в api/products.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}