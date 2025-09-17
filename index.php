<?php
require_once("helpers.php");
require_once("functions.php");
require_once("data.php");


/** @var array $categories */
/** @var array $products */
/** @var string $user_name */
/** @var int $is_auth */

date_default_timezone_set("Asia/Yekaterinburg");

$page_content = include_template("main.php", ["categories" => $categories, 'products' => $products]);

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

