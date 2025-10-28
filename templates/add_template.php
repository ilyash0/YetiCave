<?php
/** @var array $categories */
/** @var array $errors */
?>

<main>
    <?php
    $selected_category = isset($_POST['category']) ? (int)$_POST['category'] : -1;
    ?>
    <form class="form form--add-lot container <?= !empty($errors) ? 'form--invalid' : '' ?>" action="/add.php"
          method="post" enctype="multipart/form-data">
        <h2>Добавление лота</h2>
        <div class="form__container-two">
            <div class="form__item <?= in_array("lot-name", $errors) ? 'form__item--invalid' : '' ?>"> <!-- form__item--invalid -->
                <label for="lot-name">Наименование <sup>*</sup></label>
                <input id="lot-name" type="text" name="lot-name" placeholder="Введите наименование лота"
                       value="<?= htmlspecialchars($_POST['lot-name'] ?? "") ?>" >
                <span class="form__error">Введите наименование лота. Не более 255 символов</span>
            </div>
            <div class="form__item <?= in_array("category", $errors) ? 'form__item--invalid' : '' ?>">
                <label for="category">Категория <sup>*</sup></label>
                <select id="category" name="category" >
                    <option value="" disabled hidden
                        <?= $selected_category === -1 ? 'selected' : '' ?>>Выберите категорию
                    </option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['id']) ?>"
                            <?= $selected_category == ($category['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="form__error">Выберите категорию</span>
            </div>
        </div>
        <div class="form__item form__item--wide <?= in_array("message", $errors) ? 'form__item--invalid' : '' ?>">
            <label for="message">Описание <sup>*</sup></label>
            <textarea id="message" name="message" placeholder="Напишите описание лота" ><?= htmlspecialchars($_POST['message'] ?? "") ?></textarea>
            <span class="form__error">Напишите описание лота</span>
        </div>
        <div class="form__item form__item--file <?= in_array("lot-img", $errors) ? 'form__item--invalid' : '' ?>">
            <label>Изображение <sup>*</sup></label>
            <div class="form__input-file">
                <input class="visually-hidden" type="file" id="lot-img" name="lot-img" >
                <label for="lot-img">
                    Добавить
                </label>
                <span class="form__error">Прикрепите изображение</span>
            </div>
        </div>
        <div class="form__container-three">
            <div class="form__item form__item--small <?= in_array("lot-rate", $errors) ? 'form__item--invalid' : '' ?>">
                <label for="lot-rate">Начальная цена <sup>*</sup></label>
                <input id="lot-rate" type="number" name="lot-rate" placeholder="0"
                       value="<?= htmlspecialchars($_POST['lot-rate'] ?? "") ?>" >
                <span class="form__error">Введите начальную цену</span>
            </div>
            <div class="form__item form__item--small <?= in_array("lot-step", $errors) ? 'form__item--invalid' : '' ?>">
                <label for="lot-step">Шаг ставки <sup>*</sup></label>
                <input id="lot-step" type="number" name="lot-step" placeholder="0"
                       value="<?= htmlspecialchars($_POST['lot-step'] ?? "") ?>" >
                <span class="form__error">Введите шаг ставки</span>
            </div>
            <div class="form__item <?= in_array("lot-date", $errors) ? 'form__item--invalid' : '' ?>">
                <label for="lot-date">Дата окончания торгов <sup>*</sup></label>
                <input class="form__input-date" id="lot-date" type="text" name="lot-date"
                       placeholder="Введите дату в формате ГГГГ-ММ-ДД" 
                       value="<?= htmlspecialchars($_POST['lot-date'] ?? "") ?>">
                <span class="form__error">Введите дату завершения торгов</span>
            </div>
        </div>
        <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме.</span>
        <button type="submit" class="button">Добавить лот</button>
    </form>
</main>