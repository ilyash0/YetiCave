<?php
/** @var false|mysqli $connect */
date_default_timezone_set("Asia/Yekaterinburg");
const HOURS_IN_DAY = 24;

// —————————————————————————————————————————————————————————————————————————————
// 1. ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ (валидация, форматирование и т.д.)
// —————————————————————————————————————————————————————————————————————————————

/**
 * Форматирует цену с пробелами-разделителями и символом рубля.
 */
function format_price(int $amount): string
{
    return number_format($amount, 0, '', ' ') . ' ₽';
}

/**
 * Проверяет, что строка не пуста.
 */
function is_filled(string $text): bool
{
    return !empty(trim($text));
}

/**
 * Проверяет, что длина строки в заданном диапазоне.
 */
function is_valid_length(string $text, int $min, int $max): bool
{
    $len = strlen($text);
    return $len >= $min && $len <= $max;
}

/**
 * Проверяет, что значение — положительное число.
 */
function is_valid_price(string $value): bool
{
    return is_numeric($value) && (int)$value > 0;
}

/**
 * Проверяет, что дата в формате Y-m-d и в будущем.
 */
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

/**
 * Проверяет, что файл — изображение (jpeg, png, webp).
 */
function is_image($file): bool
{
    if (!is_array($file) || empty($file['tmp_name'])) {
        return false;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    return in_array($file_type, ['image/jpeg', 'image/png', 'image/webp']);
}

/**
 * Выполняет расчёт пагинации и возвращает нужный срез данных.

 */
function paginate_data(array $data, int $current_page, int $items_per_page): array
{
    $total_count = count($data);
    $total_pages = ceil($total_count / $items_per_page);

    if ($total_pages > 0) {
        $current_page = max(1, min($current_page, $total_pages));
    } else {
        $current_page = 1;
    }

    $offset = ($current_page - 1) * $items_per_page;
    $items = array_slice($data, $offset, $items_per_page);

    return [
        'items' => $items,
        'total_pages' => $total_pages,
        'current_page' => $current_page
    ];
}

// —————————————————————————————————————————————————————————————————————————————
// 2. ФУНКЦИИ ДЛЯ РАБОТЫ С БАЗОЙ ДАННЫХ (SELECT, INSERT, UPDATE)
// —————————————————————————————————————————————————————————————————————————————
/**
 * Выполняет SQL-запрос SELECT и возвращает все строки.
 * Использует подготовленные выражения (prepare).
 */
function db_fetch_all(mysqli $connect, string $sql, array $params = []): array
{
    if (empty($params)) {
        $result = mysqli_query($connect, $sql);
        return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
    }

    $stmt = db_get_prepare_stmt($connect, $sql, $params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

/**
 * Выполняет SQL-запрос SELECT и возвращает одну строку.
 * Использует подготовленные выражения (prepare).
 */
function db_fetch_one(mysqli $connect, string $sql, array $params = []): ?array
{
    if (empty($params)) {
        $result = mysqli_query($connect, $sql);
        return mysqli_fetch_assoc($result);
    }

    $stmt = db_get_prepare_stmt($connect, $sql, $params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_assoc($result);
}

/**
 * Возвращает список всех категорий.
 */
function get_categories_list(mysqli $connect): array
{
    $sql = "SELECT id, name, symbolic_code FROM categories";

    return db_fetch_all($connect, $sql);
}

/**
 * Возвращает активные лоты (торги не закончены).
 */
function get_active_lots_list(mysqli $connect): array
{
    $sql = "SELECT l.id, l.title, l.initial_price, l.image_url, 
                   c.name AS category_name, l.date_end,
                   COALESCE(MAX(b.amount), l.initial_price) AS current_price
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            LEFT JOIN bids b ON l.id = b.lot_id
            WHERE l.date_end >= CURDATE()
            GROUP BY l.id, l.created_at
            ORDER BY l.created_at DESC";

    return db_fetch_all($connect, $sql);
}

/**
 * Возвращает лот по ID.
 */
function get_lot_by_id(mysqli $connect, ?int $id): ?array
{
    if ($id === null) {
        return null;
    }

    $sql = "SELECT 
                l.id, l.title, l.initial_price, l.image_url, l.winner_id,
                l.description, l.bid_step, l.date_end, l.author_id,
                c.name AS category_name,
                COALESCE(MAX(b.amount), l.initial_price) AS current_price
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            LEFT JOIN bids b ON l.id = b.lot_id
            WHERE l.id = ?
            GROUP BY l.id";

    return db_fetch_one($connect, $sql, [$id]);
}

/**
 * Выполняет полнотекстовый поиск по активным лотам.
 */
function search_lots_by_query(mysqli $connect, string $query): array
{
    $query = trim($query);
    if (strlen($query) < 3) {
        return [];
    }

    $query = mb_strtolower($query, 'UTF-8');

    $sql = "SELECT 
                l.id, l.title, l.description, l.image_url, l.initial_price, 
                l.date_end, c.name AS category_name,
                COALESCE(MAX(b.amount), l.initial_price) AS current_price
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            LEFT JOIN bids b ON l.id = b.lot_id
            WHERE 
                MATCH(l.title, l.description) AGAINST (?)
                AND l.date_end >= CURDATE()
            GROUP BY l.id, l.created_at
            ORDER BY l.created_at DESC";

    return db_fetch_all($connect, $sql, [$query]);
}

/**
 * Возвращает ставки для лота (новые сверху).
 */
function get_bids_by_lot_id(mysqli $connect, int $lot_id): array
{
    $sql = "SELECT b.amount, b.created_at, u.name AS bidder_name
            FROM bids b
            JOIN users u ON b.user_id = u.id
            WHERE b.lot_id = ?
            ORDER BY b.created_at DESC";

    return db_fetch_all($connect, $sql, [$lot_id]);
}

/**
 * Возвращает ставки пользователя с информацией о лоте.
 */
function get_bets_by_user_id(mysqli $connect, int $user_id): array
{
    $sql = "SELECT 
                b.amount AS bet_amount, b.created_at AS bet_time, l.id AS lot_id,
                l.title AS lot_title, l.image_url, l.date_end, l.winner_id,
                l.initial_price, l.author_id, u.contact_information, c.name AS category_name,
                COALESCE(MAX(b2.amount), l.initial_price) AS current_price
            FROM bids b
            JOIN lots l ON b.lot_id = l.id
            JOIN users u ON l.author_id = u.id
            JOIN categories c ON l.category_id = c.id
            LEFT JOIN bids b2 ON l.id = b2.lot_id
            WHERE b.user_id = ?
            GROUP BY l.id, b.id, b.created_at
            ORDER BY b.created_at DESC";

    return db_fetch_all($connect, $sql, [$user_id]);
}

/**
 * Возвращает последнюю ставку для лота.
 */
function get_last_bid_for_lot(mysqli $connect, int $lot_id): ?array
{
    $sql = "SELECT user_id, amount FROM bids WHERE lot_id = ? ORDER BY created_at DESC LIMIT 1";
    return db_fetch_one($connect, $sql, [$lot_id]);
}

// —————————————————————————————————————————————————————————————————————————————
// 3. ФУНКЦИИ ДЛЯ РАБОТЫ С АВТОРИЗАЦИЕЙ
// —————————————————————————————————————————————————————————————————————————————

/**
 * Аутентифицирует пользователя.
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

/**
 * Регистрирует нового пользователя.
 */
function register_user(mysqli $connect, string $name, string $email, string $password, string $contact_information): bool
{
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (name, email, password_hash, contact_information)
            VALUES (?, ?, ?, ?)";

    $stmt = db_get_prepare_stmt($connect, $sql, [$name, $email, $password_hash, $contact_information]);

    return mysqli_stmt_execute($stmt);
}

// —————————————————————————————————————————————————————————————————————————————
// 4. ФУНКЦИИ ДЛЯ РАБОТЫ С ЛОТАМИ И СТАВКАМИ
// —————————————————————————————————————————————————————————————————————————————

/**
 * Создаёт новый лот.
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
                title, description, image_url, initial_price, bid_step, date_end, author_id, category_id
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
 * Добавляет ставку.
 */
function add_bid(mysqli $connect, int $lot_id, int $user_id, int $amount): bool
{
    $sql = "INSERT INTO bids (lot_id, user_id, amount) VALUES (?, ?, ?)";
    $stmt = db_get_prepare_stmt($connect, $sql, [$lot_id, $user_id, $amount]);
    return mysqli_stmt_execute($stmt);
}

/**
 * Проверяет и устанавливает победителя для лота.
 */
function try_set_winner_for_lot(mysqli $connect, int $lot_id): ?int
{
    $now = date('Y-m-d');
    $lot = get_lot_by_id($connect, $lot_id);

    if (!$lot || $lot['date_end'] >= $now || $lot['winner_id'] !== null) {
        return null;
    }

    $last_bid = get_last_bid_for_lot($connect, $lot_id);
    if (!$last_bid) {
        return null;
    }

    $winner_id = (int)$last_bid['user_id'];
    $sql = "UPDATE lots SET winner_id = ? WHERE id = ?";
    $stmt = db_get_prepare_stmt($connect, $sql, [$winner_id, $lot_id]);

    return mysqli_stmt_execute($stmt) ? $winner_id : null;
}

/**
 * Проверяет и устанавливает победителей для всех истёкших лотов.
 */
function check_and_set_expired_lots_winners(mysqli $connect): void
{
    $sql_check = "SELECT id FROM lots WHERE date_end < CURDATE() AND winner_id IS NULL";
    $result_check = mysqli_query($connect, $sql_check);

    if ($result_check) {
        while ($row = mysqli_fetch_assoc($result_check)) {
            try_set_winner_for_lot($connect, (int)$row['id']);
        }
    }
}

// —————————————————————————————————————————————————————————————————————————————
// 5. ФУНКЦИИ ДЛЯ ОТОБРАЖЕНИЯ (таймеры, время)
// —————————————————————————————————————————————————————————————————————————————

/**
 * Формирует таймер и класс для лота.
 */
function format_lot_timer_data(string $date_end): array
{
    $end_time = new DateTime($date_end);
    $now_time = new DateTime();
    $now = date('Y-m-d');

    if ($date_end < $now) {
        return ['text' => 'Торги окончены', 'class' => 'timer timer--end'];
    }

    $time_diff = $now_time->diff($end_time);
    $hours = $time_diff->h + ($time_diff->days * HOURS_IN_DAY);
    $minutes = $time_diff->i;

    $timer_text = str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" . str_pad($minutes, 2, "0", STR_PAD_LEFT);
    $timer_class = $time_diff->days === 0 && $hours < 2 ? 'timer timer--finishing' : 'timer';

    return ['text' => $timer_text, 'class' => $timer_class];
}

/**
 * Формирует относительное время (например, "5 мин. назад").
 */
function format_relative_time(string $mysql_datetime): string
{
    $bid_time = new DateTime($mysql_datetime);
    $now = new DateTime();
    $interval = $now->diff($bid_time);

    if ($interval->days === 0) {
        if ($interval->h > 0) {
            return $interval->h . ' ч. назад';
        }
        if ($interval->i > 0) {
            return $interval->i . ' мин. назад';
        }
        return 'только что';
    }

    return $bid_time->format('d.m.y в H:i');
}

// —————————————————————————————————————————————————————————————————————————————
// 6. ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ (например, для шаблонов)
// —————————————————————————————————————————————————————————————————————————————

/**
 * Возвращает HTML-страницу ошибки.
 */
function get_error_page(int $error, array $categories, string $user_name, int $is_auth): string
{
    $page_content = include_template($error . "_template.php");
    return include_template("layout.php", [
        "content" => $page_content,
        "title" => "Ошибка " . $error,
        "categories" => $categories,
        "user_name" => $user_name,
        "is_auth" => $is_auth
    ]);
}