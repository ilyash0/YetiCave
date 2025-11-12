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
                        <?= include_template("lot-item_template.php", ["lot" => $lot]) ?>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <?= include_template("pagination_template.php", [
            "current_page" => $current_page,
            "total_pages" => $total_pages
        ]) ?>
    </div>
</main>