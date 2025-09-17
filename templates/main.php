<?php
/** @var array $categories */
/** @var array $products */
/** @var callable $format_price */
?>

<section class="promo">
    <h2 class="promo__title">Нужен стафф для катки?</h2>
    <p class="promo__text">На нашем интернет-аукционе ты найдёшь самое эксклюзивное сноубордическое и горнолыжное
        снаряжение.</p>
    <ul class="promo__list">

        <?php foreach ($categories as $category): ?>
            <li class="promo__item promo__item--boards">
                <a class="promo__link" href="pages/all-lots.html"><?= htmlspecialchars($category) ?></a>
            </li>
        <?php endforeach; ?>

    </ul>
</section>
<section class="lots">
    <div class="lots__header">
        <h2>Открытые лоты</h2>
    </div>
    <ul class="lots__list">

        <?php foreach ($products as $product):
            $dt = get_dt_range($product['expiration_date']);

            $hours = $dt[0];
            $minutes = $dt[1];

            if ($hours < 1) {
                continue;
            }

            $imageSrc = htmlspecialchars($product['image']);
            $nameSafe = htmlspecialchars($product['name']);
            $categorySafe = htmlspecialchars($product['category']);
            $priceSafe = htmlspecialchars(format_price($product['price']));

            $timerText =
                str_pad($hours, 2, "0", STR_PAD_LEFT)
                . ":" .
                str_pad($minutes, 2, "0", STR_PAD_LEFT);
            $timerClass = $hours < 2 ? 'lot__timer timer timer--finishing' : 'lot__timer timer';
            ?>
            <li class="lots__item lot">
                <div class="lot__image">
                    <img src="<?= $imageSrc ?>" width="350" height="260" alt="">
                </div>
                <div class="lot__info">
                    <span class="lot__category"><?= $categorySafe ?></span>
                    <h3 class="lot__title">
                        <a class="text-link" href="pages/lot.html"><?= $nameSafe ?></a>
                    </h3>
                    <div class="lot__state">
                        <div class="lot__rate">
                            <span class="lot__amount">Стартовая цена</span>
                            <span class="lot__cost"><?= $priceSafe ?></span>
                        </div>

                        <div class="<?= $timerClass ?>">
                            <?= $timerText ?>
                        </div>

                    </div>
                </div>
            </li>
        <?php endforeach; ?>

    </ul>
</section>