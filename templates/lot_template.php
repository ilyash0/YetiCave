<?php
/** @var array $categories */
/** @var array $lot */
/** @var array $bids */
/** @var int $bids_count */
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
                <div class="lot-item__state <?= !$is_auth ? "visually-hidden" : "" ?>">
                    <?php
                    $timer = get_lot_timer_data($lot['date_end']);
                    $timer_text = $timer['text'];
                    $timer_class = $timer['class'];
                    ?>
                    <div class="lot-item__timer <?= htmlspecialchars($timer_class) ?>">
                        <?= htmlspecialchars($timer_text) ?>
                    </div>
                    <div class="lot-item__cost-state">
                        <div class="lot-item__rate">
                            <span class="lot-item__amount">Текущая цена</span>
                            <span class="lot-item__cost"><?= htmlspecialchars(format_price($lot['current_price'])) ?></span>
                        </div>
                        <div class="lot-item__min-cost">
                            Мин. ставка
                            <span>
                                <?= htmlspecialchars(format_price((int)($lot['current_price'] + $lot['bid_step']))) ?>
                            </span>
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
                    <h3>История ставок (<span><?= $bids_count ?></span>)</h3>
                    <?php if ($bids_count > 0): ?>
                        <table class="history__list">
                            <?php foreach ($bids as $bid): ?>
                                <tr class="history__item">
                                    <td class="history__name"><?= htmlspecialchars($bid['bidder_name']) ?></td>
                                    <td class="history__price"><?= htmlspecialchars(format_price($bid['amount'])) ?></td>
                                    <td class="history__time"><?= format_relative_time($bid['created_at']) ?> </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <p>Ставок пока нет.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>