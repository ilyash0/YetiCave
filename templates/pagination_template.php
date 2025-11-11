<?php
/** @var int $current_page */
/** @var int $total_pages */
?>

<?php if ($total_pages > 1): ?>
    <?php
    $params = $_GET;
    unset($params['page']);
    ?>

    <ul class="pagination-list">
        <li class="pagination-item pagination-item-prev">
            <a href="<?= build_pagination_params(max(1, $current_page - 1), $params) ?>">Назад</a>
        </li>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php if ($i === $current_page): ?>
                <li class="pagination-item pagination-item-active"><a><?= $i ?></a></li>
            <?php else: ?>
                <li class="pagination-item">
                    <a href="<?= build_pagination_params($i, $params) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endfor; ?>

        <li class="pagination-item pagination-item-next">
            <a href="<?= build_pagination_params(min($total_pages, $current_page + 1), $params) ?>">Вперед</a>
        </li>
    </ul>
<?php endif; ?>