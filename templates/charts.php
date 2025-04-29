<?php
// templates/charts.php
?>
<section id="charts" class="mb-4">
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="chart-container">
                <h4 class="section-title">Распределение разницы цен</h4>
                <canvas id="diffHistogram"></canvas>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="chart-container">
                <h4 class="section-title">Распределение статусов цен</h4>
                <canvas id="priceStatusChart"></canvas>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="chart-container">
                <h4 class="section-title">Сравнение цен emg и Ovoko</h4>
                <canvas id="priceComparisonChart"></canvas>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="chart-container">
                <h4 class="section-title">Детальный анализ разницы цен</h4>
                <canvas id="diffChart"></canvas>
            </div>
        </div>
    </div>
</section>