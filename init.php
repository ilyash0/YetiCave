<?php
session_start();
$is_auth = $_SESSION["is_auth"] ?? 0;
$user_id = $_SESSION["user_id"] ?? 0;
$user_name = $_SESSION["user_name"] ?? "";

const HOST = "localhost";
const USER = "kcbhnjus";
const PASSWORD = "RJ4aFd";
const DATABASE = "kcbhnjus_m1";

$connect = mysqli_connect(HOST, USER, PASSWORD, DATABASE);
if ($connect) {
    mysqli_set_charset($connect, "utf8");
}
