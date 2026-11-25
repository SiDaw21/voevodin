<?php 
require 'db.php';
$errorMsg = '';
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $company  = trim($_POST['company'] ?? '');
    $contact  = trim($_POST['contact'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $pass     = $_POST['password'] ?? '';
    $passConf = $_POST['password_confirm'] ?? '';

    if ($login === '' || $company === '' || $pass === '') {
        $errorMsg = 'Заполните логин, название компании и пароль.';
    } elseif (mb_strlen($login) < 3) {
        $errorMsg = 'Логин должен быть не короче 3 символов.';
    } elseif (strlen($pass) < 6) {
        $errorMsg = 'Пароль должен содержать не менее 6 символов.';
    } elseif ($pass !== $passConf) {
        $errorMsg = 'Пароли не совпадают.';
    } else {
        $stmt = getDbConnection()->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $errorMsg = 'Такой логин уже занят.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = getDbConnection()->prepare("
                INSERT INTO users (login, password, company_name, contact_person, phone, email, role)
                VALUES (?, ?, ?, ?, ?, ?, 'client')
            ");
            $stmt->execute([$login, $hash, $company, $contact, $phone, $email]);
            $successMsg = 'Регистрация прошла успешно! <a href="login.php">Войти</a>.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container" style="max-width:500px; margin-top:50px;">
    <div class="card">
        <h2>Регистрация контрагента</h2>
        <?php if ($errorMsg): ?><div class="flash error"><?= $errorMsg ?></div><?php endif; ?>
        <?php if ($successMsg): ?><div class="flash success"><?= $successMsg ?></div><?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Логин *</label>
                <input type="text" name="login" required>
            </div>
            <div class="form-group">
                <label>Название компании *</label>
                <input type="text" name="company" required>
            </div>
            <div class="form-group">
                <label>Контактное лицо</label>
                <input type="text" name="contact">
            </div>
            <div class="form-group">
                <label>Телефон</label>
                <input type="tel" name="phone">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email">
            </div>
            <div class="form-group">
                <label>Пароль *</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Подтверждение пароля *</label>
                <input type="password" name="password_confirm" required>
            </div>
            <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
            <a href="login.php" style="margin-left:15px;">Уже есть аккаунт?</a>
        </form>
    </div>
</div>
</body>
</html>