<?php
/** @var array $errors */
?>

<main>
    <form class="form container <?= !empty($errors) ? "form--invalid" : '' ?>" action="/sign-up.php" method="post" autocomplete="off"> <!-- form--invalid -->
        <h2>Регистрация нового аккаунта</h2>
        <div class="form__item <?= in_array("email", $errors) ? "form__item--invalid" : '' ?>"> <!-- form__item--invalid -->
            <label for="email">E-mail <sup>*</sup></label>
            <input id="email" type="email" name="email" placeholder="Введите e-mail"
                   value="<?= htmlspecialchars($_POST["email"] ?? "") ?>" >
            <span class="form__error">Введите e-mail</span>
        </div>
        <div class="form__item <?= in_array("password", $errors) ? "form__item--invalid" : '' ?>">
            <label for="password">Пароль <sup>*</sup></label>
            <input id="password" type="password" name="password" placeholder="Введите пароль"
                   value="<?= htmlspecialchars($_POST["password"] ?? "") ?>" >
            <span class="form__error">Введите пароль. Не менее 8 символов</span>
        </div>
        <div class="form__item <?= in_array("name", $errors) ? "form__item--invalid" : '' ?>">
            <label for="name">Имя <sup>*</sup></label>
            <input id="name" type="text" name="name" placeholder="Введите имя"
                   value="<?= htmlspecialchars($_POST["name"] ?? "") ?>" >
            <span class="form__error">Введите имя</span>
        </div>
        <div class="form__item <?= in_array("message", $errors) ? "form__item--invalid" : '' ?>">
            <label for="message">Контактные данные <sup>*</sup></label>
            <textarea id="message" name="message" placeholder="Напишите как с вами связаться"><?= htmlspecialchars($_POST["message"] ?? "") ?></textarea>
            <span class="form__error">Напишите как с вами связаться</span>
        </div>
        <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме.</span>
        <button type="submit" class="button">Зарегистрироваться</button>
        <a class="text-link" href="/login.php">Уже есть аккаунт</a>
    </form>
</main>
