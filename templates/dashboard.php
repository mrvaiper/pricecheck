<?php
// templates/dashboard.php
?>
<section id="dashboard">
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1><i class="fas fa-tachometer-alt me-2"></i>Аналитика цен emg ↔ Ovoko</h1>
                <p class="mb-0">Сравнительный анализ цен между вашим магазином и конкурентами</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <button id="bulkPriceUpdate" class="btn btn-primary">
                    <i class="fas fa-sync-alt me-1"></i> Массовое обновление цен
                </button>
            </div>
        </div>
    </div>

    <!-- Кнопки фильтров -->
    <div class="mb-3">
        <button id="showAll" class="btn btn-secondary me-2">Все товары</button>
        <button id="showHigh" class="btn btn-danger me-2">Завышенные</button>
        <button id="showLow" class="btn btn-warning me-2">Недооцененные</button>
        <button id="showInactive" class="btn btn-info">Неактивные</button>
    </div>

    <!-- Карточки с метриками -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Всего товаров</h5>
                    <p class="card-text"><?= $totalProducts ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Завышенные (>10€)</h5>
                    <p class="card-text"><?= $overpriced ?> (<?= $percentOverpriced ?>%)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Недооцененные (<-5€)</h5>
                    <p class="card-text"><?= $underpriced ?> (<?= $percentUnderpriced ?>%)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Рыночные</h5>
                    <p class="card-text"><?= $fairPrice ?> (<?= $percentFair ?>%)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Средняя разница</h5>
                    <p class="card-text"><?= number_format($avgDiff, 2) ?> € (Медиана: <?= number_format($medianDiff, 2) ?> €, STD: <?= number_format($stdDev, 2) ?> €)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-dark">
                <div class="card-body">
                    <h5 class="card-title">Макс. разница</h5>
                    <p class="card-text"><?= number_format($maxDiffValue, 2) ?> €<br><small><?= htmlspecialchars($maxDiffProduct) ?></small></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-dark">
                <div class="card-body">
                    <h5 class="card-title">Мин. разница</h5>
                    <p class="card-text"><?= number_format($minDiffValue, 2) ?> €<br><small><?= htmlspecialchars($minDiffProduct) ?></small></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-secondary">
                <div class="card-body">
                    <h5 class="card-title">Неактивные (>14 дней)</h5>
                    <p class="card-text"><?= $inactiveProducts ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Топ завышенных и заниженных продуктов -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h4>Топ 5 завышенных товаров</h4>
            <table class="table table-striped">
                <thead><tr><th>ID</th><th>Название</th><th>Разница (€)</th></tr></thead>
                <tbody>
                    <?php foreach ($topOverpriced as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['id']) ?></td>
                            <td><?= htmlspecialchars($item['title']) ?></td>
                            <td><?= number_format($item['diff'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <h4>Топ 5 заниженных товаров</h4>
            <table class="table table-striped">
                <thead><tr><th>ID</th><th>Название</th><th>Разница (€)</th></tr></thead>
                <tbody>
                    <?php foreach ($topUnderpriced as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['id']) ?></td>
                            <td><?= htmlspecialchars($item['title']) ?></td>
                            <td><?= number_format($item['diff'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Анализ категорий -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h4>Топ 5 категорий по количеству товаров</h4>
            <table class="table table-striped">
                <thead><tr><th>Категория</th><th>Количество</th><th>Средняя разница (€)</th></tr></thead>
                <tbody>
                    <?php foreach ($topCategoriesByCount as $cat): ?>
                        <tr>
                            <td><?= ucfirst(htmlspecialchars($cat['category'])) ?></td>
                            <td><?= $cat['count'] ?></td>
                            <td><?= number_format($cat['avg_diff'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <h4>Топ 5 категорий по средней разнице</h4>
            <table class="table table-striped">
                <thead><tr><th>Категория</th><th>Количество</th><th>Средняя разница (€)</th></tr></thead>
                <tbody>
                    <?php foreach ($topCategoriesByDiff as $cat): ?>
                        <tr>
                            <td><?= ucfirst(htmlspecialchars($cat['category'])) ?></td>
                            <td><?= $cat['count'] ?></td>
                            <td><?= number_format($cat['avg_diff'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>