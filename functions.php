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
 * @param int|null $id ID лота
 * @return array|null Ассоциативный массив с данными лота или null, если не найдено
 */
function get_lot_by_id(mysqli $connect, ?int $id): ?array
{
    $sql = "
        SELECT  l.id, l.title, l.initial_price, l.image_url, 
                c.name AS category_name, l.date_end, l.bid_step, l.description,
                COALESCE(MAX(b.amount), l.initial_price) AS current_price
        FROM lots AS l
        JOIN categories c ON l.category_id = c.id
        LEFT JOIN bids b ON l.id = b.lot_id
        WHERE l.id = ?
        GROUP BY l.id";

    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_assoc($result);
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

function is_valid_date(string $date): bool
{
    $format = 'Y-m-d';
    $dateTimeObj = date_create_from_format($format, $date);

    if (!$dateTimeObj || $dateTimeObj->format($format) !== $date) {
        return false;
    }

    $date_obj = clone $dateTimeObj;
    $date_obj->setTime(0, 0);

    $today = new DateTime();
    $today->setTime(0, 0);

    return $date_obj > $today;
}

function is_image($file): bool
{
    if (!is_array($file) || empty($file['tmp_name'])) {
        return false;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file_name = $file['tmp_name'];
    $file_type = finfo_file($finfo, $file_name);
    finfo_close($finfo);

    return $file_type == 'image/jpeg' or $file_type == 'image/png' or $file_type == 'image/webp';
}

/**
 * Создаёт новый лот: сохраняет изображение и записывает данные в БД.
 *
 * @param mysqli $connect Подключение к БД
 * @param array $lot_data Данные лота: title, description, initial_price, bid_step, date_end, author_id, category_id, lot-img
 * @param string $upload_dir Каталог для загрузки изображений (например, 'uploads/')
 * @return int|null ID созданного лота или null в случае ошибки
 */
function create_lot(mysqli $connect, array $lot_data, string $upload_dir = 'uploads/'): ?int
{
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file_extension = pathinfo($lot_data['uploaded_file']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('lot_', true) . '.' . strtolower($file_extension);
    $filepath = $upload_dir . $filename;

    if (!move_uploaded_file($lot_data['uploaded_file']['tmp_name'], $filepath)) {
        return null;
    }

    $sql = "INSERT INTO lots (
                title, 
                description, 
                image_url, 
                initial_price, 
                bid_step, 
                date_end, 
                author_id, 
                category_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = db_get_prepare_stmt($connect, $sql, [
        $lot_data['title'],
        $lot_data['description'],
        $filepath,
        $lot_data['initial_price'],
        $lot_data['bid_step'],
        $lot_data['date_end'],
        $lot_data['author_id'],
        $lot_data['category_id']
    ]);

    if (mysqli_stmt_execute($stmt)) {
        return mysqli_insert_id($connect);
    }

    if (file_exists($filepath)) {
        unlink($filepath);
    }

    return null;
}

/**
 * Регистрирует нового пользователя в базе данных.
 *
 * @param mysqli $connect Подключение к БД
 * @param string $name Имя пользователя
 * @param string $email Email
 * @param string $password Пароль (в открытом виде)
 * @param string $contact_information Контактная информация
 * @return bool true при успехе, false при ошибке
 */
function register_user(mysqli $connect, string $name, string $email, string $password, string $contact_information): bool
{
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (name, email, password_hash, contact_information)
            VALUES (?, ?, ?, ?)";

    $stmt = db_get_prepare_stmt($connect, $sql, [$name, $email, $password_hash, $contact_information]);

    return mysqli_stmt_execute($stmt);
}

/**
 * Аутентифицирует пользователя по email и паролю.
 *
 * @param mysqli $connect Подключение к БД
 * @param string $email Email пользователя
 * @param string $password Пароль в открытом виде
 * @return array|null Ассоциативный массив с данными пользователя или null, если не найден/неверный пароль
 */
function authenticate_user(mysqli $connect, string $email, string $password): ?array
{
    $sql = "SELECT id, email, name, password_hash FROM users WHERE email = ?";
    $stmt = db_get_prepare_stmt($connect, $sql, [$email]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password_hash'])) {
        return $user;
    }

    return null;
}

function get_error_page(int $error, array $categories, string $user_name, int $is_auth): string
{
    $page_content = include_template("error_" . $error . ".php", ["categories" => $categories]);
    return include_template("layout.php", [
        "content" => $page_content,
        "title" => "Доступ запрещён",
        "categories" => $categories,
        "user_name" => $user_name,
        "is_auth" => $is_auth
    ]);
}


/**
 * Выполняет полнотекстовый поиск лотов по названию и описанию.
 *
 * @param mysqli $connect Подключение к БД
 * @param string $query Поисковый запрос
 * @return array Массив найденных лотов (может быть пустым)
 */
function search_lots(mysqli $connect, string $query): array
{
    $query = mb_strtolower(trim($query), 'UTF-8');

    $sql = "SELECT 
                l.id, 
                l.title, 
                l.description, 
                l.image_url, 
                l.initial_price, 
                l.date_end, 
                c.name AS category_name
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            WHERE 
                MATCH(l.title, l.description) AGAINST (?)
                AND l.date_end >= CURDATE()
            ORDER BY l.created_at DESC";

    $stmt = db_get_prepare_stmt($connect, $sql, [$query]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

/**
 * @param mysqli $connect
 * @param int $lot_id
 * @param int $user_id
 * @param int $amount
 * @return bool
 */
function add_bid(mysqli $connect, int $lot_id, int $user_id, int $amount): bool
{
    $sql = "INSERT INTO bids (lot_id, user_id, amount) VALUES (?, ?, ?)";
    $stmt = db_get_prepare_stmt($connect, $sql, [$lot_id, $user_id, $amount]);
    return mysqli_stmt_execute($stmt);
}

/**
 * @param mysqli $connect
 * @param int $lot_id
 * @return array|null
 */
function get_last_bid_for_lot(mysqli $connect, int $lot_id): ?array
{
    $sql = "SELECT user_id, amount FROM bids WHERE lot_id = ? ORDER BY created_at DESC LIMIT 1";
    $stmt = db_get_prepare_stmt($connect, $sql, [$lot_id]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}