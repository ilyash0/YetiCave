<?php
require_once("helpers.php");
require_once("functions.php");
require_once("init.php");


/** @var mysqli $connect */
/** @var string $user_name */
/** @var int $is_auth */

date_default_timezone_set("Asia/Yekaterinburg");

$categories = get_categories_array($connect);
$lots = get_lots($connect);

$page_content = include_template("main.php", ["categories" => $categories, 'lots' => $lots]);

$layout_content = include_template("layout.php",
    [
        "content" => $page_content,
        "title" => "YetiCave",
        "categories" => $categories,
        "user_name" => $user_name,
        "is_auth" => $is_auth
    ]
);

print($layout_content);

