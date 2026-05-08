# АфишаКолыма

Веб-приложение для афиши, продажи билетов и управления мероприятиями Колымы.
Два типа пользователей (зритель / организатор) + панель модератора. Работает как PWA — устанавливается на мобильный без App Store.

---

## Стек

| Слой | Технологии |
|------|-----------|
| Backend | PHP 8, без фреймворка, PDO |
| База данных | MySQL 8 (MAMP, порт 8889) |
| Аутентификация | JWT HS256, хранение в localStorage |
| Frontend | Vanilla JS, HTML5, CSS3, без фреймворков |
| PWA | Service Worker, Web App Manifest |
| Локальная разработка | MAMP (Apache 8888) |

---

## Структура проекта

```
htdocs/
├── afisha/                          # REST API (PHP)
│   ├── config/
│   │   ├── Database.php             # PDO-синглтон (host/port/db)
│   │   └── Database.example.php     # Шаблон конфига
│   ├── controllers/
│   │   ├── AuthController.php       # Регистрация, вход, профиль
│   │   ├── EventVenueController.php # CRUD событий и площадок
│   │   ├── ReviewFavoriteController.php
│   │   ├── TicketController.php     # Корзина, оформление, история
│   │   ├── ModerationController.php # Панель модератора
│   │   ├── AnalyticsController.php  # Аналитика организатора
│   │   └── UploadController.php     # Загрузка изображений
│   ├── helpers/
│   │   ├── Auth.php                 # JWT: decode + require
│   │   └── Response.php             # JSON-ответы: success/error/notFound
│   ├── models/
│   │   ├── BaseModel.php
│   │   ├── CategoryStatusModel.php
│   │   ├── EventModel.php           # Фильтры, пагинация, поиск, сортировка
│   │   ├── ModerationModel.php
│   │   ├── OrganizationModel.php
│   │   ├── ReviewFavoriteModel.php
│   │   ├── TicketModel.php
│   │   ├── UserModel.php
│   │   └── VenueModel.php           # Расписание, контакты
│   ├── .htaccess                    # Всё через index.php
│   └── index.php                    # Роутер (PHP match)
│
└── frontend/
    ├── css/
    │   └── main.css                 # CSS-переменные, светлая/тёмная тема
    ├── js/
    │   └── main.js                  # Navbar, auth, api-хелпер, утилиты
    ├── index.html                   # Главная: каталог событий и площадок
    ├── event.html                   # Страница события
    ├── venue.html                   # Страница площадки
    ├── venues.html                  # Список площадок
    ├── login.html                   # Вход (пользователь / организатор)
    ├── register.html                # Регистрация пользователя
    ├── cabinet.html                 # Личный кабинет пользователя
    ├── org-cabinet.html             # Кабинет организатора
    ├── moderator-panel.html         # Панель модератора
    ├── cart.html                    # Корзина и мои билеты
    ├── favorites.html               # Избранное
    ├── manifest.json                # PWA манифест
    └── sw.js                        # Service Worker
```

---

## API — эндпоинты

Базовый URL: `http://localhost:8888/afisha/api`

### Аутентификация
| Метод | URL | Описание |
|-------|-----|----------|
| POST | `/auth/register` | Регистрация пользователя |
| POST | `/auth/login` | Вход пользователя |
| POST | `/auth/org/register` | Регистрация организации |
| POST | `/auth/org/login` | Вход организации |
| GET | `/auth/me` | Данные текущего пользователя/орга |
| PUT | `/auth/me` | Обновление профиля |

### События
| Метод | URL | Описание |
|-------|-----|----------|
| GET | `/events` | Список (фильтры: category_id, date_from, date_to, free, search, sort, limit, offset) |
| GET | `/events/:id` | Одно событие |
| POST | `/events` | Создать (только организатор) |
| PUT | `/events/:id` | Обновить |
| DELETE | `/events/:id` | Удалить |

### Площадки
| Метод | URL | Описание |
|-------|-----|----------|
| GET | `/venues` | Список (фильтры: category_id, search) |
| GET | `/venues/:id` | Одна площадка (+ расписание, контакты) |
| POST | `/venues` | Создать |
| PUT | `/venues/:id` | Обновить |
| DELETE | `/venues/:id` | Удалить |

### Билеты и корзина
| Метод | URL | Описание |
|-------|-----|----------|
| GET | `/tickets` | Корзина текущего пользователя |
| POST | `/tickets` | Добавить в корзину |
| PUT | `/tickets/:id` | Изменить количество |
| DELETE | `/tickets/:id` | Удалить из корзины |
| POST | `/tickets/checkout` | Оформить заказ (cart → paid) |
| GET | `/tickets/paid` | Купленные билеты |
| GET | `/tickets/count` | Количество позиций в корзине |

