<?php
/** @var array $errors */
?>

<main>
    <form id="login-form" class="form container <?= !empty($errors) ? "form--invalid" : '' ?>" action="/login.php"
          method="post">
        <h2>Вход</h2>
        <?php if (isset($errors["auth"])): ?>
            <div class="form__error-message"><?= htmlspecialchars($errors["auth"]) ?></div>
        <?php endif; ?>
        <div class="form__item <?= !empty($errors["email"]) ? "form__item--invalid" : '' ?>">
            <label for="email">E-mail <sup>*</sup></label>
            <input id="email" type="text" name="email" placeholder="Введите e-mail"
                   value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">
            <span class="form__error"><?= htmlspecialchars($errors["email"] ?? "") ?></span>
        </div>
        <div class="form__item form__item--last <?= !empty($errors["password"]) ? "form__item--invalid" : '' ?>">
            <label for="password">Пароль <sup>*</sup></label>
            <input id="password" type="password" name="password" placeholder="Введите пароль">
            <span class="form__error"><?= htmlspecialchars($errors["password"] ?? "") ?></span>
        </div>

        <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">

        <?php if (!empty($errors['recaptcha'])): ?>
            <div class="form__item form__item--invalid">
                <span class="form__error"><?= htmlspecialchars($errors['recaptcha']) ?></span>
            </div>
        <?php endif; ?>

        <button type="submit" class="button">Войти</button>
    </form>
</main>

<script src="https://www.google.com/recaptcha/api.js?render=<?= htmlspecialchars(RECAPTCHA_SITEKEY) ?>"></script>
<script>
    (function () {
        const form = document.querySelector('#login-form');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            grecaptcha.ready(function () {
                grecaptcha.execute('<?= htmlspecialchars(RECAPTCHA_SITEKEY) ?>', {action: 'login'})
                    .then(function (token) {
                        document.getElementById('g-recaptcha-response').value = token;
                        form.submit();
                    })
                    .catch(function (err) {
                        console.error('reCaptcha error', err);
                        alert('Ошибка проверки безопасности. Пожалуйста, попробуйте еще раз.');
                    });
            });
        }, {once: true});
    })();
</script>