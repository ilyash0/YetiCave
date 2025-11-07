<?php
require_once("helpers.php");
require_once("functions.php");
require_once("init.php");


/** @var mysqli $connect */
/** @var string $user_name */
/** @var int $is_auth */

if (!$is_auth) {
    header("Location: /login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$categories = get_categories_list($connect);

// Получаем ставки пользователя с информацией о лоте
$bets = get_bets_by_user_id($connect, $user_id);

$page_content = include_template("my-bets_template.php", [
    "bets" => $bets,
    "user_name" => $user_name,
    "is_auth" => $is_auth
]);

$layout_content = include_template("layout.php", [
    "content" => $page_content,
    "title" => "Мои ставки",
    "categories" => $categories,
    "user_name" => $user_name,
    "is_auth" => $is_auth
]);

print($layout_content);