### Отзывы и избранное
| Метод | URL | Описание |
|-------|-----|----------|
| GET | `/reviews?event_id=` | Отзывы к событию |
| GET | `/reviews?venue_id=` | Отзывы к площадке |
| POST | `/reviews` | Добавить отзыв (только пользователь) |
| DELETE | `/reviews/:id` | Удалить свой отзыв |
| GET | `/favorites` | Список избранного |
| POST | `/favorites` | Добавить в избранное |
| DELETE | `/favorites/:id` | Убрать из избранного |

### Справочники и загрузка
| Метод | URL | Описание |
|-------|-----|----------|
| GET | `/categories` | Все категории |
| GET | `/statuses` | Все статусы |
| POST | `/upload/avatar` | Загрузить аватар |
| POST | `/upload/event` | Загрузить изображение события |
| POST | `/upload/venue` | Загрузить изображение площадки |

### Модерация (только модератор)
| Метод | URL | Описание |
|-------|-----|----------|
| GET | `/moderation/stats` | Общая статистика платформы |
| GET | `/moderation/orgs` | Список организаций (фильтр по статусу) |
| PUT | `/moderation/orgs/:id` | Изменить статус организации |
| GET | `/moderation/reviews` | Все отзывы |
| DELETE | `/moderation/reviews/:id` | Удалить отзыв |
| GET | `/moderation/events` | Все события |
| PUT | `/moderation/events/:id` | Изменить статус события |

### Аналитика (только организатор)
| Метод | URL | Описание |
|-------|-----|----------|
| GET | `/analytics/org` | Выручка, продажи, топ событий, динамика за 30 дней |

---

## Функционал — что реализовано

### Главная страница (`index.html`)
- Баннер-карусель с автопрокруткой, прогресс-барами и параллакс-тенями
- Вкладки «События» / «Площадки»
- Горизонтальная полоса дат (60 дней) — выбор конкретного дня
- Фильтр-бар: кнопка «Все фильтры» → модальное окно с пресетами дат, диапазоном, тоглом «Бесплатно» и сортировкой
- Горизонтальный скролл категорий; категории, не влезающие в строку, убираются в dropdown «Ещё ▾»
- Строка активных фильтров с тегами — каждый тег можно удалить по отдельности
- Категории в навбаре синхронизированы с чипами под фильтрами
- Поиск в навбаре — история (localStorage), автодополнение из истории, удаление отдельных запросов
- Пагинация
- Кнопка «В избранное» прямо на карточке (моментально без перезагрузки)
- Адаптивная сетка

### Страница события (`event.html`)
- Шапка с изображением / градиентом, категория, возраст, организация
- Описание
- Кнопка «Купить» / «Добавить в корзину» (меняется после добавления)
- Кнопка «В избранное»
- Отзывы со звёздным рейтингом; удаление своих отзывов
- Встроенная Яндекс.Карта по адресу площадки
- Сайдбар с информацией и ценой

### Страница площадки (`venue.html`)
- Описание, карта (Яндекс)
- Расписание работы по дням недели
- Контакты (телефон, email, сайт, соцсети)
- Отзывы со звёздным рейтингом
- Кнопка «В избранное»

### Аутентификация
- Регистрация и вход: пользователь (по телефону) и организация (по email)
- JWT в localStorage; автообновление navbar при смене состояния
- Защита маршрутов: редирект на login.html при отсутствии токена

### Личный кабинет пользователя (`cabinet.html`)
- Редактирование профиля (ФИО, телефон, email, дата рождения)
- Загрузка/смена аватара (превью до отправки)
- Раздел «Избранное»: фильтрация по событиям / площадкам, удаление
- Inline-валидация всех полей

### Кабинет организатора (`org-cabinet.html`)
- Список своих событий с редактированием и удалением
- Форма создания/редактирования события: название, описание, дата начала/конца, цена, категория, площадка, изображение, возрастной рейтинг, статус
- Загрузка изображения с превью
- Редактирование профиля организации (ИНН, ОГРН, КПП, контакты) с inline-валидацией
- Аналитика: выручка, продажи, количество событий, ожидающие оплаты; топ-10 событий по выручке; CSS-столбчатая диаграмма продаж за 30 дней

### Панель модератора (`moderator-panel.html`)
- Статистика платформы (орги, события, отзывы)
- Управление организациями: фильтр по статусу (ожидают / одобрены / отклонены), изменение статуса
- Модерация событий: просмотр всех событий, изменение статуса
- Модерация отзывов: просмотр и удаление

### Корзина и билеты (`cart.html`)
- Список позиций корзины с изменением количества
- Оформление заказа с выбором способа оплаты (наличные, карта, СБП)
- После оплаты — раздел «Мои билеты» с QR-кодами для каждого билета (формат `TICKET:id:event_id:qty`)
- История покупок

