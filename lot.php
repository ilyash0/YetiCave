<?php
require_once("helpers.php");
require_once("functions.php");
require_once("init.php");


/** @var mysqli $connect */
/** @var string $user_name */
/** @var int $is_auth */

$categories = get_categories_array($connect);
$lot_id = $_GET['id'] ?? null;
$lot = get_lot_by_id($connect, $lot_id);
$bids = get_bids_for_lot($connect, $lot_id);
$bids_count = count($bids);

if ($lot_id === null || $lot === null) {
    http_response_code(404);
    print(get_error_page(404, $categories, $user_name, $is_auth));
    exit();
}

$title = $lot['title'];
$page_content = include_template("lot_template.php",
    [
        'lot' => $lot,
        "bids" => $bids,
        "bids_count" => $bids_count,
        "is_auth" => $is_auth
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

