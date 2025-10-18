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
        'email' => $_POST['email'],
        'password' => $_POST['password'],
        'name' => $_POST['name'],
        'message' => $_POST['message']
    ];

    $rules = [
        'email' => is_valid_length($new_user['email'], 1, 255),
        'password' => is_valid_length($new_user['password'], 1, 255),
        'name' => is_valid_length($new_user['name'], 1, 150),
        'message' => is_valid_length($new_user['message'], 1, 255)
    ];

    foreach ($rules as $key => $value) {
        if (!$value) {
            $errors[] = $key;
        }
    }

    if (empty($errors)) {
        $sql = "INSERT INTO users (name, email, password_hash, contact_information)
                VALUES (?, ?, ?, ?)";


        $stmt = db_get_prepare_stmt($connect, $sql, [
            $new_user['name'],
            $new_user['email'],
            password_hash($new_user['password'], PASSWORD_DEFAULT),
            $new_user['message']
        ]);

        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            header("Location: /");
            exit();
        } else {
            http_response_code(500);
        }
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

