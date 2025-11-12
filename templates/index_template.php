<?php
/** @var array $categories */
/** @var array $lots */
/** @var int $current_page */
/** @var int $total_pages */
?>

<main class="container">
    <section class="promo">
        <h2 class="promo__title">Нужен стафф для катки?</h2>
        <p class="promo__text">На нашем интернет-аукционе ты найдёшь самое эксклюзивное сноубордическое и горнолыжное
            снаряжение.</p>
        <ul class="promo__list">

            <?php foreach ($categories as $category): ?>
                <li class="promo__item promo__item--<?= htmlspecialchars($category["symbolic_code"]) ?>">
                    <a class="promo__link"
                       href="/all-lots.php?category=<?= urlencode($category["symbolic_code"]) ?>"><?= htmlspecialchars($category["name"]) ?></a>
                </li>
            <?php endforeach; ?>

        </ul>
    </section>
    <section class="lots">
        <div class="lots__header">
            <h2>Открытые лоты</h2>
        </div>
        <ul class="lots__list">
            <?php foreach ($lots as $lot): ?>
                <?= include_template("lot-item_template.php", ["lot" => $lot]) ?>
            <?php endforeach; ?>
        </ul>
    </section>
</main>