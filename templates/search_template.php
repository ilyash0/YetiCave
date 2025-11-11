<?php
/** @var string $title */
/** @var string $search_query */
/** @var array $lots */
/** @var int $current_page */
/** @var int $total_pages */
?>

<main>
    <div class="container">
        <section class="lots">
            <h2><?= $title ?></h2>

            <?php if (empty($lots)): ?>
                <p>Ничего не найдено по вашему запросу.</p>
            <?php elseif (empty($search_query)): ?>
                <p>Введите поисковый запрос.</p>
            <?php else: ?>
                <ul class="lots__list">
                    <?php foreach ($lots as $lot): ?>
                        <?= include_template('lot-item.php', ['lot' => $lot]) ?>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <?php if ($total_pages > 1): ?>
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