### Избранное (`favorites.html`)
- События и площадки вместе, фильтрация по типу

### PWA
- Service Worker: кеширование статики, API через сеть (fallback в кеш при офлайне)
- Web App Manifest: installable, standalone, shortcuts («Все события», «Мои билеты»)
- Theme-color для браузера

### UI / UX
- Светлая и тёмная тема (переключатель в navbar, сохранение в localStorage)
- Защита от XSS: `escHtml()` применяется ко всем данным из API перед вставкой в innerHTML
- Toast-уведомления
- Адаптивная вёрстка (мобильный breakpoint 600–768px)
- Кастомные скроллбары, focus-visible, плавные переходы

---

## Функционал — что реализовано частично / с ограничениями

| Что | Что сделано | Чего не хватает |
|-----|-------------|-----------------|
| **Оплата** | Смена статуса `cart → paid` при checkout, выбор способа оплаты записывается | Нет интеграции с реальным платёжным шлюзом (Stripe, ЮKassa и др.) |
| **Аналитика** | Выручка, продажи, топ событий, динамика за 30 дней для организатора | Нет глобальной аналитики для модератора; нет графика на Chart.js (только CSS) |
| **Кабинет организатора** | CRUD событий, профиль, аналитика | Нет управления площадками; нет раздела возвратов; нет управления билетами (ручная отмена) |
| **Модерация** | Статусы для событий и организаций | Нет бейджа «Верифицирован» на публичных страницах; нет верификации с подтверждением документов |
| **PWA** | Service Worker работает | Нет реальных PNG-иконок (`icon-192.png`, `icon-512.png` в папке `img/` не созданы) — установка на iOS может не работать |
| **Поиск** | История в localStorage, автодополнение из истории | Нет поиска по API «на лету» (live search по мере печати) |
| **Площадки** | Полные страницы, расписание, контакты, отзывы | Нет создания площадок из кабинета организатора (только через прямой POST) |
| **Безопасность** | JWT, escHtml, PDO prepared statements | Нет rate limiting; CORS `*`; JWT-секрет зашит в код, не в env |

---

## Функционал — что не реализовано

| Фича | Статус |
|------|--------|
| **Промокоды** | Нет: ни таблицы в БД, ни API, ни UI в корзине и кабинете орга |
| **Email-уведомления** | Нет: подтверждение заказа, напоминание за день до события |
| **Верификация организаций** | Нет: колонки `verified` нет в таблице `organization`, бейджа нет на страницах |
| **Повторяющиеся события** | Нет: в таблице `events` нет полей `recurrence_rule`, `recurrence_end` |
| **Push-уведомления** | Нет: нет подписки на Web Push |
| **Лимит билетов / sold out** | Нет: в `events` нет колонки `capacity`; нет проверки при покупке |
| **Возврат билетов** | Нет: нет маршрута `PUT /tickets/:id/refund` и UI |
| **Публичная страница организации** | Нет: нет `org.html` со списком её событий |
| **Рейтинг организаций** | Нет |
| **Поделиться событием** | Нет (Web Share API) |
| **SEO / OG-теги** | Нет динамических мета-тегов на страницах событий |
| **Календарный вид событий** | Нет |
| **Список ожидания (waitlist)** | Нет |
| **Мультиязычность** | Нет (всё на русском) |
| **Тесты** | Нет (ни unit, ни e2e) |

---

## Известные особенности БД

- Таблица `categories` — первичный ключ `id`, **не** `category_id`. Все JOIN-ы должны использовать `c.id`.
- Таблица `users` — нет колонки `full_name`. Имя собирается как `TRIM(CONCAT_WS(' ', last_name, first_name, patronymic))`.
- Таблица `organization` — наоборот, есть колонка `full_name`.
- Таблица `tickets` — нет `updated_at`, только `created_at` и `paid_at`.
- Таблица `schedule` — опечатка в PK: `shedule_id` (одна «h»).

---

## Установка и запуск

