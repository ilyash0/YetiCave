<?php
require_once("helpers.php");
require_once("functions.php");
require_once("strings.php");
require_once("init.php");

/** @var mysqli $connect */
/** @var array $strings */
/** @var string $user_name */
/** @var int $user_id */
/** @var int $is_auth */

$categories = get_categories_list($connect);
$errors = [];
$bid_amount = '';
$lot_id = (int)($_GET['id'] ?? 0);

$lot = get_lot_by_id($connect, $lot_id);
if ($lot === null) {
    http_response_code(404);
    print(get_error_page(404, $categories, $user_name, $is_auth));
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$is_auth) {
    header("Location: /login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $is_auth) {
    $bid_amount = trim($_POST["cost"] ?? '');
    $errors = validate_bid([
        'cost' => $bid_amount,
        'lot_id' => $lot_id
    ], $lot, $connect, $user_id, $strings);

    if (empty($errors)) {
        $bid_amount_int = (int)$bid_amount;
        $result = add_bid($connect, $lot_id, $user_id, $bid_amount_int);
        if ($result) {
            header("Location: /lot.php?id=$lot_id");
            exit();
        }
        $errors['common'] = $strings['bid_failed'];
    }
}

$bids = get_bids_by_lot_id($connect, $lot_id);
$last_bid = get_last_bid_for_lot($connect, $lot_id);
$now = date("Y-m-d");
$should_hide_bid_form = !$is_auth
    || $lot["date_end"] < $now
    || (int)$lot["author_id"] === $user_id
    || ($last_bid && (int)$last_bid["user_id"] === $user_id);

$page_content = include_template("lot_template.php", [
    "lot" => $lot,
    "bids" => $bids,
    "is_auth" => $is_auth,
    "should_hide_bid_form" => $should_hide_bid_form,
    "bid_errors" => $errors,
    "bid_amount" => $bid_amount
]);

$layout_content = include_template("layout.php", [
    "content" => $page_content,
    "title" => htmlspecialchars($lot['title']),
    "categories" => $categories,
    "user_name" => $user_name,
    "is_auth" => $is_auth
]);

print($layout_content);