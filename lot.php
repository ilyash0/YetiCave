<?php
require_once("helpers.php");
require_once("functions.php");
require_once("init.php");


/** @var mysqli $connect */
/** @var string $user_name */
/** @var int $is_auth */

$categories = get_categories_array($connect);
$lot_id = $_GET['id'] ?? null;
$lot = get_lot_or_null_by_id($connect, $lot_id);

if ($lot_id === null || $lot === null) {
    http_response_code(404);
    $title = "Ошибка 404";
    $page_content = include_template("404.php", ["categories" => $categories]);
}
else
{
    $title = $lot['title'];
    $page_content = include_template("lot_template.php", ["categories" => $categories, 'lot' => $lot, "is_auth" => $is_auth]);
}

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

