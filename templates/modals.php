<?php
// templates/modals.php
?>
<div class="modal fade" id="priceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Цены Ovoko для: <span id="modalProductTitle"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row" id="modalPriceList"></div>
            </div>
            <div class="modal-footer">
                <a id="modalSearchLink" href="#" target="_blank" class="btn btn-primary">Перейти в Ovoko</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editPriceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Редактировать цену: <span id="editProductTitle"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPriceForm" action="update_price.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="editProductId">
                    <div class="mb-3">
                        <label for="editProductPrice" class="form-label">Новая цена (€)</label>
                        <input type="number" step="0.01" class="form-control" name="new_price" id="editProductPrice" required>
                    </div>
                    <div class="mb-3">
                        <p>Рыночные показатели: <span id="marketMin"></span> - <span id="marketMax"></span> €, среднее: <span id="marketAvg"></span> €</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                </div>
            </form>
        </div>
    </div>
</div>