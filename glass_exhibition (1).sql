-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: MySQL-8.2
-- Время создания: Ноя 17 2026 г., 15:45
-- Версия сервера: 8.2.0
-- Версия PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `glass_exhibition`
--

-- --------------------------------------------------------

--
-- Структура таблицы `exhibits`
--

CREATE TABLE `exhibits` (
  `id` int NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `volume_ml` int DEFAULT NULL,
  `neck_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `glass_color` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('available','out_of_stock') COLLATE utf8mb4_unicode_ci DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `exhibits`
--

INSERT INTO `exhibits` (`id`, `name`, `volume_ml`, `neck_type`, `glass_color`, `description`, `image_url`, `status`, `created_at`) VALUES
(1, 'Бутылка \"Классика\" 0,5л', 500, 'ТО-43', 'Бесцветное', 'Универсальная бутылка для питьевой воды и соков. Изготовлена из бесцветного натрий-кальций-силикатного стекла. Соответствует ГОСТ 10117.2-2004.', 'images/bottle-05.jpg', 'available', '2026-11-17 12:38:03'),
(2, 'Флакон парфюмерный 50мл', 50, 'PP-20', 'Матовое', 'Элегантный матовый флакон для парфюмерно-косметической продукции. Идеален для духов, лосьонов и тоников.', 'images/flacon-50.jpg', 'available', '2026-11-17 12:38:03'),
(3, 'Банка для мёда 0,7л', 700, '400/335', 'Бесцветное', 'Широкогорлая банка для мёда, джемов и сгущённого молока. Удобна для фасовки густых продуктов.', 'images/jar-honey.jpg', 'available', '2026-11-17 12:38:03'),
(4, 'Банка консервная 0,8л', 800, 'СКО-82', 'Бесцветное', 'Классическая банка для домашней и промышленной консервации. Укупоривается металлической крышкой.', 'images/jar-08.jpg', 'available', '2026-11-17 12:38:03'),
(5, 'Банка с деревянной крышкой 1л', 1000, 'Твист-офф', 'Бесцветное', 'Декоративная банка с герметичной деревянной крышкой. Подходит для сыпучих продуктов и хранения.', 'images/bottle-wine.jpg', 'available', '2026-11-17 12:38:03'),
(6, 'Банка для варенья 0,5л', 500, 'Твист-офф', 'Бесцветное', 'Банка с удобной ручкой и цветной крышкой. Оптимальна для варенья, компотов и солений.', 'images/ttt.jpeg', 'available', '2026-11-17 12:38:03'),
(7, 'Набор банок \"Ассорти\" 3×0,5л', 1500, '400/335', 'Бесцветное', 'Комплект из трёх банок разного объёма. Выгодное решение для фасовки и розничной продажи.', 'images/iii.jpg', 'available', '2026-11-17 12:38:03'),
(8, 'Бутылка узкая 0,33л', 330, 'PCO-1881', 'Бесцветное', 'Сlim-бутылка для лимонадов и пивных напитков. Эргономичная форма, лёгкий вес.', 'images/origh.jpg', 'available', '2026-11-17 12:38:03'),
(9, 'Банка круглая 0,5л', 500, '63-200', 'Бесцветное', 'Круглая банка с широким горлом для кремов, косметики и пищевых паст.', 'images/orig.jpg', 'available', '2026-11-17 12:38:03'),
(10, 'Банка с красной крышкой 0,65л', 650, 'Твист-офф', 'Бесцветное', 'Яркая банка с цветной пластиковой крышкой для фасовки салатов и закусок.', 'images/orr.jpg', 'available', '2026-11-17 12:38:03'),
(11, 'Мини-банка 0,2л', 200, '400/335', 'Бесцветное', 'Компактная банка для детских пюре, специй и порционных заготовок.', 'images/he.jpeg', 'available', '2026-11-17 12:38:03');

-- --------------------------------------------------------

--
-- Структура таблицы `requests`
--

CREATE TABLE `requests` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `type` enum('sample_order','bulk_order','consultation','partnership','complaint') COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('new','in_progress','done','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'new',
  `admin_comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `requests`
--

INSERT INTO `requests` (`id`, `user_id`, `type`, `subject`, `message`, `phone`, `status`, `admin_comment`, `created_at`) VALUES
(1, 2, 'partnership', 'закупка 200000 банок', 'Просим выполнить заказ', '+78954546363', 'new', NULL, '2026-11-17 11:52:47');

-- --------------------------------------------------------

--
-- Структура таблицы `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` tinyint DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `status` enum('published','moderated') COLLATE utf8mb4_unicode_ci DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Дамп данных таблицы `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `rating`, `message`, `status`, `created_at`) VALUES
(1, 2, 1, 'все выполнено в срок', 'published', '2026-11-17 12:15:22');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `login` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_person` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('client','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'client',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `login`, `password`, `company_name`, `contact_person`, `phone`, `email`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$e0NRbQ5K5rL8xZqZqZqZqO8xZqZqZqZqZqZqZqZqZqZqZqZqZq', 'Каменск-Шахтинский стеклотарный завод', 'Администратор', NULL, 'admin@kamensk-glass.ru', 'admin', '2026-11-17 08:56:38'),
(2, '111', '$2y$10$P6n5mfhVS/1IKUn3LnrpMOVkYHxCCkrOm8BFThvuN4bQmdfD9soCK', 'Крышка', 'ВВ Петров', '+78954546363', 'l@mail.ru', 'client', '2026-11-17 09:21:03');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `exhibits`
--
ALTER TABLE `exhibits`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `exhibits`
--
ALTER TABLE `exhibits`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
