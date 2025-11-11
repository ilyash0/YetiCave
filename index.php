<?php
require_once("helpers.php");
require_once("functions.php");
require_once("init.php");

/** @var mysqli $connect */
/** @var string $user_name */
/** @var int $is_auth */
/** @const int LOTS_PER_PAGE */

$categories = get_categories_list($connect);
$lots = get_active_lots_list($connect);
$pagination_data = paginate_data($lots, (int)($_GET["page"] ?? 1), LOTS_PER_PAGE);

$page_content = include_template("index_template.php", [
    "categories" => $categories,
    "lots" => $pagination_data["items"],
    "total_pages" => $pagination_data["total_pages"],
    "current_page" => $pagination_data["current_page"]
]);

$layout_content = include_template("layout.php",
    [
        "content" => $page_content,
        "title" => "YetiCave",
        "categories" => $categories,
        "user_name" => $user_name,
        "is_auth" => $is_auth,
    ]
);

print($layout_content);
