<?php
require_once("helpers.php");
require_once("functions.php");
require_once("init.php");


/** @var mysqli $connect */
/** @var string $user_name */
/** @var int $is_auth */

$errors = [];
$new_lot = [];
$categories = get_categories_array($connect);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_lot = [
        'lot-name' => trim($_POST['lot-name']),
        'category' => $_POST['category'] ?? null,
        'message' => trim($_POST['message']),
        'lot-rate' => $_POST['lot-rate'],
        'lot-step' => $_POST['lot-step'],
        'lot-img' => $_FILES['lot-img'],
        'lot-date' => $_POST['lot-date']
    ];

    $rules = [
        'lot-name' => is_valid_length($new_lot['lot-name'], 1, 255),
        'category' => isset($new_lot['category']) && isset($categories[$new_lot['category'] - 1]),
        'message' => is_valid_length($new_lot['message'], 1, 5000),
        'lot-rate' => is_filled($new_lot['lot-rate']) and is_valid_price($new_lot['lot-rate']),
        'lot-step' => is_filled($new_lot['lot-step']) and is_valid_price($new_lot['lot-step']),
        'lot-img' => isset($new_lot['lot-img']) && is_image($new_lot['lot-img']),
        'lot-date' => is_filled($new_lot['lot-date']) and is_valid_date($new_lot['lot-date'])
    ];

    foreach ($rules as $key => $value) {
        if (!$value) {
            $errors[] = $key;
        }
    }

    if (empty($errors)) {
        $lot_data = [
            'title' => $new_lot['lot-name'],
            'description' => $new_lot['message'],
            'initial_price' => $new_lot['lot-rate'],
            'bid_step' => $new_lot['lot-step'],
            'date_end' => $new_lot['lot-date'],
            'author_id' => 1,
            'uploaded_file' => $new_lot['lot-img'],
            'category_id' => $new_lot['category']
        ];

        $lot_id = create_lot($connect, $lot_data, 'uploads/');

        header("Location: /lot.php?id=" . $lot_id);
    }
}

$page_content = include_template("add_template.php", ["categories" => $categories, "errors" => $errors]);
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

