<?php
require_once("helpers.php");
require_once("functions.php");
require_once("init.php");


/** @var mysqli $connect */
/** @var string $user_name */
/** @var int $is_auth */
/** @const int LOTS_PER_PAGE */

$categories = get_categories_list($connect);
$search_query = trim($_GET["search"] ?? "");

if (empty($search_query)) {
    header("Location: /");
    exit();
}

$all_results = search_lots_by_query($connect, $search_query);
$pagination_data = paginate_data($all_results, (int)($_GET["page"] ?? 1), LOTS_PER_PAGE);

$page_content = include_template("search_template.php", [
    "title" => "Результаты поиска по запросу «" . $search_query . "»",
    "search_query" => $search_query,
    "lots" => $pagination_data['items'],
    "total_pages" => $pagination_data['total_pages'],
    "current_page" => $pagination_data['current_page']
]);

$layout_content = include_template("layout.php", [
    "content" => $page_content,
    "title" => "Результаты поиска по запросу «" . $search_query . "»",
    "categories" => $categories,
    "user_name" => $user_name,
    "is_auth" => $is_auth
]);

print($layout_content);