<?php
require_once("helpers.php");
require_once("functions.php");
require_once("init.php");

/** @var mysqli $connect */
/** @var string $user_name */
/** @var int $is_auth */
/** @var int $user_id */

if (!$is_auth) {
    header("Location: /login.php");
    exit();
}

$categories = get_categories_list($connect);
$bets = get_bids_by_user_id($connect, $user_id);

$page_content = include_template("my-bets_template.php", [
    "bets" => $bets
]);

$layout_content = include_template("layout.php", [
    "content" => $page_content,
    "title" => "Мои ставки",
    "categories" => $categories,
    "user_name" => $user_name,
    "is_auth" => $is_auth
]);

print($layout_content);