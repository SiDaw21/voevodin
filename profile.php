<?php 
require 'db.php';
if (!isLoggedIn()) { header('Location: login.php'); exit; }
$user = $_SESSION['user'];

// Обработка заявки на продукцию
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_product_request') {
    $reqType  = in_array($_POST['type'] ?? '', ['sample_order', 'bulk_order'], true) ? $_POST['type'] : 'sample_order';
    $subject  = trim($_POST['subject'] ?? ''); 
    $quantity = (int)($_POST['quantity'] ?? 0);
    $message  = trim($_POST['message'] ?? ''); 
    $phone    = trim($_POST['phone'] ?? $user['phone']);

    if ($subject === '' || $quantity <= 0) {
        $_SESSION['flash'] = 'Укажите наименование и корректное количество.';
        $_SESSION['flash_type'] = 'error';
    } else {
        $stmt = getDbConnection()->prepare("
            INSERT INTO requests (user_id, type, subject, message, phone, status)
            VALUES (?, ?, ?, ?, ?, 'new')
        ");
        $stmt->execute([$user['id'], $reqType, $subject, $message . " (Кол-во: $quantity)", $phone]);
        $_SESSION['flash'] = 'Заявка на продукцию отправлена!';
        $_SESSION['flash_type'] = 'success';
    }
    header('Location: profile.php'); exit;
}

// Обработка общего обращения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_general_request') {
    $type    = in_array($_POST['type'] ?? '', ['consultation', 'partnership', 'complaint'], true) ? $_POST['type'] : 'consultation';
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $phone   = trim($_POST['phone'] ?? $user['phone']);

    if ($subject === '' || $message === '') {
        $_SESSION['flash'] = 'Заполните тему и текст обращения.';
        $_SESSION['flash_type'] = 'error';
    } else {
        $stmt = getDbConnection()->prepare("
            INSERT INTO requests (user_id, type, subject, message, phone, status)
            VALUES (?, ?, ?, ?, ?, 'new')
        ");
        $stmt->execute([$user['id'], $type, $subject, $message, $phone]);
        $_SESSION['flash'] = 'Обращение зарегистрировано!';
        $_SESSION['flash_type'] = 'success';
    }
    header('Location: profile.php'); exit;
}

$flash = $_SESSION['flash'] ?? '';
$flashType = $_SESSION['flash_type'] ?? '';
unset($_SESSION['flash'], $_SESSION['flash_type']);

// История заявок
$stmt = getDbConnection()->prepare("SELECT * FROM requests WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$requests = $stmt->fetchAll();

// Предзаполнение из каталога
$preSubject = '';
if (isset($_GET['exhibit_id'])) {
    $stmt = getDbConnection()->prepare("SELECT name FROM exhibits WHERE id = ?");
    $stmt->execute([(int)$_GET['exhibit_id']]);
    $ex = $stmt->fetch();
    if ($ex) $preSubject = $ex['name'];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="container nav">
        <a href="index.php" class="logo">🏭 СТЕКЛОТАРА</a>
        <nav class="nav-links">
            <a href="index.php">Выставка</a>
            <a href="reviews.php">Отзывы</a>
            <a href="profile.php">Личный кабинет</a>
            <a href="logout.php">Выход (<?= escape($user['login']) ?>)</a>
        </nav>
    </div>
</header>

<div class="container">
    <div class="card">
        <h2>👤 Личный кабинет</h2>
        <p><strong>Компания:</strong> <?= escape($user['company_name']) ?></p>
        <p><strong>Контактное лицо:</strong> <?= escape($user['contact_person']) ?></p>
        <p><strong>Email:</strong> <?= escape($user['email']) ?></p>
        <p><strong>Телефон:</strong> <?= escape($user['phone']) ?></p>
    </div>

    <?php if ($flash): ?>
        <div class="flash <?= $flashType ?>"><?= escape($flash) ?></div>
    <?php endif; ?>

    <div class="card">
        <h2>📦 Заявка на продукцию</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create_product_request">
            <div class="form-group">
                <label>Тип заявки</label>
                <select name="type">
                    <option value="sample_order">Заказ образцов</option>
                    <option value="bulk_order">Оптовый заказ</option>
                </select>
            </div>
            <div class="form-group">
                <label>Наименование продукции *</label>
                <input type="text" name="subject" value="<?= escape($preSubject) ?>" required>
            </div>
            <div class="form-group">
                <label>Количество (шт.) *</label>
                <input type="number" name="quantity" min="1" required>
            </div>
            <div class="form-group">
                <label>Дополнительные пожелания</label>
                <textarea name="message" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Телефон</label>
                <input type="tel" name="phone" value="<?= escape($user['phone']) ?>">
            </div>
            <button type="submit" class="btn btn-primary">Отправить заявку</button>
        </form>
    </div>

    <div class="card">
        <h2>💬 Общее обращение</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create_general_request">
            <div class="form-group">
                <label>Тип обращения</label>
                <select name="type">
                    <option value="consultation">Консультация</option>
                    <option value="partnership">Предложение о сотрудничестве</option>
                    <option value="complaint">Жалоба</option>
                </select>
            </div>
            <div class="form-group">
                <label>Тема *</label>
                <input type="text" name="subject" required>
            </div>
            <div class="form-group">
                <label>Текст *</label>
                <textarea name="message" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label>Телефон</label>
                <input type="tel" name="phone" value="<?= escape($user['phone']) ?>">
            </div>
            <button type="submit" class="btn btn-primary">Отправить обращение</button>
        </form>
    </div>

    <div class="card">
        <h2>📋 История моих заявок</h2>
        <?php if (empty($requests)): ?>
            <p>У вас пока нет заявок.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Дата</th>
                    <th>Тип</th>
                    <th>Тема</th>
                    <th>Статус</th>
                    <th>Комментарий</th>
                </tr>
                <?php foreach ($requests as $r): ?>
                    <tr>
                        <td><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></td>
                        <td><?= escape($r['type']) ?></td>
                        <td><?= escape($r['subject']) ?></td>
                        <td>
                            <?php
                            $statusLabels = ['new'=>'🆕 Новая', 'in_progress'=>'⏳ В работе', 'done'=>'✅ Выполнена', 'rejected'=>'❌ Отклонена'];
                            echo $statusLabels[$r['status']] ?? $r['status'];
                            ?>
                        </td>
                        <td><?= escape($r['admin_comment'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>