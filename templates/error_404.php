<?php
/** @var array $categories */
?>

<main>
    <nav class="nav">
        <ul class="nav__list container">
            <?php foreach ($categories as $category): ?>
                <li class="nav__item">
                    <a href="all-lots.html"><?= htmlspecialchars($category['name']) ?></a>
                </li>
            <?php endforeach; ?>
    </nav>
    <section class="lot-item container">
        <h2>Ошибка 404: страница не найдена</h2>
        <p>Данной страницы не существует на сайте.</p>
    </section>
</main>