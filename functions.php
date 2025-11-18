<?php
date_default_timezone_set("Asia/Yekaterinburg");
const HOURS_IN_DAY = 24;
const MAX_EMAIL_LEN = 255;
const MAX_NAME_LEN = 150;
const MAX_MESSAGE_LEN = 255;
const MIN_PASSWORD_LEN = 8;
const MAX_PASSWORD_LEN = 255;
const MAX_DESCRIPTION_LEN = 5000;


// UTILITY FUNCTIONS
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

/**
 * Проверка корректности выбранной категории
 */
function is_valid_category(array $categories, int $category_id): bool
{
    foreach ($categories as $category) {
        if ((int)$category['id'] === $category_id) {
            return true;
        }
    }
    return false;
}

/**
 * Проверяет, существует ли пользователь с указанным email
 */
function is_email_exists(mysqli $connect, string $email): bool
{
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = db_get_prepare_stmt($connect, $sql, [$email]);
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_num_rows($result) > 0;
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
    $format = "Y-m-d";
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
    if (!is_array($file) || empty($file["tmp_name"])) {
        return false;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file_type = finfo_file($finfo, $file["tmp_name"]);
    finfo_close($finfo);

    return in_array($file_type, ["image/jpeg", "image/png", "image/jpg"]);
}

/**
 * Проверяет, содержит ли строка заглавные буквы
 */
function has_uppercase(string $string): bool
{
    return (bool)preg_match("/[A-ZА-ЯЁ]/u", $string);
}

/**
 * Проверяет, содержит ли строка строчные буквы
 */
function has_lowercase(string $string): bool
{
    return (bool)preg_match("/[a-zа-яё]/u", $string);
}

/**
 * Проверяет, содержит ли строка цифры
 */
function has_digit(string $string): bool
{
    return (bool)preg_match("/[0-9]/", $string);
}

/**
 * Проверяет, содержит ли строка специальные символы
 */
function has_special_chars(string $string): bool
{
    return (bool)preg_match("/[^a-zа-яё0-9\s]/iu", $string);
}

/**
 * Проверяет, является ли строка одним из часто используемых ненадежных паролей
 */
function is_common_password(string $string): bool
{
    $common_passwords = [
        "123456", "password", "12345678", "qwerty", "12345", "123456789",
        "1234", "111111", "1234567", "dragon", "123123", "baseball",
        "admin", "letmein", "welcome", "monkey", "login", "abc123",
        "starwars", "1234qwer", "1q2w3e4r", "1qaz2wsx", "trustno1",
        "привет", "qwerty123", "password1", "iloveyou", "1234567890",
        "asdfgh", "123321", "123qwe", "q1w2e3r4", "zaq12wsx",
        "qwertyuiop", "1q2w3e4r5t", "123456a", "123456789a", "987654321",
        "пароль", "йцукен"
    ];

    return in_array(mb_strtolower($string, "UTF-8"), $common_passwords);
}

/**
 * Группирует ошибки валидации по полям формы
 */
function group_errors_by_field(array $errors, array $fields): array
{
    $field_errors = array_fill_keys($fields, []);

    foreach ($errors as $error_key => $error_message) {
        foreach ($fields as $field) {
            if (str_starts_with($error_key, $field . '_')) {
                $field_errors[$field][] = $error_message;
                break;
            }
        }
    }

    return $field_errors;
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
        "items" => $items,
        "total_pages" => $total_pages,
        "current_page" => $current_page
    ];
}


// DATA BASE FUNCTIONS
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

    $query = mb_strtolower($query, "UTF-8");

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
 * Получает категорию по её названию
 */
function get_category_by_symbolic_code(mysqli $connect, string $name): ?array
{
    $name = trim($name);
    $sql = "SELECT * FROM categories WHERE symbolic_code = ?";

    return db_fetch_one($connect, $sql, [$name]);
}

/**
 * Получает активные лоты по ID категории
 */
