<?php
/** @var array $categories */
/** @var array $bets */
/** @var string $user_name */
/** @var int $user_id */
/** @var int $is_auth */
?>

<main>
    <section class="rates container">
        <h2>Мои ставки</h2>
        <?php if (empty($bets)): ?>
            <p>У вас пока нет ставок.</p>
        <?php else: ?>
            <table class="rates__list">
                <?php foreach ($bets as $bet): ?>
                    <?php
                    $image_url = htmlspecialchars($bet['image_url']);
                    $title_safe = htmlspecialchars($bet['lot_title']);
                    $category_name = htmlspecialchars($bet['category_name']);
                    $bet_amount = (int)$bet['bet_amount'];
                    $current_price = (int)$bet['current_price'];
                    $date_end = $bet['date_end'];
                    $winner_id = $bet['winner_id'];
                    $lot_id = (int)$bet['lot_id'];
                    $now = date('Y-m-d');
                    $is_expired = $date_end < $now;

                    // Вычисляем оставшееся время
                    $end_time = new DateTime($date_end);
                    $now_time = new DateTime();
                    $time_diff = $now_time->diff($end_time);

                    $timer = get_lot_timer_data($date_end);
                    $timer_text = $timer['text'];
                    $timer_class = $timer['class'];

                    // Определяем статус строки
                    $status_class = '';
                    if ($is_expired) {
                        if ($winner_id && (int)$winner_id === (int)$_SESSION['user_id']) {
                            $status_class = 'rates__item--win';
                        } else {
                            $status_class = 'rates__item--lose';
                        }
                    } else {
                        $status_class = 'rates__item--active';
                    }
                    ?>
                    <tr class="rates__item <?= $status_class ?>">
                        <td class="rates__info">
                            <div class="rates__img">
                                <img src="<?= $image_url ?>" width="54" height="40" alt="<?= $title_safe ?>">
                            </div>
                            <h3 class="rates__title">
                                <a href="/lot.php?id=<?= $lot_id ?>"><?= $title_safe ?></a>
                            </h3>
                        </td>
                        <td class="rates__category">
                            <?= $category_name ?>
                        </td>
                        <td class="rates__timer">
                            <div class="<?= $timer_class ?>"><?= $timer_text ?></div>
                        </td>
                        <td class="rates__price">
                            <?= htmlspecialchars(format_price($bet_amount)) ?> р
                        </td>
                        <td class="rates__time">
                            <?= format_relative_time($bet['bet_time']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </section>
</main>