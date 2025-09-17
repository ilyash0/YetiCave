<?php

/**
 * @param int $amount
 * @return string
 */
function format_price(int $amount): string
{
    $formatted = number_format($amount, 0, ",", " ");

    return $formatted . " ₽";
}

/**
 * @param string $date_str
 * @return array массив с двумя строками 'HH' и 'MM'
 */
function get_dt_range(string $date_str): array
{
    $day_start = strtotime($date_str);

    if ($day_start === false) {
        return [0, 0];
    }

    $now = time();
    $target_date = $day_start + 24 * 3600;
    $difference = $target_date - $now;

    if ($difference <= 0) {
        return [0, 0];
    }

    $hours = floor($difference / 3600);
    $minutes = floor(($difference % 3600) / 60);
    return [$hours, $minutes];
}
