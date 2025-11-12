<?php
require_once("helpers.php");
require_once("functions.php");
require_once("init.php");

/** @var mysqli $connect */
/** @var string $user_name */
/** @var int $is_auth */
/** @var int $user_id */

$errors = [];
$new_lot = [];
$categories = get_categories_list($connect);

if (!$is_auth) {
    http_response_code(403);
    print(get_error_page(403, $categories, $user_name, $is_auth));
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_lot = [
        "title" => trim($_POST["title"]),
        "category_id" => $_POST["category_id"] ?? null,
        "description" => trim($_POST["description"]),
        "initial_price" => $_POST["initial_price"],
        "bid_step" => $_POST["bid_step"],
        "uploaded_file" => $_FILES["uploaded_file"],
        "date_end" => $_POST["date_end"],
        "author_id" => $user_id,
    ];

    $rules = [
        "title" => is_valid_length($new_lot["title"], 1, 255),
        "category_id" => isset($new_lot["category_id"]) && isset($categories[$new_lot["category_id"] - 1]),
        "description" => is_valid_length($new_lot["description"], 1, 5000),
        "initial_price" => is_filled($new_lot["initial_price"]) and is_valid_price($new_lot["initial_price"]),
        "bid_step" => is_filled($new_lot["bid_step"]) and is_valid_price($new_lot["bid_step"]),
        "uploaded_file" => isset($new_lot["uploaded_file"]) && is_image($new_lot["uploaded_file"]),
        "date_end" => is_filled($new_lot["date_end"]) and is_valid_date($new_lot["date_end"])
    ];

    foreach ($rules as $key => $value) {
        if (!$value) {
            $errors[] = $key;
        }
    }

    if (empty($errors)) {
        $lot_id = create_lot($connect, $new_lot, "uploads/");

        header("Location: /lot.php?id=" . $lot_id);
        exit();
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

