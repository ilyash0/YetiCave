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
        'category' => isset($new_lot['category']) && isset($categories[$new_lot['category']-1]),
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
        $img_path = 'uploads/';
        $img_name = $new_lot['lot-img']['name'];
        $img_full_path = $img_path . uniqid() . '_' . basename($img_name);

        if (move_uploaded_file($new_lot['lot-img']['tmp_name'], $img_full_path)) {
            $sql = "INSERT INTO lots (title, description, image_url, initial_price, bid_step, date_end, author_id, category_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $user_id = 1;

            $stmt = db_get_prepare_stmt($connect, $sql, [
                $new_lot['lot-name'],
                $new_lot['message'],
                $img_full_path,
                $new_lot['lot-rate'],
                $new_lot['lot-step'],
                $new_lot['lot-date'],
                $user_id,
                $new_lot['category']
            ]);

            if ($stmt) {
                $result = mysqli_stmt_execute($stmt);

                if ($result) {
                    header("Location: /lot.php?id=" . mysqli_insert_id($connect));
                    exit();
                } else {
                    http_response_code(500);
                }
            } else {
                http_response_code(500);
            }
        } else {
            http_response_code(500);
        }
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

