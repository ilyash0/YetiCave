<?php
/** @var array $categories */
/** @var array $lot */
/** @var int $is_auth */
?>

<main>
    <section class="lot-item container">
        <h2><?= htmlspecialchars($lot['title']) ?></h2>
        <div class="lot-item__content">
            <div class="lot-item__left">
                <div class="lot-item__image">
                    <img src="../<?= htmlspecialchars($lot['image_url']) ?>" width="730" height="548"
                         alt="<?= htmlspecialchars($lot['title']) ?>">
                </div>
                <p class="lot-item__category">Категория: <span><?= htmlspecialchars($lot['category_name']) ?></span></p>
                <p class="lot-item__description"><?= htmlspecialchars($lot['description']) ?></p>
            </div>
            <div class="lot-item__right">
                <div class="lot-item__state <?= !$is_auth ? "visually-hidden" : ""?>">
                    <?php
                    $dt = get_dt_range($lot['date_end']);

                    $hours = $dt[0];
                    $minutes = $dt[1];

                    $timer_text =
                        str_pad($hours, 2, "0", STR_PAD_LEFT)
                        . ":" .
                        str_pad($minutes, 2, "0", STR_PAD_LEFT);
                    $timer_class = $hours < 2 ? 'lot-item__timer timer timer--finishing' : 'lot-item__timer timer';
                    ?>
                    <div class="<?= htmlspecialchars($timer_class) ?>">
                        <?= htmlspecialchars($timer_text) ?>
                    </div>
                    <div class="lot-item__cost-state">
                        <div class="lot-item__rate">
                            <span class="lot-item__amount">Текущая цена</span>
                            <span class="lot-item__cost"><?= htmlspecialchars(format_price($lot['current_price'])) ?></span>
                        </div>
                        <div class="lot-item__min-cost">
                            Мин. ставка <span><?= htmlspecialchars(format_price($lot['initial_price'])) ?></span>
                        </div>
                    </div>

                    <?php
                    $bid_errors = $_SESSION['bid_errors'] ?? [];
                    unset($_SESSION['bid_errors']);
                    ?>

                    <form class="lot-item__form" action="/bid.php" method="post" autocomplete="off">
                        <input type="hidden" name="lot_id" value="<?= (int)$lot['id'] ?>">
                        <p class="lot-item__form-item form__item form__item--invalid">
                            <label for="cost">Ваша ставка</label>
                            <input id="cost" type="text" name="cost"
                                   placeholder="<?= (int)($lot['current_price'] + $lot['bid_step']) ?>">
                            <?php if (!empty($bid_errors['error'])): ?>
                                <span class="form__error"><?= htmlspecialchars($bid_errors['error']) ?></span>
                            <?php endif; ?>
                        </p>
                        <button type="submit" class="button">Сделать ставку</button>
                    </form>
                </div>
                <div class="history">
                    <h3>История ставок (<span>10</span>)</h3>
                    <table class="history__list">
                        <tr class="history__item">
                            <td class="history__name">Иван</td>
                            <td class="history__price">10 999 р</td>
                            <td class="history__time">5 минут назад</td>
                        </tr>
                        <tr class="history__item">
                            <td class="history__name">Константин</td>
                            <td class="history__price">10 999 р</td>
                            <td class="history__time">20 минут назад</td>
                        </tr>
                        <tr class="history__item">
                            <td class="history__name">Евгений</td>
                            <td class="history__price">10 999 р</td>
                            <td class="history__time">Час назад</td>
                        </tr>
                        <tr class="history__item">
                            <td class="history__name">Игорь</td>
                            <td class="history__price">10 999 р</td>
                            <td class="history__time">19.03.17 в 08:21</td>
                        </tr>
                        <tr class="history__item">
                            <td class="history__name">Енакентий</td>
                            <td class="history__price">10 999 р</td>
                            <td class="history__time">19.03.17 в 13:20</td>
                        </tr>
                        <tr class="history__item">
                            <td class="history__name">Семён</td>
                            <td class="history__price">10 999 р</td>
                            <td class="history__time">19.03.17 в 12:20</td>
                        </tr>
                        <tr class="history__item">
                            <td class="history__name">Илья</td>
                            <td class="history__price">10 999 р</td>
                            <td class="history__time">19.03.17 в 10:20</td>
                        </tr>
                        <tr class="history__item">
                            <td class="history__name">Енакентий</td>
                            <td class="history__price">10 999 р</td>
                            <td class="history__time">19.03.17 в 13:20</td>
                        </tr>
                        <tr class="history__item">
                            <td class="history__name">Семён</td>
                            <td class="history__price">10 999 р</td>
                            <td class="history__time">19.03.17 в 12:20</td>
                        </tr>
                        <tr class="history__item">
                            <td class="history__name">Илья</td>
                            <td class="history__price">10 999 р</td>
                            <td class="history__time">19.03.17 в 10:20</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>