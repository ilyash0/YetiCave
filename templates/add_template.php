<?php
/** @var array $categories */
/** @var array $errors */
?>

<main>
    <?php
    $selected_category = isset($_POST["category_id"]) ? (int)$_POST["category_id"] : -1;
    ?>
    <form class="form form--add-lot container <?= !empty($errors) ? "form--invalid" : "" ?>" action="/add.php"
          method="post" enctype="multipart/form-data">
        <h2>Добавление лота</h2>
        <div class="form__container-two">
            <div class="form__item <?= !empty($errors['title']) ? "form__item--invalid" : '' ?>"> <!-- form__item--invalid -->
                <label for="title">Наименование <sup>*</sup></label>
                <input id="title" type="text" name="title" placeholder="Введите наименование лота"
                       value="<?= htmlspecialchars($_POST["title"] ?? "") ?>" >
                <span class="form__error"><?= htmlspecialchars($errors["title"] ?? "") ?></span>
            </div>
            <div class="form__item <?= !empty($errors['category_id']) ? "form__item--invalid" : '' ?>">
                <label for="category_id">Категория <sup>*</sup></label>
                <select id="category_id" name="category_id" >
                    <option value="" disabled hidden
                        <?= $selected_category === -1 ? "selected" : "" ?>>Выберите категорию
                    </option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category["id"]) ?>"
                            <?= $selected_category === (int)($category["id"]) ? "selected" : "" ?>>
                            <?= htmlspecialchars($category["name"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="form__error"><?= htmlspecialchars($errors["category_id"] ?? "") ?></span>
            </div>
        </div>
        <div class="form__item form__item--wide <?= !empty($errors['description']) ? "form__item--invalid" : "" ?>">
            <label for="description">Описание <sup>*</sup></label>
            <textarea id="description" name="description" placeholder="Напишите описание лота" ><?= htmlspecialchars($_POST["description"] ?? "") ?></textarea>
            <span class="form__error"><?= htmlspecialchars($errors["description"] ?? "") ?></span>
        </div>
        <div class="form__item form__item--file <?= !empty($errors['uploaded_file']) ? "form__item--invalid" : "" ?>">
            <label>Изображение <sup>*</sup></label>
            <div class="form__input-file">
                <input class="visually-hidden" type="file" id="uploaded_file" name="uploaded_file" >
                <label for="uploaded_file">
                    Добавить
                </label>
                <span class="form__error"><?= htmlspecialchars($errors["uploaded_file"] ?? "") ?></span>
            </div>
        </div>
        <div class="form__container-three">
            <div class="form__item form__item--small <?= !empty($errors['initial_price']) ? "form__item--invalid" : "" ?>">
                <label for="initial_price">Начальная цена <sup>*</sup></label>
                <input id="initial_price" type="number" name="initial_price" placeholder="0"
                       value="<?= htmlspecialchars($_POST["initial_price"] ?? "") ?>" >
                <span class="form__error"><?= htmlspecialchars($errors["initial_price"] ?? "") ?></span>
            </div>
            <div class="form__item form__item--small <?= !empty($errors['bid_step']) ? "form__item--invalid" : "" ?>">
                <label for="bid_step">Шаг ставки <sup>*</sup></label>
                <input id="bid_step" type="number" name="bid_step" placeholder="0"
                       value="<?= htmlspecialchars($_POST["bid_step"] ?? "") ?>" >
                <span class="form__error"><?= htmlspecialchars($errors["bid_step"] ?? "") ?></span>
            </div>
            <div class="form__item <?= !empty($errors['date_end']) ? "form__item--invalid" : "" ?>">
                <label for="date_end">Дата окончания торгов <sup>*</sup></label>
                <input class="form__input-date" id="date_end" type="text" name="date_end"
                       placeholder="Введите дату в формате ГГГГ-ММ-ДД"
                       value="<?= htmlspecialchars($_POST["date_end"] ?? "") ?>">
                <span class="form__error"><?= htmlspecialchars($errors["date_end"] ?? "") ?></span>
            </div>
        </div>
        <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме.</span>
        <button type="submit" class="button">Добавить лот</button>
    </form>
</main>