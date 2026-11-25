<?php 
require 'db.php';

// Обработка добавления в "корзину" (сессию)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_to_cart') {
    $exhibitId = (int)($_POST['exhibit_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    if ($exhibitId > 0 && $quantity > 0) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Проверяем, есть ли уже этот товар
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] === $exhibitId) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $_SESSION['cart'][] = ['id' => $exhibitId, 'quantity' => $quantity];
        }
        
        $_SESSION['flash'] = 'Продукция добавлена в заявку!';
        $_SESSION['flash_type'] = 'success';
    }
    header('Location: catalog.php'); exit;
}

// Удаление из корзины
if (isset($_GET['remove'])) {
    $removeId = (int)$_GET['remove'];
    if (isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($removeId) {
            return $item['id'] !== $removeId;
        });
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
    header('Location: catalog.php'); exit;
}

// Очистка корзины
if (isset($_GET['clear_cart'])) {
    unset($_SESSION['cart']);
    header('Location: catalog.php'); exit;
}

// Отправка заявки на выбранное
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_cart_request') {
    if (!isLoggedIn()) {
        $_SESSION['flash'] = 'Для оформления заявки необходимо войти в систему.';
        $_SESSION['flash_type'] = 'error';
        header('Location: login.php'); exit;
    }
    
    if (empty($_SESSION['cart'])) {
        $_SESSION['flash'] = 'Корзина пуста!';
        $_SESSION['flash_type'] = 'error';
        header('Location: catalog.php'); exit;
    }
    
    $user = $_SESSION['user'];
    $reqType = $_POST['request_type'] ?? 'bulk_order';
    
    // Формируем текст заявки
    $message = "Заявка на следующую продукцию:\n\n";
    $stmt = getDbConnection()->prepare("SELECT id, name, volume_ml FROM exhibits WHERE id = ?");
    foreach ($_SESSION['cart'] as $item) {
        $stmt->execute([$item['id']]);
        $ex = $stmt->fetch();
        if ($ex) {
            $message .= "• {$ex['name']} ({$ex['volume_ml']} мл) — {$item['quantity']} шт.\n";
        }
    }
    
    $stmt = getDbConnection()->prepare("
        INSERT INTO requests (user_id, type, subject, message, phone, status)
        VALUES (?, ?, ?, ?, ?, 'new')
    ");
    $stmt->execute([
        $user['id'],
        $reqType,
        'Заявка из каталога',
        $message,
        $user['phone']
    ]);
    
    unset($_SESSION['cart']);
    $_SESSION['flash'] = 'Заявка успешно отправлена! Менеджер свяжется с вами.';
    $_SESSION['flash_type'] = 'success';
    header('Location: profile.php'); exit;
}

$flash = $_SESSION['flash'] ?? '';
$flashType = $_SESSION['flash_type'] ?? '';
unset($_SESSION['flash'], $_SESSION['flash_type']);

// Фильтры
$filterVolume = $_GET['volume'] ?? '';
$filterNeck = $_GET['neck'] ?? '';
$filterColor = $_GET['color'] ?? '';
$search = trim($_GET['search'] ?? '');

// Построение SQL-запроса с фильтрами
$sql = "SELECT * FROM exhibits WHERE 1=1";
$params = [];

if ($filterVolume) {
    $sql .= " AND volume_ml = ?";
    $params[] = (int)$filterVolume;
}
if ($filterNeck) {
    $sql .= " AND neck_type = ?";
    $params[] = $filterNeck;
}
if ($filterColor) {
    $sql .= " AND glass_color = ?";
    $params[] = $filterColor;
}
if ($search) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY created_at DESC";

$stmt = getDbConnection()->prepare($sql);
$stmt->execute($params);
$exhibits = $stmt->fetchAll();

