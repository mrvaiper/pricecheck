<?php
// includes/analytics.php
require_once BASE_PATH . 'config/db.php';
require_once BASE_PATH . 'includes/functions.php';

$sql = "SELECT wc_id, title, wc_price, wc_image, wc_link, ovoko_search_url, last_updated, ovoko, ovoko_thumbs
        FROM product_prices
        ORDER BY wc_id ASC";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$dataDiff = [];
$dataPrices = [];
$dataAvg = [];
$categories = [];
$differences = [];
$totalProducts = 0;
$overpriced = 0;
$underpriced = 0;
$fairPrice = 0;
$inactiveProducts = 0;

foreach ($products as $p) {
    $ov = json_decode($p['ovoko'], true) ?: [];
    if (empty($ov)) continue;
    
    $totalProducts++;
    
    $ovNumeric = [];
    foreach ($ov as $priceStr) {
        $num = floatval(str_replace(['€', ' ', ' '], ['', '', ''], $priceStr));
        $ovNumeric[] = $num;
    }
    
    $avg = array_sum($ovNumeric) / count($ovNumeric);
    $diff = round($p['wc_price'] - $avg, 2);
    
    $labels[] = "ID " . $p['wc_id'];
    $dataDiff[] = $diff;
    $dataPrices[] = $p['wc_price'];
    $dataAvg[] = $avg;
    $differences[] = [
        'id' => $p['wc_id'],
        'title' => $p['title'],
        'diff' => $diff
    ];
    
    if ($diff > 10) {
        $overpriced++;
    } elseif ($diff < -5) {
        $underpriced++;
    } else {
        $fairPrice++;
    }
    
    $lastUpdateTime = strtotime($p['last_updated']);
    if ((time() - $lastUpdateTime) > (14 * 24 * 60 * 60)) {
        $inactiveProducts++;
    }
    
    $firstWord = strtolower(explode(' ', $p['title'])[0]);
    if (!isset($categories[$firstWord])) {
        $categories[$firstWord] = ['count' => 0, 'sum_diff' => 0];
    }
    $categories[$firstWord]['count']++;
    $categories[$firstWord]['sum_diff'] += $diff;
}

$catList = [];
foreach ($categories as $name => $data) {
    $catList[] = [
        'category' => $name,
        'count' => $data['count'],
        'avg_diff' => $data['sum_diff'] / $data['count']
    ];
}

usort($catList, function($a, $b) {
    return $b['count'] <=> $a['count'];
});
$topCategoriesByCount = array_slice($catList, 0, 5);

usort($catList, function($a, $b) {
    return abs($b['avg_diff']) <=> abs($a['avg_diff']);
});
$topCategoriesByDiff = array_slice($catList, 0, 5);

usort($differences, function($a, $b) {
    return $b['diff'] <=> $a['diff'];
});
$topOverpriced = array_slice($differences, 0, 5);

usort($differences, function($a, $b) {
    return $a['diff'] <=> $b['diff'];
});
$topUnderpriced = array_slice($differences, 0, 5);

$avgDiff = count($dataDiff) > 0 ? array_sum($dataDiff) / count($dataDiff) : 0;

$medianDiff = 0;
if (count($dataDiff) > 0) {
    $sortedDiff = $dataDiff;
    sort($sortedDiff);
    $middle = floor(count($sortedDiff) / 2);
    if (count($sortedDiff) % 2) {
        $medianDiff = $sortedDiff[$middle];
    } else {
        $medianDiff = ($sortedDiff[$middle - 1] + $sortedDiff[$middle]) / 2;
    }
}

$variance = 0;
if (count($dataDiff) > 1) {
    foreach ($dataDiff as $diff) {
        $variance += pow($diff - $avgDiff, 2);
    }
    $variance = $variance / (count($dataDiff) - 1);
}
$stdDev = round(sqrt($variance), 2);

$maxDiffValue = count($dataDiff) > 0 ? max($dataDiff) : 0;
$minDiffValue = count($dataDiff) > 0 ? min($dataDiff) : 0;
$maxDiffProduct = '';
$minDiffProduct = '';
foreach ($differences as $diff) {
    if ($diff['diff'] == $maxDiffValue) {
        $maxDiffProduct = $diff['id'] . ': ' . $diff['title'];
    }
    if ($diff['diff'] == $minDiffValue) {
        $minDiffProduct = $diff['id'] . ': ' . $diff['title'];
    }
}

$percentOverpriced = $totalProducts > 0 ? round(($overpriced / $totalProducts) * 100, 1) : 0;
$percentUnderpriced = $totalProducts > 0 ? round(($underpriced / $totalProducts) * 100, 1) : 0;
$percentFair = $totalProducts > 0 ? round(($fairPrice / $totalProducts) * 100, 1) : 0;

$bins = [
    '< -20' => 0,
    '-20 to -10' => 0,
    '-10 to 0' => 0,
    '0 to 10' => 0,
    '10 to 20' => 0,
    '> 20' => 0
];
foreach ($dataDiff as $diff) {
    if ($diff < -20) {
        $bins['< -20']++;
    } elseif ($diff < -10) {
        $bins['-20 to -10']++;
    } elseif ($diff < 0) {
        $bins['-10 to 0']++;
    } elseif ($diff < 10) {
        $bins['0 to 10']++;
    } elseif ($diff < 20) {
        $bins['10 to 20']++;
    } else {
        $bins['> 20']++;
    }
}
$binsJson = json_encode($bins);