<?php 
require 'db.php';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $pass  = $_POST['password'] ?? '';
    
    $stmt = getDbConnection()->prepare("SELECT * FROM users WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['user'] = $user;
        header('Location: ' . ($user['role'] === 'admin' ? 'admin.php' : 'profile.php'));
        exit;
    } else {
        $errorMsg = 'Неверный логин или пароль.';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container" style="max-width:400px; margin-top:80px;">
    <div class="card">
        <h2>Вход в систему</h2>
        <?php if ($errorMsg): ?><div class="flash error"><?= $errorMsg ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Логин</label>
                <input type="text" name="login" required>
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Войти</button>
            <a href="registration.php" style="margin-left:15px;">Регистрация</a>
        </form>
    </div>
</div>
</body>
</html>