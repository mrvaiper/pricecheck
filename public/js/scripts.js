// public/js/scripts.js
$(document).ready(function() {
    console.log('scripts.js загружен, jQuery:', typeof $);

    if (!$.fn.DataTable) {
        console.error('DataTables не загружен');
        return;
    }

    var table = $('#analytics-table').DataTable({
        responsive: true,
        serverSide: true,
        processing: true,
        pageLength: 100,
        ajax: {
            url: '/api/products.php',
            type: 'GET',
            dataSrc: function(json) {
                console.log('Полученные данные:', json);
                if (!json || !json.data) {
                    console.error('Некорректный ответ от API:', json);
                    return [];
                }
                return json.data;
            },
            error: function(xhr, error, thrown) {
                console.error('Ошибка AJAX:', xhr.responseText, error, thrown);
            }
        },
        columns: [
            { data: 0 }, // Чекбокс
            { data: 1 }, // ID
            { data: 2 }, // Изображение
            { data: 3 }, // Название
            { data: 4 }, // emg Price (€)
            { data: 5 }, // Ovoko Avg (€)
            { data: 6 }, // Ovoko Median (€)
            { data: 7 }, // Диап. Ovoko (€)
            { data: 8 }, // Все цены + фото
            { data: 9 }, // Δ to Avg (€)
            { data: 10 }, // Статус
            { data: 11 }, // Действия
            { data: 12 }, // Обновлено
            { data: 13 }, // Идеальная цена (€)
            { data: 14, visible: false }, // diff
            { data: 15, visible: false } // last_updated
        ],
        order: [[9, 'desc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json' },
        drawCallback: function(settings) {
            console.log('Таблица отрисована');
            console.log('Найдено кнопок .delete-product:', $('.delete-product').length);
            $('.delete-product').each(function() {
                console.log('Кнопка:', $(this).data('product-id'), $(this).data('product-title'));
            });
        }
    });

    // Тестовый обработчик для проверки кликов
    $(document).on('click', '.delete-product', function(e) {
        e.preventDefault();
        console.log('Клик по .delete-product зарегистрирован');
        var productId = $(this).data('product-id');
        var productTitle = $(this).data('product-title');
        console.log('Данные кнопки:', { productId, productTitle });

        if (!productId) {
            console.error('productId не определён');
            alert('Ошибка: ID товара не указан');
            return;
        }

        if (confirm('Вы уверены, что хотите удалить товар "' + productTitle + '" (ID: ' + productId + ')?')) {
            console.log('Подтверждено удаление, отправка AJAX:', productId);
            $.ajax({
                url: '/api/delete_product.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ wc_ids: [productId] }),
                success: function(response) {
                    console.log('Ответ от delete_product.php:', response);
                    if (response.success) {
                        alert(response.message);
                        table.ajax.reload();
                    } else {
                        console.error('Ошибка сервера:', response.message);
                        alert('Ошибка: ' + response.message);
                    }
                },
                error: function(xhr) {
                    console.error('Ошибка AJAX:', xhr.status, xhr.responseText);
                    alert('Ошибка сервера: ' + xhr.status + ' ' + xhr.responseText);
                }
            });
        } else {
            console.log('Удаление отменено');
        }
    });

    // Функции фильтрации
    function filterRows(type) {
        table.ajax.url('/api/products.php?filter=' + type).load();
    }

    $('#showAll').click(() => filterRows('all'));
    $('#showHigh').click(() => filterRows('high'));
    $('#showLow').click(() => filterRows('low'));
    $('#showInactive').click(() => filterRows('inactive'));

    // Обработчик модального окна цен
    $('#priceModal').on('show.bs.modal', function(e) {
        var btn = $(e.relatedTarget);
        var rawPrices = btn.attr('data-product-prices') || '[]';
        var rawThumbs = btn.attr('data-product-thumbs') || '[]';
        var title = btn.data('product-title') || 'Неизвестный товар';
        var url = btn.data('search-url') || '#';

        var prices = [];
        try {
            prices = JSON.parse(rawPrices);
            if (!Array.isArray(prices)) prices = [];
        } catch (err) {
            console.error('Ошибка парсинга цен:', err, rawPrices);
            prices = [];
        }

        var thumbs = [];
        try {
            thumbs = JSON.parse(rawThumbs);
            if (!Array.isArray(thumbs)) thumbs = [];
        } catch (err) {
            console.error('Ошибка парсинга миниатюр:', err, rawThumbs);
            thumbs = [];
        }

        $('#modalProductTitle').text(title);
        $('#modalPriceList').empty();

        if (prices.length > 0) {
            prices.forEach(function(price, idx) {
                var col = $('<div class="col-md-4 text-center mb-3"></div>');
                if (thumbs[idx]) {
                    col.append('<img src="' + thumbs[idx] + '" class="img-fluid rounded mb-1" style="max-height: 100px;" alt="Миниатюра">');
                }
                col.append('<div>' + price + '</div>');
                $('#modalPriceList').append(col);
            });
        } else {
            $('#modalPriceList').append('<p class="text-center">Цены отсутствуют</p>');
        }

        $('#modalSearchLink').attr('href', url);
    });

    // Обработчик модального окна редактирования
    $('#editPriceModal').on('show.bs.modal', function(e) {
        var btn = $(e.relatedTarget);
        $('#editProductId').val(btn.data('product-id') || '');
        $('#editProductTitle').text(btn.data('product-title') || 'Неизвестный товар');
        $('#editProductPrice').val(btn.data('product-price') || '');
        $('#marketMin').text(parseFloat(btn.data('market-min') || 0).toFixed(2));
        $('#marketMax').text(parseFloat(btn.data('market-max') || 0).toFixed(2));
        $('#marketAvg').text(parseFloat(btn.data('market-avg') || 0).toFixed(2));
        $('#idealPrice').text(parseFloat(btn.data('ideal-price') || 0).toFixed(2));
    });

    // Графики
    if (typeof Chart === 'undefined') {
        console.error('Chart.js не загружен');
    } else {
        var diffLabels = window.diffLabels || [];
        var diffData = window.diffData || [];
        var priceData = window.priceData || [];
        var avgData = window.avgData || [];
        var bins = window.bins || {};
        var binLabels = Object.keys(bins);
        var binData = Object.values(bins);
        var overpriced = window.overpriced || 0;
        var underpriced = window.underpriced || 0;
        var fairPrice = window.fairPrice || 0;

        if ($('#diffHistogram').length && binLabels.length > 0) {
            new Chart($('#diffHistogram'), {
                type: 'bar',
                data: {
                    labels: binLabels,
                    datasets: [{
                        label: 'Количество товаров',
                        data: binData,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: { y: { beginAtZero: true } },
                    plugins: { legend: { display: true } }
                }
            });
        }

        if ($('#priceStatusChart').length && (overpriced || underpriced || fairPrice)) {
            new Chart($('#priceStatusChart'), {
                type: 'pie',
                data: {
                    labels: ['Завышенные', 'Недооцененные', 'Рыночные'],
                    datasets: [{
                        data: [overpriced, underpriced, fairPrice],
                        backgroundColor: ['#dc3545', '#ffc107', '#17a2b8'],
                        borderWidth: 1
                    }]
                },
                options: {
                    plugins: { legend: { position: 'top' } }
                }
            });
        }

        if ($('#priceComparisonChart').length && diffLabels.length > 0) {
            new Chart($('#priceComparisonChart'), {
                type: 'line',
                data: {
                    labels: diffLabels,
                    datasets: [
                        { label: 'emg Price', data: priceData, borderColor: '#007bff', fill: false },
                        { label: 'Ovoko Avg', data: avgData, borderColor: '#28a745', fill: false }
                    ]
                },
                options: {
                    elements: { point: { radius: 3 } },
                    scales: { y: { beginAtZero: false } }
                }
            });
        }

        if ($('#diffChart').length && diffLabels.length > 0) {
            new Chart($('#diffChart'), {
                type: 'line',
                data: {
                    labels: diffLabels,
                    datasets: [{
                        label: 'Δ (€)',
                        data: diffData,
                        borderColor: '#dc3545',
                        borderWidth: 1,
                        fill: false
                    }]
                },
                options: {
                    scales: { y: { beginAtZero: false } }
                }
            });
        }
    }

    $('#refreshButton').click(() => location.reload());
});