<?php
require_once("helpers.php");
require_once("functions.php");
require_once("init.php");

/** @var mysqli $connect */
/** @var int $is_auth */
/** @var int $user_id */

if (!$is_auth) {
    header("Location: /login.php");
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    exit();
}

$lot_id = (int)($_POST['lot_id'] ?? 0);
$bid_amount = trim($_POST['cost'] ?? '');

$lot = get_lot_by_id($connect, $lot_id);
if (!$lot) {
    http_response_code(404); // Not Found
    exit();
}

$now = date('Y-m-d');
if ($lot['date_end'] < $now) {
    $_SESSION['bid_errors'] = ['error' => 'Торги по этому лоту уже завершены.'];
    header("Location: /lot.php?id=$lot_id");
    exit();
}

if ((int)$lot['author_id'] === $user_id) {
    $_SESSION['bid_errors'] = ['error' => 'Нельзя ставить на свой лот.'];
    header("Location: /lot.php?id=$lot_id");
    exit();
}

$last_bid = get_last_bid_for_lot($connect, $lot_id);
if ($last_bid && (int)$last_bid['user_id'] === $user_id) {
    $_SESSION['bid_errors'] = ['error' => 'Вы уже сделали последнюю ставку. Дождитесь чужой ставки.'];
    header("Location: /lot.php?id=$lot_id");
    exit();
}

if (empty($bid_amount)) {
    $errors['error'] = 'Введите ставку';
} elseif (!is_numeric($bid_amount) || (int)$bid_amount != $bid_amount || (int)$bid_amount <= 0) {
    $errors['error'] = 'Ставка должна быть целым положительным числом';
} else {
    $bid_amount = (int)$bid_amount;
    $current_price = (int)$lot['current_price'];
    $bid_step = (int)$lot['bid_step'];
    $min_bid = $current_price + $bid_step;

    if ($bid_amount < $min_bid) {
        $errors['error'] = "Ставка должна быть не меньше $min_bid";
    }
    elseif (($bid_amount - $current_price) % $bid_step !== 0) {
        $errors['error'] = "Ставка должна быть кратна шагу в $bid_step ₽";
    }
}

if (empty($errors)) {
    if (add_bid($connect, $lot_id, $user_id, $bid_amount)) {
        header("Location: /lot.php?id=$lot_id");
        exit();
    } else {
        $errors['common'] = 'Не удалось сделать ставку. Попробуйте позже.';
    }
}

$_SESSION['bid_errors'] = $errors;

header("Location: /lot.php?id=$lot_id");
exit();