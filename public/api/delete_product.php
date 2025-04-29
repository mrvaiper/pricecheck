<?php
// public/api/delete_product.php
require_once BASE_PATH . 'config/db.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Недопустимый метод запроса');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $wc_ids = isset($input['wc_ids']) && is_array($input['wc_ids']) ? $input['wc_ids'] : [];

    if (empty($wc_ids)) {
        throw new Exception('ID товаров не указаны');
    }

    // Очистка и валидация wc_ids
    $wc_ids = array_map('trim', $wc_ids);
    $wc_ids = array_filter($wc_ids, function($id) {
        return !empty($id);
    });

    if (empty($wc_ids)) {
        throw new Exception('Нет валидных ID товаров');
    }

    // Проверка существования товаров
    $placeholders = implode(',', array_fill(0, count($wc_ids), '?'));
    $stmt = $pdo->prepare("SELECT wc_id FROM product_prices WHERE wc_id IN ($placeholders)");
    $stmt->execute($wc_ids);
    $existing_ids = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'wc_id');

    if (empty($existing_ids)) {
        throw new Exception('Ни один из указанных товаров не найден');
    }

    // Удаление товаров
    $stmt = $pdo->prepare("DELETE FROM product_prices WHERE wc_id IN ($placeholders)");
    $stmt->execute($wc_ids);

    $deleted_count = count($existing_ids);
    echo json_encode([
        'success' => true,
        'message' => "Удалено $deleted_count товар(ов)"
    ]);
} catch (Exception $e) {
    error_log("Ошибка в delete_product.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}