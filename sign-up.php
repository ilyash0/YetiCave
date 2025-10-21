<?php
require_once("helpers.php");
require_once("functions.php");
require_once("init.php");


/** @var mysqli $connect */
/** @var string $user_name */
/** @var int $is_auth */

if ($is_auth) {
    header("Location: /");
    exit();
}

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
        'email' => is_valid_length($new_user['email'], 1, 255) && filter_var($new_user['email'], FILTER_VALIDATE_EMAIL),
        'password' => is_valid_length($new_user['password'], 8, 255),
        'name' => is_valid_length($new_user['name'], 1, 150),
        'message' => is_valid_length($new_user['message'], 1, 255)
    ];

    foreach ($rules as $key => $value) {
        if (!$value) {
            $errors[] = $key;
        }
    }

    $sql_check = "SELECT id FROM users WHERE email = ?";
    $stmt_check = db_get_prepare_stmt($connect, $sql_check, [$new_user['email']]);
    mysqli_stmt_execute($stmt_check);
    if (mysqli_stmt_get_result($stmt_check)->num_rows > 0) {
        $errors[] = 'email';
    }

    if (empty($errors)) {
        register_user($connect, $new_user["name"], $new_user["email"], $new_user["password"], $new_user["message"]);
        header("Location: /login.php");
        exit();
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

