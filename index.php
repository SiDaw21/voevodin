<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Виртуальная выставка — Каменск-Шахтинский стеклотарный завод</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Hero-баннер на главной */
        .hero-banner {
            position: relative;
            width: 100%;
            height: 480px;
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 30px;
            background: rgba(0,0,0,0.2);
        }
        .hero-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.6s ease;
        }
        .hero-banner:hover img {
            transform: scale(1.04);
        }
        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(30,60,114,0.85) 0%, rgba(42,82,152,0.5) 50%, rgba(76,161,175,0.3) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
        }
        .hero-overlay h1 {
            font-size: 46px;
            margin-bottom: 15px;
            text-shadow: 2px 2px 10px rgba(0,0,0,0.5);
            color: #fff;
        }
        .hero-overlay p {
            font-size: 20px;
            opacity: 0.95;
            margin-bottom: 25px;
            max-width: 700px;
            color: #fff;
        }
        .hero-overlay .btn {
            font-size: 16px;
            padding: 14px 35px;
        }

        /* Баннер в секции "О выставке" */
        .banner {
            margin-top: 15px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.2);
            line-height: 0;
        }
        .banner img {
            width: 100%;
            height: 320px;
            object-fit: cover;
            display: block;
            transition: transform 0.5s;
        }
        .banner:hover img { transform: scale(1.03); }

        /* Изображение в карточке товара */
        .exhibit-image {
            width: 100%;
            height: 220px;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 15px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.15);
            line-height: 0;
        }
        .exhibit-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.4s ease;
        }
        .exhibit-card:hover .exhibit-image img {
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .hero-banner { height: 280px; }
            .hero-overlay h1 { font-size: 26px; }
            .hero-overlay p { font-size: 15px; }
            .banner img { height: 180px; }
            .exhibit-image { height: 160px; }
        }
    </style>
</head>
<body>
<header>
    <div class="container nav">
        <a href="index.php" class="logo"> ОАО «Каменский стеклотарный завод»</a>
        
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

    <!-- HERO-БАННЕР С ПАНОРАМОЙ ЦЕХА -->
    <section class="hero-banner">
        <img src="images/XXL_height.jpg" alt="Производственный цех Каменск-Шахтинского стеклотарного завода">
        <div class="hero-overlay">
            <h1>Виртуальная выставка стеклотары</h1>
            <p>Каменск-Шахтинский стеклотарный завод — качество, проверенное десятилетиями</p>
            <a href="catalog.php" class="btn btn-primary">Перейти в каталог</a>
        </div>
    </section>

    <!-- О ВЫСТАВКЕ -->
    <section class="card">
        <h2>🎯 О выставке</h2>

        <div class="banner">
            <img src="images/iii.jpg" alt="Продукция Каменск-Шахтинского стеклотарного завода">
        </div>

        <p style="margin-top:15px;">
            Добро пожаловать на виртуальную выставку нашего предприятия. Здесь вы можете ознакомиться 
            с полным ассортиментом стеклянной тары, производимой на нашем заводе: бутылки для напитков, 
            банки для консервации, флаконы для косметической продукции. Все изделия соответствуют 
            ГОСТ и изготавливаются из экологически чистого сырья.
        </p>
    </section>

    <!-- КАТАЛОГ ПРОДУКЦИИ -->
    <section>
        <h2 style="margin-bottom:20px;">📦 Каталог продукции</h2>
        <div class="exhibits-grid">
            <?php
            $stmt = getDbConnection()->query("SELECT * FROM exhibits ORDER BY created_at DESC");
            $exhibits = $stmt->fetchAll();
            if (empty($exhibits)): ?>
                <p>Каталог временно пуст.</p>
            <?php else: foreach ($exhibits as $ex): ?>
                <div class="exhibit-card">
                    <!-- ИЗОБРАЖЕНИЕ ТОВАРА -->
                    <div class="exhibit-image">
                        <?php if (!empty($ex['image_url'])): ?>
                            <img src="<?= escape($ex['image_url']) ?>" alt="<?= escape($ex['name']) ?>">
                        <?php else: ?>
                            <img src="images/bottle-05.jpg" alt="Нет изображения">
                                 <img src="images/jar-08.jpg" alt="Нет изображения">
                                      <img src="images/bottle-05.jpg" alt="Нет изображения">
                        <?php endif; ?>
                    </div>

                    <h3><?= escape($ex['name']) ?></h3>
                    <div class="specs">
                        <p>📏 Объём: <strong><?= $ex['volume_ml'] ?> мл</strong></p>
                        <p>🔩 Горловина: <strong><?= escape($ex['neck_type']) ?></strong></p>
                        <p>🎨 Цвет: <strong><?= escape($ex['glass_color']) ?></strong></p>
                        <p>📦 Статус: <strong><?= $ex['status'] === 'available' ? '✅ В наличии' : '❌ Нет в наличии' ?></strong></p>
                    </div>
                    <p style="font-size:14px; opacity:0.85;"><?= escape($ex['description']) ?></p>
                    <?php if (isLoggedIn() && $ex['status'] === 'available'): ?>
                        <a href="profile.php?exhibit_id=<?= $ex['id'] ?>" class="btn btn-primary" style="margin-top:10px;">Заказать</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </section>

</div>

<script src="script.js"></script>
</body>
</html>