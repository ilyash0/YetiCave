<?php
require_once("helpers.php");
require_once("functions.php");
require_once("init.php");


/** @var mysqli $connect */
/** @var string $user_name */
/** @var int $is_auth */

$categories = get_categories_array($connect);
$lot_id = $_GET['id'] ?? null;

if ($lot_id === null || get_lot_by_id($connect, $lot_id) === null) {
    $title = "Ошибка 404";
    $page_content = include_template("404.php", ["categories" => $categories]);
}
else
{
    $lot = get_lot_by_id($connect, $lot_id);
    $title = $lot['title'];
    $page_content = include_template("lot.php", ["categories" => $categories, 'lot' => $lot]);
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

