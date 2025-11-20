<?php
require_once("helpers.php");
require_once("functions.php");
require_once("init.php");

/** @var mysqli $connect */
/** @var string $user_name */
/** @var int $is_auth */


$errors = [];
$new_user = [];
$categories = get_categories_list($connect);

if ($is_auth) {
    http_response_code(403);
    print(get_error_page(403, $categories, $user_name, $is_auth));
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_user = [
        "email" => trim($_POST["email"]),
        "password" => $_POST["password"],
        "name" => trim($_POST["name"]),
        "message" => trim($_POST["message"]),
        "recaptcha_token" => $_POST['g-recaptcha-response'] ?? ''
    ];

    $errors = validate_registration($connect, $new_user);

    if (empty($errors)) {
        register_user($connect, $new_user["name"], $new_user["email"], $new_user["password"], $new_user["message"]);
        header("Location: /login.php");
        exit();
    }
}

$page_content = include_template("sign-up_template.php", ["errors" => $errors]);
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

