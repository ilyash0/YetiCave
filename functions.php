<?php
/** @var false|mysqli $connect */
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

/**
 * @param mysqli $connect
 * @return array Массив ассоциативных массивов с данными категорий. Каждый элемент содержит:
 * * id (int) - интенсификатор категории
 * * name (string) - читаемое название
 * * symbolic_code (string) - символьный код для URL
 */
function get_categories_array(mysqli $connect): array
{
    $sql = "SELECT id, name, symbolic_code FROM categories";
    $result = mysqli_query($connect, $sql);

    $categories = [];
    if ($result) {
        $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    return $categories;
}

/**
 * @param mysqli $connect
 * @return array Массив ассоциативных массивов с данными лотов. Каждый элемент содержит:
 * * title (string) - название лота
 * * initial_price (int) - начальная цена
 * * image_url (string) - путь к изображению
 * * category_name (string) - название категории (из таблицы categories)
 * * date_end (string) - дата окончания торгов в формате YYYY-MM-DD
 */
function get_lots(mysqli $connect): array
{
    $sql = "SELECT l.id, l.title, l.initial_price, l.image_url, 
                   c.name AS category_name, l.date_end
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            WHERE l.date_end >= CURDATE()
            ORDER BY l.created_at DESC";

    $result = mysqli_query($connect, $sql);

    $lots = [];
    if ($result) {
        $lots = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    return $lots;
}

/**
 * @param mysqli $connect Подключение к базе данных
 * @param int $id ID лота
 * @return array|null Ассоциативный массив с данными лота или null, если не найдено
 */
function get_lot_or_null_by_id(mysqli $connect, ?int $id): ?array
{
    $sql = "
        SELECT  l.title, l.initial_price, l.image_url, 
                c.name AS category_name, l.date_end, l.bid_step, l.description
        FROM lots AS l
        JOIN categories c ON l.category_id = c.id
        WHERE l.id = ?";

    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_assoc($result) ?: null;
}

function is_filled(string $text): bool
{
    return !empty($text);
}

function is_valid_length(string $text, int $min, int $max): bool
{
    $len = strlen($text);
    return $len >= $min and $len <= $max;
}

function is_valid_price(string $value): bool
{
    return is_numeric($value) && $value > 0;
}

function is_valid_date(string $date) : bool {
    $format_to_check = 'Y-m-d';
    $dateTimeObj = date_create_from_format($format_to_check, $date);

    if ($dateTimeObj === false) {
        return false;
    }

    $now = new DateTime();
    $today_start = new DateTime($now->format($format_to_check));

    return $dateTimeObj >= $today_start;
}

function is_image($file): bool
{
    if (!is_array($file) || !isset($file['tmp_name']) || empty($file['tmp_name']))
    {
        return false;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file_name = $file['tmp_name'];
    $file_type = finfo_file($finfo, $file_name);
    finfo_close($finfo);

    return $file_type == 'image/jpeg' or $file_type == 'image/png' or $file_type == 'image/webp';
}
