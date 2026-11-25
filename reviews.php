<?php 
require 'db.php';

// Обработка отзыва
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_review') {
    if (!isLoggedIn()) {
        $_SESSION['flash'] = 'Для отзыва необходима авторизация.';
        $_SESSION['flash_type'] = 'error';
        header('Location: login.php'); exit;
    }
    $rating  = (int)($_POST['rating'] ?? 0);
    $message = trim($_POST['message'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $_SESSION['flash'] = 'Оценка от 1 до 5.';
        $_SESSION['flash_type'] = 'error';
    } elseif ($message === '') {
        $_SESSION['flash'] = 'Введите текст отзыва.';
        $_SESSION['flash_type'] = 'error';
    } else {
        $stmt = getDbConnection()->prepare("
            INSERT INTO reviews (user_id, rating, message, status) VALUES (?, ?, ?, 'published')
        ");
        $stmt->execute([$_SESSION['user']['id'], $rating, $message]);
        $_SESSION['flash'] = 'Спасибо за отзыв!';
        $_SESSION['flash_type'] = 'success';
    }
    header('Location: reviews.php'); exit;
}

$flash = $_SESSION['flash'] ?? '';
$flashType = $_SESSION['flash_type'] ?? '';
unset($_SESSION['flash'], $_SESSION['flash_type']);

// Выборка отзывов
$stmt = getDbConnection()->query("
    SELECT r.*, u.company_name 
    FROM reviews r 
    JOIN users u ON u.id = r.user_id 
    WHERE r.status = 'published' 
    ORDER BY r.created_at DESC
");
$reviewsList = $stmt->fetchAll();
$totalReviews = count($reviewsList);
$sumRatings = 0;
foreach ($reviewsList as $rev) $sumRatings += (int)$rev['rating'];
$averageRating = $totalReviews ? round($sumRatings / $totalReviews, 1) : 0;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отзывы</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="container nav">
        <a href="index.php" class="logo">🏭 СТЕКЛОТАРА</a>
        <nav class="nav-links">
            <a href="index.php">Выставка</a>
            <a href="reviews.php">Отзывы</a>
            <?php if (isLoggedIn()): ?>
                <a href="profile.php">Личный кабинет</a>
                <a href="logout.php">Выход</a>
            <?php else: ?>
                <a href="login.php">Вход</a>
                <a href="registration.php">Регистрация</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<div class="container">
    <div class="card" style="text-align:center;">
        <h2>⭐ Рейтинг выставки</h2>
        <div style="font-size:48px; margin:15px 0;"><?= $averageRating ?> / 5</div>
        <p>На основе <?= $totalReviews ?> отзывов</p>
    </div>

    <?php if ($flash): ?>
        <div class="flash <?= $flashType ?>"><?= escape($flash) ?></div>
    <?php endif; ?>

    <?php if (isLoggedIn()): ?>
    <div class="card">
        <h2>✍️ Оставить отзыв</h2>
        <form method="POST">
            <input type="hidden" name="action" value="submit_review">
            <div class="form-group">
                <label>Оценка</label>
                <div class="stars">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>">
                        <label for="star<?= $i ?>">★</label>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="form-group">
                <label>Текст отзыва</label>
                <textarea name="message" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Отправить</button>
        </form>
    </div>
    <?php else: ?>
        <div class="card">
            <p>Чтобы оставить отзыв, <a href="login.php">войдите в систему</a>.</p>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>💬 Все отзывы</h2>
        <?php if (empty($reviewsList)): ?>
            <p>Пока нет отзывов.</p>
        <?php else: foreach ($reviewsList as $rev): ?>
            <div style="border-bottom:1px solid rgba(255,255,255,0.2); padding:15px 0;">
                <strong><?= escape($rev['company_name']) ?></strong>
                <span style="color:#f1c40f;">
                    <?= str_repeat('★', $rev['rating']) . str_repeat('☆', 5 - $rev['rating']) ?>
                </span>
                <span style="opacity:0.7; font-size:13px;"><?= date('d.m.Y', strtotime($rev['created_at'])) ?></span>
                <p style="margin-top:8px;"><?= nl2br(escape($rev['message'])) ?></p>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>