CREATE TABLE IF NOT EXISTS users
(
    id                  INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    registered_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    email               VARCHAR(255) NOT NULL UNIQUE,
    password_hash       VARCHAR(255) NOT NULL,
    name                VARCHAR(150) NOT NULL,
    contact_information VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS categories
(
    id            INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(150) NOT NULL,
    symbolic_code VARCHAR(50)  NOT NULL,
    INDEX idx_categories_name (name)
);

CREATE TABLE IF NOT EXISTS lots
(
    id            INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    author_id     INT          NOT NULL,
    category_id   INT          NOT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    title         VARCHAR(255) NOT NULL,
    description   TEXT         NOT NULL,
    image_url     VARCHAR(255) NOT NULL,
    initial_price INT UNSIGNED NOT NULL,
    date_end      DATE         NOT NULL,
    bid_step      INT UNSIGNED NOT NULL,
    winner_id     INT          NULL,
    CONSTRAINT fk_lots_author FOREIGN KEY (author_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_lots_category FOREIGN KEY (category_id) REFERENCES categories (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_lots_winner FOREIGN KEY (winner_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS bids
(
    id         INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    lot_id     INT          NOT NULL,
    user_id    INT          NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    amount     INT UNSIGNED NOT NULL,
    CONSTRAINT fk_bids_lot FOREIGN KEY (lot_id) REFERENCES lots (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_bids_user FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE lots ADD FULLTEXT(title, description);

ALTER TABLE lots
    MODIFY title VARCHAR(255)
        CHARACTER SET utf8mb4
        COLLATE utf8mb4_unicode_ci,
    MODIFY description TEXT
        CHARACTER SET utf8mb4
        COLLATE utf8mb4_unicode_ci;
