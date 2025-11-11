<?php
require_once("helpers.php");
require_once("functions.php");
require_once("init.php");


/** @var mysqli $connect */
/** @var string $user_name */
/** @var int $is_auth */
/** @const int LOTS_PER_PAGE */

$categories = get_categories_list($connect);
$search_category = trim($_GET["category"] ?? "boards");

$category = get_category_by_symbolic_code($connect, $search_category);
$category_id = $category['id'];

if ($search_category && !$category_id) {
    http_response_code(404);
    print(get_error_page(404, $categories, $user_name, $is_auth));
    exit();
}

$all_results = get_lots_by_category_id($connect, $category_id);
$pagination_data = paginate_data($all_results, (int)($_GET["page"] ?? 1), LOTS_PER_PAGE);

$page_content = include_template("search_template.php", [
    "title" => "Все лоты в категории «" . $category['name'] . "»",
    "search_query" => $search_category,
    "lots" => $pagination_data['items'],
    "total_pages" => $pagination_data['total_pages'],
    "current_page" => $pagination_data['current_page']
]);

$layout_content = include_template("layout.php", [
    "content" => $page_content,
    "title" => "Все лоты в категории «" . $category['name'] . "»",
    "categories" => $categories,
    "user_name" => $user_name,
    "is_auth" => $is_auth,
    "current_category_id" => $category_id,
]);

print($layout_content);