function get_lots_by_category_id(mysqli $connect, int $category_id): array
{
    $sql = "SELECT 
                l.id, l.title, l.description, l.image_url, l.initial_price, 
                l.date_end, c.name AS category_name,
                COALESCE(MAX(b.amount), l.initial_price) AS current_price
            FROM lots l
            INNER JOIN categories c ON l.category_id = c.id
            LEFT JOIN bids b ON l.id = b.lot_id
            WHERE 
                l.category_id = ?
                AND l.date_end >= NOW()
            GROUP BY l.id, l.created_at
            ORDER BY l.created_at DESC";

    return db_fetch_all($connect, $sql, [$category_id]);
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
function get_bids_by_user_id(mysqli $connect, int $user_id): array
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


//  AUTH FUNCTIONS
/**
 * Основная функция валидации — возвращает массив ошибок по полям
 */
function validate_registration(mysqli $conn, array $input, array $strings): array
{
    $errors = [];

    $email = mb_strtolower(trim($input['email'] ?? ''));
    $password = $input['password'] ?? '';
    $name = trim($input['name'] ?? '');
    $message = trim($input['message'] ?? '');
    $recaptcha_token = $input['recaptcha_token'] ?? '';

    if (!is_filled($email)) {
        $errors['email'] = $strings['email_empty'];
    } elseif (!is_valid_length($email, 0, MAX_EMAIL_LEN)) {
        $errors['email'] = $strings['email_long'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = $strings['email_invalid'];
    } elseif (is_email_exists($conn, $email)) {
        $errors['email'] = $strings['email_exists'];
    }

    if (!is_filled($email)) {
        $errors['password_empty'] = $strings['password_empty'];
    } elseif (strlen($password) < MIN_PASSWORD_LEN) {
        $errors['password_short'] = $strings['password_short'];
    } elseif (strlen($password) > MAX_PASSWORD_LEN) {
        $errors['password_long'] = $strings['password_long'];
    } else {
        if (!has_uppercase($password)) {
            $errors['password_no_uppercase'] = $strings['password_no_uppercase'];
        }
        if (!has_lowercase($password)) {
            $errors['password_no_lowercase'] = $strings['password_no_lowercase'];
        }
        if (!has_digit($password)) {
            $errors['password_no_digits'] = $strings['password_no_digits'];
        }
        if (!has_special_chars($password)) {
            $errors['password_no_special_chars'] = $strings['password_no_special_chars'];
        }
        if (is_common_password($password)) {
            $errors['password_common'] = $strings['password_common'];
        }
    }

    if (!is_filled($name)) {
        $errors['name'] = $strings['name_empty'];
    } elseif (!is_valid_length($name, 0, MAX_NAME_LEN)) {
        $errors['name'] = $strings['name_long'];
    }

    if (!is_filled($message)) {
        $errors['message'] = $strings['message_empty'];
    } elseif (!is_valid_length($message, 0, MAX_MESSAGE_LEN)) {
        $errors['message'] = $strings['message_long'];
    }

    if (!validate_recaptcha($recaptcha_token, "signup")) {
        $errors['recaptcha'] = $strings['recaptcha_failed'];
    }

    return $errors;
}

/**
 * Основная функция валидации — возвращает массив ошибок по полям
 */
function validate_authentication(array $input, array $strings): array
{
    $errors = [];
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $recaptcha_token = $input['recaptcha_token'] ?? '';

    if (!is_filled($email)) {
        $errors['email'] = $strings['email_empty'];
    }

    if (empty($password)) {
        $errors['password'] = $strings['password_empty'];
    }

    if (!validate_recaptcha($recaptcha_token, "login")) {
        $errors['recaptcha'] = $strings['recaptcha_failed'];
    }

    return $errors;
}

/**
 * @param mixed $recaptcha_token
 * @param string $expected_action
 * @return bool
 */
function validate_recaptcha(string $recaptcha_token, string $expected_action): bool
{
    if (empty($recaptcha_token)) {
        return false;
    }

    $rec = verify_recaptcha_v3(RECAPTCHA_SECRET, $recaptcha_token);

    if (!$rec || empty($rec['success'])) {
        return false;
    }

    if (isset($rec['action']) && $rec['action'] !== $expected_action) {
        return false;
    }

    if (isset($rec['score']) && $rec['score'] < RECAPTCHA_MIN_SCORE) {
        return false;
    }

    return true;
}

function verify_recaptcha_v3(string $secret, string $token, ?string $remote_ip = null): ?array
{
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => $secret,
        'response' => $token
    ];
    if ($remote_ip) {
        $data['remoteip'] = $remote_ip;
    }

    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
            'timeout' => 5
        ]
    ];

    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    if ($result === false) {
        return null;
    }
    return json_decode($result, true);
}

/**
 * Аутентифицирует пользователя.
 */
