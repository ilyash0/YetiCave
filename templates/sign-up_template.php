<?php
/** @var array $errors */
?>

<?php
$fields = ['password'];

$field_errors = group_errors_by_field($errors, $fields);
?>

<main>
    <form id="signup-form" class="form container <?= !empty($errors) ? "form--invalid" : '' ?>" action="/sign-up.php"
          method="post"
          autocomplete="off">
        <h2>Регистрация нового аккаунта</h2>

        <div class="form__item <?= !empty($errors['email']) ? "form__item--invalid" : '' ?>">
            <label for="email">E-mail <sup>*</sup></label>
            <input id="email" type="email" name="email" placeholder="Введите e-mail"
                   value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">
            <span class="form__error"><?= htmlspecialchars($errors["email"] ?? "") ?></span>
        </div>

        <div class="form__item <?= !empty($field_errors['password']) ? "form__item--invalid" : '' ?>">
            <label for="password">Пароль <sup>*</sup></label>
            <input id="password" type="password" name="password" placeholder="Введите пароль"
                   value="<?= htmlspecialchars($_POST["password"] ?? "") ?>">
            <?php foreach ($field_errors['password'] as $error): ?>
                <span class="form__error"><?= htmlspecialchars($error) ?></span>
            <?php endforeach; ?>
        </div>

        <div class="form__item <?= !empty($errors['name']) ? "form__item--invalid" : '' ?>">
            <label for="name">Имя <sup>*</sup></label>
            <input id="name" type="text" name="name" placeholder="Введите имя"
                   value="<?= htmlspecialchars($_POST["name"] ?? "") ?>">
            <span class="form__error"><?= htmlspecialchars($errors["name"] ?? "") ?></span>
        </div>

        <div class="form__item <?= !empty($errors['message']) ? "form__item--invalid" : '' ?>">
            <label for="message">Контактные данные <sup>*</sup></label>
            <textarea id="message" name="message"
                      placeholder="Напишите как с вами связаться"><?= htmlspecialchars($_POST["message"] ?? "") ?></textarea>
            <span class="form__error"><?= htmlspecialchars($errors["message"] ?? "") ?></span>
        </div>

        <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">

        <?php if (!empty($errors['recaptcha'])): ?>
            <div class="form__item form__item--invalid">
                <span class="form__error"><?= htmlspecialchars($errors['recaptcha']) ?></span>
            </div>
        <?php endif; ?>

        <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме.</span>
        <button id="signup-button" type="submit" class="button">Зарегистрироваться</button>
        <a class="text-link" href="/login.php">Уже есть аккаунт</a>
    </form>
</main>

<script src="https://www.google.com/recaptcha/api.js?render=<?= htmlspecialchars(RECAPTCHA_SITEKEY) ?>"></script>
<script>
    (function () {
        const form = document.querySelector('#signup-form');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            grecaptcha.ready(function () {
                grecaptcha.execute('<?= htmlspecialchars(RECAPTCHA_SITEKEY) ?>', {action: 'signup'})
                    .then(function (token) {
                        document.getElementById('g-recaptcha-response').value = token;
                        form.submit();
                    })
                    .catch(function (err) {
                        console.error('reCaptcha error', err);
                        form.submit();
                    });
            });
        }, {once: true});
    })();
</script>
