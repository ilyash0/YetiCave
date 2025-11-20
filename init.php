<?php
date_default_timezone_set("Asia/Yekaterinburg");
session_start();
$is_auth = $_SESSION["is_auth"] ?? 0;
$user_id = $_SESSION["user_id"] ?? 0;
$user_name = $_SESSION["user_name"] ?? "";

const HOST = "localhost";
const USER = "kcbhnjus";
const PASSWORD = "RJ4aFd";
const DATABASE = "kcbhnjus_m1";
const LOTS_PER_PAGE = 9;
const RECAPTCHA_SITEKEY = "6Ldecg0sAAAAAGmlTtgpS_O77V7fAEIl3yCXF5in";
const RECAPTCHA_SECRET = "6Ldecg0sAAAAAN-wcvWQzTp1uNfdiRn_crCkM5U2";
const RECAPTCHA_MIN_SCORE = 0.5;

try {
    $connect = mysqli_connect(HOST, USER, PASSWORD, DATABASE);
    if (!$connect) {
        throw new RuntimeException();
    }
    mysqli_set_charset($connect, "utf8");
} catch (Throwable $e) {
    http_response_code(500);
    echo "Внутренняя ошибка сервера";
    exit;
}