function authenticate_user(mysqli $connect, string $email, string $password): ?array
{
    $sql = "SELECT id, email, name, password_hash FROM users WHERE email = ?";
    $user = db_fetch_one($connect, $sql, [$email]);

    if ($user && password_verify($password, $user["password_hash"])) {
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


// LOTS FUNCTIONS
/**
 * Валидация формы добавления лота
 */
function validate_lot_creation(array $data, array $strings, array $categories): array
{
    $errors = [];

    if (!is_filled($data['title'])) {
        $errors['title'] = $strings['title_empty'];
    } elseif (!is_valid_length($data['title'], 0, MAX_MESSAGE_LEN)) {
        $errors['title'] = $strings['title_long'];
    }

    if (!is_filled($data['category_id'])) {
        $errors['category_id'] = $strings['category_empty'];
    } elseif (!is_valid_category($categories, (int)$data['category_id'])) {
        $errors['category_id'] = $strings['category_invalid'];
    }

    if (!is_filled($data['description'])) {
        $errors['description'] = $strings['description_empty'];
    } elseif (!is_valid_length($data['description'], 0, MAX_DESCRIPTION_LEN)) {
        $errors['description'] = $strings['description_long'];
    }

    if (!is_filled($data['initial_price'])) {
        $errors['initial_price'] = $strings['price_empty'];
    } elseif (!is_valid_price($data['initial_price'])) {
        $errors['initial_price'] = $strings['price_invalid'];
    }

    if (!is_filled($data['bid_step'])) {
        $errors['bid_step'] = $strings['step_empty'];
    } elseif (!is_valid_price($data['bid_step'])) {
        $errors['bid_step'] = $strings['step_invalid'];
    }

    if (!is_uploaded_file($data['uploaded_file']['tmp_name'] ?? '')) {
        $errors['uploaded_file'] = $strings['image_empty'];
    } elseif (!is_image($data['uploaded_file'])) {
        $errors['uploaded_file'] = $strings['image_invalid'];
    }

    if (!is_filled($data['date_end'])) {
        $errors['date_end'] = $strings['date_empty'];
    } elseif (!is_valid_date($data['date_end'])) {
        $errors['date_end'] = $strings['date_invalid'];
    }

    return $errors;
}

/**
 * Создаёт новый лот.
 */
function create_lot(mysqli $connect, array $lot_data, string $upload_dir = "uploads/"): ?int
{
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file_extension = pathinfo($lot_data["uploaded_file"]["name"], PATHINFO_EXTENSION);
    $filename = uniqid("lot_", true) . "." . strtolower($file_extension);
    $filepath = $upload_dir . $filename;

    if (!move_uploaded_file($lot_data["uploaded_file"]["tmp_name"], $filepath)) {
        return null;
    }

    $sql = "INSERT INTO lots (
                title, description, image_url, initial_price, bid_step, date_end, author_id, category_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = db_get_prepare_stmt($connect, $sql, [
        $lot_data["title"],
        $lot_data["description"],
        $filepath,
        $lot_data["initial_price"],
        $lot_data["bid_step"],
        $lot_data["date_end"],
        $lot_data["author_id"],
        $lot_data["category_id"]
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
 * Проверяет и устанавливает победителя для лота.
 */
function set_winner_for_lot(mysqli $connect, int $lot_id): ?int
{
    $now = date("Y-m-d");
    $lot = get_lot_by_id($connect, $lot_id);

    if (!$lot || $lot["date_end"] >= $now || $lot["winner_id"] !== null) {
        return null;
    }

    $last_bid = get_last_bid_for_lot($connect, $lot_id);
    if (!$last_bid) {
        return null;
    }

    $winner_id = (int)$last_bid["user_id"];
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
            set_winner_for_lot($connect, (int)$row["id"]);
        }
    }
}


//  FORMAT FUNCTIONS
/**
 * Формирует таймер и класс для лота.
 */
function format_lot_timer_data(string $date_end): array
{
    $end_time = new DateTime($date_end);
    $now_time = new DateTime();
    $now = date("Y-m-d");

    if ($date_end < $now) {
        return ["text" => "Торги окончены", "class" => "timer timer--end"];
    }

    $time_diff = $now_time->diff($end_time);
    $hours = $time_diff->h + ($time_diff->days * HOURS_IN_DAY);
    $minutes = $time_diff->i;

    $timer_text = str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" . str_pad($minutes, 2, "0", STR_PAD_LEFT);
    $timer_class = $time_diff->days === 0 && $hours < 2 ? "timer timer--finishing" : "timer";

    return ["text" => $timer_text, "class" => $timer_class];
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
            return $interval->h . " ч. назад";
        }
        if ($interval->i > 0) {
            return $interval->i . " мин. назад";
        }
        return "только что";
    }

    return $bid_time->format("d.m.y в H:i");
}

/**
 * Форматирует цену с пробелами-разделителями и символом рубля.
 */
function format_price(int $amount): string
{
    return number_format($amount, 0, "", " ") . " ₽";
}

/**
 * Функция для формирования URL с параметрами страницы
 */
function build_pagination_params(int $page_num, array $params): string
{
    $params["page"] = $page_num;
    $query_string = http_build_query($params);
    return $query_string ? "?" . $query_string : "";
}
