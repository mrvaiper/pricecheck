<?php
// public/index.php
define('BASE_PATH', '/www/wwwroot/dem.ehost.work/');
require_once BASE_PATH . 'includes/analytics.php';
include BASE_PATH . 'templates/header.php';
include BASE_PATH . 'templates/dashboard.php';
include BASE_PATH . 'templates/table.php';
include BASE_PATH . 'templates/charts.php';
include BASE_PATH . 'templates/modals.php';
include BASE_PATH . 'templates/footer.php';