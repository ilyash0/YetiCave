<?php
/** @var array $categories */
/** @var int|null $current_category_id */
?>

<ul class="nav__list container">
    <?php foreach ($categories as $category): ?>
        <li class="nav__item <?= $current_category_id === (int)$category["id"] ? "nav__item--current" : '' ?>">
            <a href="/all-lots.php?category=<?= urlencode($category["symbolic_code"]) ?>">
                <?= htmlspecialchars($category["name"]) ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>