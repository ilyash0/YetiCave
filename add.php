<?php
require_once("helpers.php");
require_once("functions.php");
require_once("init.php");
require_once("strings.php");

/** @var mysqli $connect */
/** @var array $strings */
/** @var string $user_name */
/** @var int $is_auth */
/** @var int $user_id */

$errors = [];
$categories = get_categories_list($connect);

if (!$is_auth) {
    http_response_code(403);
    print(get_error_page(403, $categories, $user_name, $is_auth));
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_lot = [
        "title" => trim($_POST["title"]),
        "category_id" => $_POST["category_id"] ?? -1,
        "description" => trim($_POST["description"]),
        "initial_price" => $_POST["initial_price"],
        "bid_step" => $_POST["bid_step"],
        "uploaded_file" => $_FILES["uploaded_file"],
        "date_end" => $_POST["date_end"],
        "author_id" => $user_id,
    ];

    $errors = validate_lot_creation($new_lot, $strings, $categories);

    if (empty($errors)) {
        $lot_id = create_lot($connect, $new_lot);

        if ($lot_id) {
            header("Location: /lot.php?id=" . $lot_id);
            exit();
        }
        $errors['general'] = $strings['lot_creation_failed'];
    }
}

$page_content = include_template("add_template.php", ["errors" => $errors, "categories" => $categories]);
$layout_content = include_template("layout.php",
    [
        "content" => $page_content,
        "title" => "Добавить лот",
        "categories" => $categories,
        "user_name" => $user_name,
        "is_auth" => $is_auth
    ]
);

print($layout_content);

