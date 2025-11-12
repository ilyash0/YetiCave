<?php
/** @var array $lot */

$image_url = htmlspecialchars($lot["image_url"]);
$title_safe = htmlspecialchars($lot["title"]);
$category_safe = htmlspecialchars($lot["category_name"] ?? '');
$price_safe = htmlspecialchars(format_price($lot["current_price"]));

$timer = format_lot_timer_data($lot["date_end"]);
$timer_text = $timer["text"];
$timer_class = $timer["class"];
?>

<li class="lots__item lot">
    <div class="lot__image">
        <a href="/lot.php?id=<?= (int)$lot["id"] ?>">
            <img src="<?= $image_url ?>" width="350" height="260" alt="<?= $title_safe ?>">
        </a>
    </div>
    <div class="lot__info">
        <span class="lot__category"><?= $category_safe ?></span>
        <h3 class="lot__title">
            <a class="text-link" href="/lot.php?id=<?= (int)$lot["id"] ?>"><?= $title_safe ?></a>
        </h3>
        <div class="lot__state">
            <div class="lot__rate">
                <span class="lot__amount">Стартовая цена</span>
                <span class="lot__cost"><?= $price_safe ?></span>
            </div>
            <div class="lot__timer <?= $timer_class ?>">
                <?= $timer_text ?>
            </div>
        </div>
    </div>
</li>