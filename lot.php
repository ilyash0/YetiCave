<?php
require_once("helpers.php");
require_once("functions.php");
require_once("init.php");

/** @var mysqli $connect */
/** @var string $user_name */
/** @var int $user_id */
/** @var int $is_auth */

$categories = get_categories_list($connect);
$lot_id_param = $_GET["id"] ?? null;

if (!is_numeric($lot_id_param)) {
    http_response_code(404);
    print(get_error_page(404, $categories, $user_name, $is_auth));
    exit();
}

$lot_id = (int)$lot_id_param;

set_winner_for_lot($connect, $lot_id);
$lot = get_lot_by_id($connect, $lot_id);
$bids = get_bids_by_lot_id($connect, $lot_id);
$last_bid = get_last_bid_for_lot($connect, $lot_id);
$now = date("Y-m-d");
$should_hide_bid_form = !$is_auth
    || $lot["date_end"] < $now
    || (int)$lot["author_id"] === $user_id
    || $last_bid && (int)$last_bid["user_id"] === $user_id;

if ($lot === null) {
    http_response_code(404);
    print(get_error_page(404, $categories, $user_name, $is_auth));
    exit();
}

$title = $lot["title"];
$page_content = include_template("lot_template.php",
    [
        "lot" => $lot,
        "bids" => $bids,
        "is_auth" => $is_auth,
        "should_hide_bid_form" => $should_hide_bid_form
    ]
);

$layout_content = include_template("layout.php",
    [
        "content" => $page_content,
        "title" => $title,
        "categories" => $categories,
        "user_name" => $user_name,
        "is_auth" => $is_auth
    ]
);

print($layout_content);