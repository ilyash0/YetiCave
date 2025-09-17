<?php
$is_auth = rand(0, 1);

$user_name = "<script>alert('XSS!')</script>";

$categories = ["Доски и лыжи", "Крепления", "Ботинки", "Одежда", "Инструменты", "Разное"];

$products = [
    [
        "name" => "2014 Rossignol District Snowboard",
        "category" => "Доски и лыжи",
        "price" => 10999,
        "image" => "img/lot-1.jpg",
        "expiration_date" => "18.09.2025"
    ],
    [
        "name" => "DC Ply Mens 2016/2017 Snowboard",
        "category" => "Доски и лыжи",
        "price" => 159999,
        "image" => "img/lot-2.jpg",
        "expiration_date" => "19.09.2025"
    ],
    [
        "name" => "Крепления Union Contact Pro 2015 года размер L/XL",
        "category" => "Крепления",
        "price" => 8000,
        "image" => "img/lot-3.jpg",
        "expiration_date" => "20.09.2025"
    ],
    [
        "name" => "Ботинки для сноуборда DC Mutiny Charocal",
        "category" => "Ботинки",
        "price" => 10999,
        "image" => "img/lot-4.jpg",
        "expiration_date" => "21.09.2025"
    ],
    [
        "name" => "Куртка для сноуборда DC Mutiny Charocal",
        "category" => "Одежда",
        "price" => 7500,
        "image" => "img/lot-5.jpg",
        "expiration_date" => "22.09.2025"
    ],
    [
        "name" => "Маска Oakley Canopy",
        "category" => "Разное",
        "price" => 5400,
        "image" => "img/lot-6.jpg",
        "expiration_date" => "23.09.2025"
    ]
];
