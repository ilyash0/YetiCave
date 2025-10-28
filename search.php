<?php
require_once("helpers.php");
require_once("functions.php");
require_once("init.php");

/** @var mysqli $connect */
/** @var string $user_name */
/** @var int $is_auth */

const LOTS_PER_PAGE = 9;

$categories = get_categories_array($connect);
$search_query = trim($_GET["search"] ?? "");

if (empty($search_query))
{
    header("Location: /");
    exit();
}

$all_results = search_lots($connect, $search_query);

$total_count = count($all_results);
$total_pages = ceil($total_count / LOTS_PER_PAGE);
$current_page = max(1, (int)($_GET["page"] ?? 1));
$current_page = min($total_pages, $current_page);

$offset = ($current_page - 1) * LOTS_PER_PAGE;
$search_results = array_slice($all_results, $offset, LOTS_PER_PAGE);

$page_content = include_template("search_template.php", [
    "categories" => $categories,
    "search_query" => $search_query,
    "search_results" => $search_results,
    "total_pages" => $total_pages,
    "current_page" => $current_page
]);

$layout_content = include_template("layout.php", [
    "content" => $page_content,
    "title" => "Результаты поиска",
    "categories" => $categories,
    "user_name" => $user_name,
    "is_auth" => $is_auth
]);

print($layout_content);