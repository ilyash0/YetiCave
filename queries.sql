-- Добавление существующих категорий
INSERT INTO categories (name, symbolic_code)
VALUES ('Доски и лыжи', 'boards'),
       ('Крепления', 'attachment'),
       ('Ботинки', 'boots'),
       ('Одежда', 'clothing'),
       ('Инструменты', 'tools'),
       ('Разное', 'other');

-- Добавление тестовых пользователей
INSERT INTO users (email, password_hash, name, contact_information)
VALUES ('ivanov@mail.ru', 'hash1', 'Иван Иванов', 'Москва, ул. Пушкина 1'),
       ('petrov@mail.ru', 'hash2', 'Петр Петров', 'Санкт-Петербург, Невский пр. 10');

-- Добавление тестовых объявлений
INSERT INTO lots (author_id, category_id, title, description, image_url, initial_price, date_end, bid_step)
VALUES (1, 1, '2014 Rossignol District Snowboard', 'Описание отсутствует', 'img/lot-1.jpg', 10999,
        '2025-09-18', 100),
       (1, 1, 'DC Ply Mens 2016/2017 Snowboard', 'Описание отсутствует', 'img/lot-2.jpg', 15999,
        '2025-09-19', 100),
       (1, 2, 'Крепления Union Contact Pro 2015 года размер L/XL', 'Описание отсутствует', 'img/lot-3.jpg', 8000,
        '2025-09-20', 100),
       (1, 3, 'Ботинки для сноуборда DC Mutiny Charocal', 'Описание отсутствует', 'img/lot-4.jpg', 10999,
        '2025-09-21', 100),
       (1, 4, 'Куртка для сноуборда DC Mutiny Charocal', 'Описание отсутствует', 'img/lot-5.jpg', 7500,
        '2025-09-22', 100),
       (1, 6, 'Маска Oakley Canopy', 'Описание отсутствует', 'img/lot-6.jpg', 5400,
        '2025-09-23', 100);

-- Добавление тестовых ставок
INSERT INTO bids (lot_id, user_id, amount)
VALUES (1, 2, 11500),
       (1, 2, 12000),
       (2, 1, 8500);

-- Получить список всех категорий
SELECT *
FROM categories;

-- Список актуальных лотов с категориями
SELECT l.title,
       l.initial_price,
       l.image_url,
       c.name AS category_name,
       l.date_end
FROM lots l
         JOIN categories c ON l.category_id = c.id
WHERE l.date_end >= CURDATE()
ORDER BY l.created_at DESC;

-- Показать лот по ID с названием категории
SELECT l.id,
       l.title,
       l.description,
       l.author_id,
       l.bid_step,
       l.image_url,
       l.initial_price,
       c.name AS category_name,
       l.date_end
FROM lots l
         JOIN categories c ON l.category_id = c.id
WHERE l.id = 1;

-- Обновить название лота по ID
UPDATE lots
SET title = '2014 Rossignol District Snowboard (NEW)'
WHERE id = 1;

-- Получить ставки для лота с деталями
SELECT b.created_at,
       b.amount,
       l.title AS lot_title,
       u.name  AS user_name
FROM bids b
         JOIN users u ON b.user_id = u.id
         JOIN lots l ON b.lot_id = l.id
WHERE b.lot_id = 1
ORDER BY b.created_at DESC;


SELECT l.title,
       l.description,
       l.image_url,
       l.date_end,
       l.initial_price,
       c.name                                                AS category_name,
       GREATEST(COALESCE(MAX(b.amount), 0), l.initial_price) AS current_price,
       MAX(l.created_at)                                     AS created_at
FROM lots l
         JOIN categories c ON l.category_id = c.id
         LEFT JOIN bids b ON l.id = b.lot_id
WHERE l.date_end >= CURDATE()
GROUP BY l.id
ORDER BY created_at DESC;

