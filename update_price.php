<?php
// update_price.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Логика обновления цены
    $product_id = $_POST['product_id'];
    $new_price = $_POST['new_price'];
    // Пример: обновление в базе данных
    // require_once 'config/db.php';
    // $pdo->prepare("UPDATE product_prices SET wc_price = ? WHERE wc_id = ?")->execute([$new_price, $product_id]);
    header('Location: index.php');
    exit;
}