// Получение уникальных значений для фильтров
$volumes = getDbConnection()->query("SELECT DISTINCT volume_ml FROM exhibits ORDER BY volume_ml")->fetchAll(PDO::FETCH_COLUMN);
$necks = getDbConnection()->query("SELECT DISTINCT neck_type FROM exhibits ORDER BY neck_type")->fetchAll(PDO::FETCH_COLUMN);
$colors = getDbConnection()->query("SELECT DISTINCT glass_color FROM exhibits ORDER BY glass_color")->fetchAll(PDO::FETCH_COLUMN);

// Подсчёт товаров в корзине
$cartCount = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог продукции — Стеклотарный завод</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .catalog-layout {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        .filters {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 15px;
            padding: 20px;
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        .filters h3 {
            margin-bottom: 15px;
            font-size: 18px;
        }
        .filter-group {
            margin-bottom: 20px;
        }
        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
        }
        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 6px;
            background: rgba(255,255,255,0.1);
            color: #fff;
            font-size: 13px;
        }
        .filter-group select option {
            background: #2a5298;
        }
        .btn-filter {
            width: 100%;
            padding: 10px;
            background: linear-gradient(135deg, #4ca1af, #2a5298);
            border: none;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            font-weight: 500;
            margin-top: 10px;
        }
        .btn-reset {
            width: 100%;
            padding: 10px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            margin-top: 5px;
        }
        
        .cart-widget {
            background: rgba(39,174,96,0.2);
            border: 2px solid #27ae60;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            position: sticky;
            top: 100px;
        }
        .cart-widget h3 {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cart-count {
            background: #27ae60;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
        }
        .cart-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 15px;
        }
        .cart-item {
            background: rgba(255,255,255,0.1);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
        }
        .cart-item-info {
            flex: 1;
        }
        .cart-item-remove {
            color: #e74c3c;
            text-decoration: none;
            font-size: 18px;
            padding: 0 5px;
        }
        .cart-item-remove:hover {
            color: #c0392b;
        }
        
        .exhibit-card {
            position: relative;
        }
        .quantity-input {
            width: 80px;
            padding: 8px;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 6px;
            background: rgba(255,255,255,0.1);
            color: #fff;
            margin-right: 10px;
        }
        .btn-add {
            padding: 8px 15px;
            background: #27ae60;
            border: none;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            font-size: 13px;
        }
        .btn-add:hover {
            background: #229954;
        }
        
        @media (max-width: 768px) {
            .catalog-layout {
                grid-template-columns: 1fr;
            }
            .filters {
                position: static;
            }
        }
    </style>
</head>
<body>
<header>
    <div class="container nav">
        <a href="index.php" class="logo">🏭 СТЕКЛОТАРА</a>
        <nav class="nav-links">
            <a href="index.php">Главная</a>
            <a href="catalog.php">Каталог</a>
            <a href="reviews.php">Отзывы</a>
            <?php if (isLoggedIn()): ?>
                <a href="profile.php">Личный кабинет</a>
                <?php if (isAdmin()): ?>
                    <a href="admin.php">Админ-панель</a>
                <?php endif; ?>
                <a href="logout.php">Выход (<?= escape($_SESSION['user']['login']) ?>)</a>
            <?php else: ?>
                <a href="login.php">Вход</a>
                <a href="registration.php">Регистрация</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<div class="container">
    <?php if ($flash): ?>
        <div class="flash <?= $flashType ?>"><?= escape($flash) ?></div>
    <?php endif; ?>

    <h1 style="text-align:center; margin: 30px 0;">📦 Каталог продукции</h1>

    <div class="catalog-layout">
        <!-- Фильтры -->
        <aside class="filters">
            <h3>🔍 Фильтры</h3>
            <form method="GET">
                <div class="filter-group">
                    <label>Поиск по названию</label>
                    <input type="text" name="search" value="<?= escape($search) ?>" placeholder="Введите название...">
                </div>
                
                <div class="filter-group">
                    <label>Объём (мл)</label>
                    <select name="volume">
                        <option value="">Все</option>
                        <?php foreach ($volumes as $vol): ?>
                            <option value="<?= $vol ?>" <?= $filterVolume == $vol ? 'selected' : '' ?>><?= $vol ?> мл</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Тип горловины</label>
                    <select name="neck">
                        <option value="">Все</option>
                        <?php foreach ($necks as $neck): ?>
                            <option value="<?= escape($neck) ?>" <?= $filterNeck == $neck ? 'selected' : '' ?>><?= escape($neck) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Цвет стекла</label>
                    <select name="color">
                        <option value="">Все</option>
                        <?php foreach ($colors as $color): ?>
                            <option value="<?= escape($color) ?>" <?= $filterColor == $color ? 'selected' : '' ?>><?= escape($color) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn-filter">Применить фильтры</button>
                <a href="catalog.php" class="btn-reset" style="display:block; text-align:center; text-decoration:none;">Сбросить</a>
            </form>
        </aside>

        <!-- Основная область -->
        <div>
            <!-- Виджет корзины -->
            <?php if (!empty($_SESSION['cart'])): ?>
            <div class="cart-widget">
                <h3>
                    🛒 Ваша заявка
                    <span class="cart-count"><?= $cartCount ?> шт.</span>
                </h3>
                <div class="cart-items">
                    <?php 
                    $stmt = getDbConnection()->prepare("SELECT * FROM exhibits WHERE id = ?");
                    foreach ($_SESSION['cart'] as $item):
                        $stmt->execute([$item['id']]);
                        $ex = $stmt->fetch();
                        if ($ex):
                    ?>
                        <div class="cart-item">
                            <div class="cart-item-info">
                                <strong><?= escape($ex['name']) ?></strong><br>
                                <small><?= $item['quantity'] ?> шт.</small>
                            </div>
                            <a href="catalog.php?remove=<?= $item['id'] ?>" class="cart-item-remove">×</a>
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
                
                <?php if (isLoggedIn()): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="submit_cart_request">
                    <div class="form-group">
                        <label>Тип заявки</label>
                        <select name="request_type">
                            <option value="sample_order">Заказ образцов</option>
                            <option value="bulk_order">Оптовый заказ</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success" style="width:100%;">Отправить заявку</button>
                </form>
                <?php else: ?>
                <a href="login.php" class="btn btn-primary" style="width:100%; text-align:center;">Войти для оформления</a>
                <?php endif; ?>
                
                <a href="catalog.php?clear_cart=1" style="display:block; text-align:center; margin-top:10px; color:#e74c3c; text-decoration:none;">Очистить корзину</a>
            </div>
            <?php endif; ?>

            <!-- Каталог -->
            <?php if (empty($exhibits)): ?>
                <div class="card">
                    <p style="text-align:center;">По вашему запросу ничего не найдено.</p>
                </div>
            <?php else: ?>
                <div class="exhibits-grid">
                    <?php foreach ($exhibits as $ex): ?>
                        <div class="exhibit-card">
                            <h3><?= escape($ex['name']) ?></h3>
                            <div class="specs">
                                <p>📏 Объём: <strong><?= $ex['volume_ml'] ?> мл</strong></p>
                                <p>🔩 Горловина: <strong><?= escape($ex['neck_type']) ?></strong></p>
                                <p>🎨 Цвет: <strong><?= escape($ex['glass_color']) ?></strong></p>
                                <p>📦 Статус: <strong><?= $ex['status'] === 'available' ? '✅ В наличии' : '❌ Нет в наличии' ?></strong></p>
                            </div>
                            <p style="font-size:14px; opacity:0.85; margin: 10px 0;"><?= escape($ex['description']) ?></p>
                            
                            <?php if ($ex['status'] === 'available'): ?>
                                <form method="POST" style="display:flex; align-items:center; margin-top:15px;">
                                    <input type="hidden" name="action" value="add_to_cart">
                                    <input type="hidden" name="exhibit_id" value="<?= $ex['id'] ?>">
                                    <input type="number" name="quantity" value="1" min="1" class="quantity-input">
                                    <button type="submit" class="btn-add">+ В заявку</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>