### Требования
- [MAMP](https://www.mamp.info/) (PHP 8+, MySQL 8)
- Браузер Chrome / Safari / Firefox

### Шаги

1. Скопировать проект в `htdocs` MAMP:
   ```bash
   git clone <url> /Applications/MAMP/htdocs
   ```

2. Запустить MAMP: Apache → порт **8888**, MySQL → порт **8889**.

3. В phpMyAdmin (`http://localhost:8888/phpMyAdmin`) создать БД `afisha` и импортировать SQL-схему.

4. Настроить подключение к БД:
   ```bash
   cp afisha/config/Database.example.php afisha/config/Database.php
   # Открыть Database.php и при необходимости изменить host/port/user/password
   ```

5. Открыть в браузере: `http://localhost:8888/frontend/index.html`

### Первый пользователь-модератор

Модераторская роль назначается напрямую в БД:
```sql
UPDATE users SET role_id = (SELECT role_id FROM roles WHERE name = 'moderator') WHERE phone = 'ваш_телефон';
```

---

## База данных

**СУБД:** MySQL 8 · **Кодировка:** utf8mb4 · **БД:** `afisha`

### Схема связей

```
roles ─────────── users ──────────── favorites
                    │                    │
              reviews (event/venue)      │
                    │                    │
organization_types ─ organization        │
                    │                    │
statuses ───────────┤                    │
                    │                    │
         events ────┴──── favorites ─────┘
           │
           └──── tickets
                   (cart / paid)

categories ──── venues
                  ├── contacts
                  └── schedule
```

### Таблицы

#### `users`
| Колонка | Тип | Описание |
|---------|-----|----------|
| `user_id` | int PK | |
| `last_name` | varchar(100) | |
| `first_name` | varchar(100) | |
| `patronymic` | varchar(100) | |
| `email` | text | |
| `phone` | varchar(20) | Логин |
| `age` | tinyint | |
| `avatar` | varchar(500) | Путь к файлу |
| `role_id` | int FK | → `roles` |
| `password_hash` | varchar(255) | bcrypt |

#### `roles`
| `role_id` | `name` (user / moderator / admin) |

#### `organization`
| Колонка | Тип | Описание |
|---------|-----|----------|
| `organization_id` | int PK | |
| `full_name` | varchar(100) | |
| `address` | text | |
| `inn` | text | |
| `ogrn` | text | |
| `kpp` | text | |
| `phone` | varchar(20) | |
| `website` | text | |
| `type_id` | int FK | → `organization_types` |
| `status_id` | int FK | → `statuses` |
| `email` | text | Логин |
| `password_hash` | varchar(255) | |
| `image` | varchar(500) | Логотип |

#### `events`
| Колонка | Тип | Описание |
|---------|-----|----------|
| `event_id` | int PK | |
| `title` | varchar(255) | |
| `description` | text | |
| `age_restriction` | tinyint | 0/6/12/16/18 |
| `start_datetime` | datetime | |
| `end_datetime` | datetime | |
| `price` | decimal(10,2) | 0 = бесплатно |
| `image` | text | |
| `organization_id` | int FK | → `organization` |
| `venue_id` | int FK | → `venues` |
| `category_id` | int FK | → `categories.id` |
| `status_id` | int FK | → `statuses` |

#### `venues`
| Колонка | Тип | Описание |
|---------|-----|----------|
| `venue_id` | int PK | |
| `name` | varchar(50) | |
| `address` | text | |
| `category_id` | int FK | → `categories.id` |
| `age` | tinyint | |
| `description` | text | |
| `image` | text | |

#### `tickets`
| Колонка | Тип | Описание |
|---------|-----|----------|
| `ticket_id` | int PK | |
| `user_id` | int FK | → `users` CASCADE DELETE |
| `event_id` | int FK | → `events` CASCADE DELETE |
| `quantity` | int | |
| `price` | decimal(10,2) | Цена на момент добавления |
| `status` | varchar(20) | `cart` / `paid` |
| `payment_method` | varchar(50) | наличные / карта / СБП |
| `paid_at` | datetime | |
| `created_at` | datetime | |

#### `reviews`
| Колонка | Тип | Описание |
|---------|-----|----------|
| `review_id` | int PK | |
| `user_id` | int FK | → `users` |
| `event_id` | int FK | NULL если к площадке |
| `venue_id` | int FK | NULL если к событию |
| `text` | text | |
| `rating` | int | 1–5 |
| `created_at` | datetime | |

#### `favorites`
| Колонка | Тип | Описание |
|---------|-----|----------|
| `favorite_id` | int PK | |
| `user_id` | int FK | → `users` |
| `event_id` | int FK | NULL если площадка |
| `venue_id` | int FK | NULL если событие |
| `created_at` | datetime | |

#### `categories`
| `id` PK | `name` varchar(50) |

> ⚠️ PK называется `id`, а не `category_id`.

#### `statuses`
| `status_id` PK | `status_name` varchar(50) |

#### `contacts` (к площадке)
| `contact_id` | `venue_id` FK | `number` | `email` | `social_media` | `website` |

#### `schedule` (к площадке)
| `shedule_id` PK | `venue_id` FK | `organization_id` FK | `day_of_week` | `start_time` | `end_time` | `description` |

> ⚠️ Опечатка в названии PK: `shedule_id` (одна «h»).

#### `organization_types`
| `type_id` PK | `name` varchar(50) |
