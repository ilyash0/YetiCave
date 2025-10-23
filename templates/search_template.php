<?php
/** @var array $categories */
/** @var string $search_query */
/** @var array $search_results */
/** @var int $current_page */
/** @var int $total_pages */
/** @var bool $show_pagination */
?>

<main>
    <nav class="nav">
        <ul class="nav__list container">
            <?php foreach ($categories as $category): ?>
                <li class="nav__item">
                    <a href="all-lots.html"><?= htmlspecialchars($category['name']) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <div class="container">
        <section class="lots">
            <h2>Результаты поиска по запросу «<span><?= htmlspecialchars($search_query) ?></span>»</h2>

            <?php if (empty($all_results) && !empty($search_query)): ?>
                <p>Ничего не найдено по вашему запросу.</p>
            <?php elseif (empty($search_query)): ?>
                <p>Введите поисковый запрос.</p>
            <?php else: ?>
                <ul class="lots__list">

                    <?php foreach ($search_results as $lot):
                        $dt = get_dt_range($lot['date_end']);

                        $hours = $dt[0];
                        $minutes = $dt[1];

                        $image_url = htmlspecialchars($lot['image_url']);
                        $title_safe = htmlspecialchars($lot['title']);
                        $category_safe = htmlspecialchars($lot['category_name']);
                        $price_safe = htmlspecialchars(format_price($lot['initial_price']));

                        $timer_text =
                            str_pad($hours, 2, "0", STR_PAD_LEFT)
                            . ":" .
                            str_pad($minutes, 2, "0", STR_PAD_LEFT);
                        $timer_class = $hours < 2 ? 'lot__timer timer timer--finishing' : 'lot__timer timer';
                        ?>
                        <li class="lots__item lot">
                            <div class="lot__image">
                                <img src="<?= $image_url ?>" width="350" height="260" alt="">
                            </div>
                            <div class="lot__info">
                                <span class="lot__category"><?= $category_safe ?></span>
                                <h3 class="lot__title">
                                    <a class="text-link" href="/lot.php?id=<?= $lot["id"] ?>"><?= $title_safe ?></a>
                                </h3>
                                <div class="lot__state">
                                    <div class="lot__rate">
                                        <span class="lot__amount">Стартовая цена</span>
                                        <span class="lot__cost"><?= $price_safe ?></span>
                                    </div>

                                    <div class="<?= $timer_class ?>">
                                        <?= $timer_text ?>
                                    </div>

                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>

                </ul>
            <?php endif; ?>
        </section>

        <?php if ($show_pagination && $total_pages > 1): ?>
            <ul class="pagination-list">
                <li class="pagination-item pagination-item-prev">
                    <a href="?search=<?= urlencode($search_query) ?>&page=<?= max(1, $current_page - 1) ?>">Назад</a>
                </li>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i === $current_page): ?>
                        <li class="pagination-item pagination__item--current"><a><?= $i ?></a></li>
                    <?php else: ?>
                        <li class="pagination-item">
                            <a href="?search=<?= urlencode($search_query) ?>&page=<?= $i ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endfor; ?>

                <li class="pagination-item pagination-item-next">
                    <a href="?search=<?= urlencode($search_query) ?>&page=<?= min($total_pages, $current_page + 1) ?>">Вперед</a>
                </li>
            </ul>
        <?php endif; ?>
    </div>
</main>