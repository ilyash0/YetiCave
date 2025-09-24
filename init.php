<?php
$is_auth = rand(0, 1);

$user_name = "Илья";

const HOST = "localhost";
const USER = "kcbhnjus";
const PASSWORD = "RJ4aFd";
const DATABASE = "kcbhnjus_m1";

$connect = mysqli_connect(HOST, USER, PASSWORD, DATABASE);
if ($connect) {
    mysqli_set_charset($connect, "utf8");
}
