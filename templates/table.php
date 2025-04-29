<!-- templates/table.php -->
<div class="table-container">
    <div class="mb-3">
        <button id="delete-selected" class="btn btn-danger">
            <i class="fas fa-trash-alt me-1"></i> Удалить выбранные
        </button>
    </div>
    <table id="analytics-table" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th><input type="checkbox" id="select-all"></th>
                <th>ID</th>
                <th>Изображение</th>
                <th>Название</th>
                <th>emg Price (€)</th>
                <th>Ovoko Avg (€)</th>
                <th>Ovoko Median (€)</th>
                <th>Диап. Ovoko (€)</th>
                <th>Все цены</th>
                <th>Δ to Avg (€)</th>
                <th>Статус</th>
                <th>Действия</th>
                <th>Обновлено</th>
                <th style="display:none;">diff</th>
                <th style="display:none;">last_updated</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>