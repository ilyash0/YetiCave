<?php
require_once("helpers.php");
require_once("functions.php");
$data = require_once("data.php");

$page_content = include_template("main.php", [
    "categories" => $data["categories"],
    "products" => $data["products"]
]);

$layout_content = include_template("layout.php", [
    "content" => $page_content,
    "title" => "YetiCave",
    "categories" => $data["categories"],
    "user_name" => $data["user_name"],
    "is_auth" => $data["is_auth"]
]);

print($layout_content);

