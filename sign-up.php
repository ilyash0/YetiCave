<?php
require_once("helpers.php");
require_once("functions.php");
require_once("init.php");


/** @var mysqli $connect */
/** @var string $user_name */
/** @var int $is_auth */

$errors = [];
$new_user = [];
$categories = get_categories_array($connect);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_user = [
        'email' => trim($_POST['email']),
        'password' => $_POST['password'],
        'name' => trim($_POST['name']),
        'message' => trim($_POST['message'])
    ];

    $rules = [
        'email' => is_valid_length($new_user['email'], 1, 255) and filter_var($_POST['email'], FILTER_VALIDATE_EMAIL),
        'password' => is_valid_length($new_user['password'], 8, 255),
        'name' => is_valid_length($new_user['name'], 1, 150),
        'message' => is_valid_length($new_user['message'], 1, 255)
    ];

    foreach ($rules as $key => $value) {
        if (!$value) {
            $errors[] = $key;
        }
    }

    if (empty($errors)) {
        register_user($connect, $new_user["name"], $new_user["email"], $new_user["password"], $new_user["message"]);
        header("Location: /login.php");
    }
}

$page_content = include_template("sign-up_template.php", ["categories" => $categories, "errors" => $errors]);
$layout_content = include_template("layout.php",
    [
        "content" => $page_content,
        "title" => "Регистрация",
        "categories" => $categories,
        "user_name" => $user_name,
        "is_auth" => $is_auth
    ]
);

print($layout_content);

