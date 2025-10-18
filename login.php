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
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email)) {
        $errors[] = 'email';
    }

    if (empty($password)) {
        $errors[] = 'password';
    }

    if (empty($errors)) {
        $sql = "SELECT id, email, name, password_hash FROM users WHERE email = ?";
        $stmt = db_get_prepare_stmt($connect, $sql, [$email]);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password_hash'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['is_auth'] = 1;

            header("Location: /");
            exit();
        } else {
            $errors[] = 'auth';
        }
    }
}

$page_content = include_template("login_template.php", ["categories" => $categories, "errors" => $errors]);
$layout_content = include_template("layout.php",
    [
        "content" => $page_content,
        "title" => "Вход",
        "categories" => $categories,
        "user_name" => $user_name,
        "is_auth" => $is_auth
    ]
);

print($layout_content);

