<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../dummydata/OrderInfo.php';


if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $id = $_GET['id'] ?? '';
    if ($id === '1') {
        require_once __DIR__ . '/../views/customer/order_info.php';
    } else if ($id === 2) {
        require_once __DIR__ . '/../views/customer/order_info.php';

    } else if ($id === 3) {
        require_once __DIR__ . '/../views/customer/order_info.php';

    } else if ($id === 4) {
        require_once __DIR__ . '/../views/customer/order_info.php';

    }
}