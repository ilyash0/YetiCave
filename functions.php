<?php
date_default_timezone_set("Asia/Yekaterinburg");
const HOURS_IN_DAY = 24;
const MINUTES_IN_HOUR = 60;
const SECONDS_IN_HOUR = 3600;


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
 * @return array массив [часы, минуты] в числовом формате
 */
function get_dt_range(string $date_str): array
{
    $day_start = strtotime($date_str);

    if ($day_start === false) {
        return [0, 0];
    }

    $now = time();
    $target_date = $day_start + HOURS_IN_DAY * SECONDS_IN_HOUR;
    $difference = $target_date - $now;

    if ($difference <= 0) {
        return [0, 0];
    }

    $hours = floor($difference / SECONDS_IN_HOUR);
    $minutes = floor(($difference % SECONDS_IN_HOUR) / MINUTES_IN_HOUR);
    return [$hours, $minutes];
}
