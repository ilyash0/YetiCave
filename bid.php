<?php
require_once("helpers.php");
require_once("functions.php");
require_once("init.php");

/** @var mysqli $connect */
/** @var int $is_auth */

if (!$is_auth) {
    header("Location: /login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit();
}

$lot_id = (int)($_POST["lot_id"] ?? 0);
$bid_amount_input = trim($_POST["cost"] ?? '');
$bid_amount = (int)$bid_amount_input;
$user_id = (int)$_SESSION["user_id"];
$lot = get_lot_by_id($connect, $lot_id);

if (!$lot) {
    http_response_code(404);
    exit();
}

$now = date("Y-m-d");
$last_bid = get_last_bid_for_lot($connect, $lot_id);
$errors = [];

if ($lot["date_end"] < $now) {
    $errors["error"] = "Торги по этому лоту уже завершены.";
} elseif ((int)$lot["author_id"] === $user_id) {
    $errors["error"] = "Нельзя ставить на свой лот.";
} elseif ($last_bid && (int)$last_bid["user_id"] === $user_id) {
    $errors["error"] = "Вы уже сделали последнюю ставку.";
} elseif (empty($bid_amount_input)) {
    $errors["error"] = "Введите ставку";
} elseif (!is_numeric($bid_amount_input) || (int)$bid_amount_input != $bid_amount_input || (int)$bid_amount_input <= 0) {
    $errors["error"] = "Ставка должна быть целым положительным числом";
} else {

    $current_price = (int)$lot["current_price"];
    $bid_step = (int)$lot["bid_step"];
    $min_bid = $current_price + $bid_step;

    if ($bid_amount < $min_bid) {
        $errors["error"] = "Ставка должна быть не меньше $min_bid";
    } elseif (($bid_amount - $current_price) % $bid_step !== 0) {
        $errors["error"] = "Ставка должна быть кратна шагу в $bid_step ₽";
    }
}

if (empty($errors)) {
    $result = add_bid($connect, $lot_id, $user_id, $bid_amount);
    if ($result) {
        header("Location: /lot.php?id=$lot_id");
        exit();
    }
    $errors["common"] = "Не удалось сделать ставку.";
}

$_SESSION["bid_errors"] = $errors;

header("Location: /lot.php?id=$lot_id");
exit();