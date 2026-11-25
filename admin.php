<?php 
require 'db.php';
if (!isAdmin()) { header('Location: index.php'); exit; }

// Обновление статуса заявки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_request') {
    $id = (int)$_POST['id'];
    $status = $_POST['status'] ?? 'new';
    $comment = trim($_POST['admin_comment'] ?? '');
    $stmt = getDbConnection()->prepare("UPDATE requests SET status = ?, admin_comment = ? WHERE id = ?");
    $stmt->execute([$status, $comment, $id]);
    header('Location: admin.php'); exit;
}

// Добавление экспоната
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_exhibit') {
    $name = trim($_POST['name'] ?? '');
    $volume = (int)($_POST['volume'] ?? 0);
    $neck = trim($_POST['neck'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    if ($name) {
        $stmt = getDbConnection()->prepare("INSERT INTO exhibits (name, volume_ml, neck_type, glass_color, description) VALUES (?,?,?,?,?)");
        $stmt->execute([$name, $volume, $neck, $color, $desc]);
    }
    header('Location: admin.php'); exit;
}

$requests = getDbConnection()->query("SELECT r.*, u.company_name FROM requests r JOIN users u ON u.id = r.user_id ORDER BY r.created_at DESC")->fetchAll();
$exhibits = getDbConnection()->query("SELECT * FROM exhibits ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="container nav">
        <a href="index.php" class="logo">🏭 АДМИН</a>
        <nav class="nav-links">
            <a href="index.php">На сайт</a>
            <a href="admin.php">Панель</a>
            <a href="logout.php">Выход</a>
        </nav>
    </div>
</header>

<div class="container">
    <div class="card">
        <h2>📋 Все заявки</h2>
        <table>
            <tr><th>Дата</th><th>Компания</th><th>Тип</th><th>Тема</th><th>Статус</th><th>Действие</th></tr>
            <?php foreach ($requests as $r): ?>
            <tr>
                <td><?= date('d.m.Y', strtotime($r['created_at'])) ?></td>
                <td><?= escape($r['company_name']) ?></td>
                <td><?= escape($r['type']) ?></td>
                <td><?= escape($r['subject']) ?></td>
                <td><?= escape($r['status']) ?></td>
                <td>
                    <form method="POST" style="display:flex; gap:5px;">
                        <input type="hidden" name="action" value="update_request">
                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <select name="status" style="padding:5px;">
                            <option value="new" <?= $r['status']==='new'?'selected':'' ?>>Новая</option>
                            <option value="in_progress" <?= $r['status']==='in_progress'?'selected':'' ?>>В работе</option>
                            <option value="done" <?= $r['status']==='done'?'selected':'' ?>>Выполнена</option>
                            <option value="rejected" <?= $r['status']==='rejected'?'selected':'' ?>>Отклонена</option>
                        </select>
                        <input type="text" name="admin_comment" placeholder="Комментарий" value="<?= escape($r['admin_comment'] ?? '') ?>">
                        <button type="submit" class="btn btn-success" style="padding:5px 10px;">✓</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="card">
        <h2>➕ Добавить экспонат</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add_exhibit">
            <div class="form-group"><label>Название</label><input type="text" name="name" required></div>
            <div class="form-group"><label>Объём (мл)</label><input type="number" name="volume"></div>
            <div class="form-group"><label>Тип горловины</label><input type="text" name="neck"></div>
            <div class="form-group"><label>Цвет стекла</label><input type="text" name="color"></div>
            <div class="form-group"><label>Описание</label><textarea name="description" rows="3"></textarea></div>
            <button type="submit" class="btn btn-primary">Добавить</button>
        </form>
    </div>

    <div class="card">
        <h2>📦 Текущий каталог</h2>
        <table>
            <tr><th>ID</th><th>Название</th><th>Объём</th><th>Горловина</th><th>Статус</th></tr>
            <?php foreach ($exhibits as $ex): ?>
            <tr>
                <td><?= $ex['id'] ?></td>
                <td><?= escape($ex['name']) ?></td>
                <td><?= $ex['volume_ml'] ?> мл</td>
                <td><?= escape($ex['neck_type']) ?></td>
                <td><?= $ex['status'] === 'available' ? '✅' : '❌' ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
</body>
</html>