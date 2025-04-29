<?php
// includes/functions.php
function calculatePriceStats($prices) {
    $ovNumeric = array_map(function($price) {
        return floatval(str_replace(['€', ' ', ' '], ['', '', ''], $price));
    }, $prices);

    if (empty($ovNumeric)) {
        return ['avg' => 0, 'median' => 0, 'min' => 0, 'max' => 0];
    }

    $avg = array_sum($ovNumeric) / count($ovNumeric);
    sort($ovNumeric);
    $middle = floor(count($ovNumeric) / 2);
    $median = count($ovNumeric) % 2 ? $ovNumeric[$middle] : ($ovNumeric[$middle - 1] + $ovNumeric[$middle]) / 2;
    $min = min($ovNumeric);
    $max = max($ovNumeric);

    return ['avg' => $avg, 'median' => $median, 'min' => $min, 'max' => $max];
}

function getStatus($diff) {
    if ($diff > 20) {
        return ['class' => 'diff-highest', 'text' => 'Сильно завышена', 'badge' => 'badge-diff-highest'];
    } elseif ($diff > 10) {
        return ['class' => 'diff-high', 'text' => 'Завышена', 'badge' => 'badge-diff-high'];
    } elseif ($diff > 0) {
        return ['class' => 'diff-medium', 'text' => 'Немного выше', 'badge' => 'badge-diff-medium'];
    } else {
        return ['class' => 'diff-negative', 'text' => 'Ниже рынка', 'badge' => 'badge-diff-negative'];
    }
}