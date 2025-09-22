CREATE DATABASE IF NOT EXISTS yeticave;
USE yeticave;

CREATE TABLE IF NOT EXISTS users
(
    id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    registered_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    email               VARCHAR(255) NOT NULL,
    password_hash       VARCHAR(255) NOT NULL,
    name                VARCHAR(150) NOT NULL,
    contact_information TEXT         NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY ux_users_email (email),
    INDEX idx_users_registered_at (registered_at)
);

CREATE TABLE IF NOT EXISTS categories
(
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name          VARCHAR(150) NOT NULL,
    symbolic_code VARCHAR(50)  NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY ux_categories_symbolic_code (symbolic_code),
    INDEX idx_categories_name (name)
);

CREATE TABLE IF NOT EXISTS lots
(
    id            INT UNSIGNED           NOT NULL AUTO_INCREMENT,
    author_id     INT UNSIGNED           NOT NULL,
    category_id   INT UNSIGNED           NOT NULL,
    created_at    DATETIME               NOT NULL DEFAULT CURRENT_TIMESTAMP,
    title         VARCHAR(255)           NOT NULL,
    description   TEXT                   NOT NULL,
    image_url     VARCHAR(255)           NOT NULL,
    initial_price INT UNSIGNED           NOT NULL,
    date_end      DATE                   NOT NULL,
    bid_step      INT UNSIGNED           NOT NULL,
    winner_id     INT UNSIGNED           NULL,
    PRIMARY KEY (id),
    FULLTEXT KEY ft_lots_title_description (title, description),
    INDEX idx_lots_category_id (category_id),
    INDEX idx_lots_created_at (created_at),
    INDEX idx_lots_date_end (date_end),
    INDEX idx_lots_author_id (author_id),
    INDEX idx_lots_winner_id (winner_id),
    CONSTRAINT fk_lots_author FOREIGN KEY (author_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_lots_category FOREIGN KEY (category_id) REFERENCES categories (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_lots_winner FOREIGN KEY (winner_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS bids
(
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    lot_id     INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    amount     INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    INDEX idx_bids_lot_id (lot_id),
    INDEX idx_bids_user_id (user_id),
    INDEX idx_bids_created_at (created_at),
    CONSTRAINT fk_bids_lot FOREIGN KEY (lot_id) REFERENCES lots (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_bids_user FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE INDEX idx_bids_lot_amount ON bids (lot_id, amount);

CREATE INDEX idx_bids_user_created_at ON bids (user_id, created_at);
