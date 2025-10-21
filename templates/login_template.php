<?php
/** @var array $categories */
/** @var array $errors */
?>

<main>
    <nav class="nav">
        <ul class="nav__list container">
            <?php foreach ($categories as $category): ?>
                <li class="nav__item">
                    <a href="all-lots.html"><?= htmlspecialchars($category['name']) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <form class="form container <?= !empty($errors) ? 'form--invalid' : '' ?>" action="/login.php" method="post"> <!-- form--invalid -->
        <h2>Вход</h2>
        <div class="form__item <?= in_array("email", $errors) ? 'form__item--invalid' : '' ?>"> <!-- form__item--invalid -->
            <label for="email">E-mail <sup>*</sup></label>
            <input id="email" type="text" name="email" placeholder="Введите e-mail">
            <span class="form__error">Введите e-mail</span>
        </div>
        <div class="form__item form__item--last <?= in_array("password", $errors) ? 'form__item--invalid' : '' ?>">
            <label for="password">Пароль <sup>*</sup></label>
            <input id="password" type="password" name="password" placeholder="Введите пароль">
            <span class="form__error">Введите пароль</span>
        </div>
        <?php if (in_array("auth", $errors)): ?>
            <div class="form__error-message">Вы ввели неверный email/пароль</div>
        <?php endif; ?>
        <button type="submit" class="button">Войти</button>
    </form>
</main>