# АфишаКолыма

Веб-приложение для просмотра и покупки билетов на события Колымы: концерты, выставки, фестивали и другие мероприятия.

## Стек технологий

| Слой | Технологии |
|------|-----------|
| Backend | PHP 8 (без фреймворка), PDO |
| База данных | MySQL (через MAMP, порт 8889) |
| Аутентификация | JWT (HS256) |
| Frontend | Vanilla JS, HTML5, CSS3 |
| Локальная разработка | MAMP |

## Структура проекта

```
htdocs/
├── afisha/               # REST API (PHP)
│   ├── config/
│   │   └── Database.php  # Подключение к БД (синглтон PDO)
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── EventVenueController.php
│   │   ├── ReviewFavoriteController.php
│   │   ├── TicketController.php
│   │   └── UploadController.php
│   ├── helpers/
│   │   ├── Auth.php      # JWT-декодирование и проверка токена
│   │   └── Response.php  # Хелперы для JSON-ответов
│   ├── models/
│   │   ├── BaseModel.php
│   │   ├── CategoryStatusModel.php
│   │   ├── EventModel.php
│   │   ├── OrganizationModel.php
│   │   ├── ReviewFavoriteModel.php
│   │   ├── TicketModel.php
│   │   ├── UserModel.php
│   │   └── VenueModel.php
│   ├── .htaccess         # Роутинг всех запросов через index.php
│   └── index.php         # Единая точка входа (роутер)
│
└── frontend/             # Статический фронтенд
    ├── css/
    │   └── main.css      # Глобальные стили, темы (light/dark)
    ├── js/
    │   └── main.js       # Общий JS: navbar, auth, API-хелпер
    ├── uploads/
    │   └── avatars/      # Загружаемые аватары пользователей
    ├── index.html        # Главная страница — каталог событий
    ├── event.html        # Страница события
    ├── venue.html        # Страница площадки
    ├── venues.html       # Список площадок
    ├── login.html        # Вход / Регистрация
    ├── register.html     # Регистрация (отдельная страница)
    ├── cabinet.html      # Личный кабинет пользователя
    ├── org-cabinet.html  # Кабинет организатора
    └── cart.html         # Корзина и оформление заказа
```

## API — эндпоинты

Базовый URL: `http://localhost:8888/afisha/api`

### Аутентификация
| Метод | URL | Описание |
|-------|-----|----------|
| POST | `/auth/register` | Регистрация пользователя |
| POST | `/auth/login` | Вход пользователя |
| POST | `/auth/org/register` | Регистрация организации |
| POST | `/auth/org/login` | Вход организации |
| GET | `/auth/me` | Данные текущего пользователя |
| PUT | `/auth/me` | Обновление профиля |

### События
| Метод | URL | Описание |
|-------|-----|----------|
| GET | `/events` | Список событий (фильтры, пагинация, поиск) |
| GET | `/events/:id` | Одно событие |
| POST | `/events` | Создать событие (организатор) |
| PUT | `/events/:id` | Обновить событие |
| DELETE | `/events/:id` | Удалить событие |

### Площадки
| Метод | URL | Описание |
|-------|-----|----------|
| GET | `/venues` | Список площадок |
| GET | `/venues/:id` | Одна площадка |
| POST | `/venues` | Создать площадку |
| PUT | `/venues/:id` | Обновить площадку |
| DELETE | `/venues/:id` | Удалить площадку |

### Корзина и билеты
| Метод | URL | Описание |
|-------|-----|----------|
| GET | `/tickets` | Корзина пользователя |
| POST | `/tickets` | Добавить билет в корзину |
| PUT | `/tickets/:id` | Изменить количество |
| DELETE | `/tickets/:id` | Удалить из корзины |
| POST | `/tickets/checkout` | Оформить заказ |
| GET | `/tickets/paid` | Купленные билеты |
| GET | `/tickets/count` | Количество в корзине |

### Прочее
| Метод | URL | Описание |
|-------|-----|----------|
| GET | `/reviews` | Отзывы |
| POST | `/reviews` | Добавить отзыв |
| DELETE | `/reviews/:id` | Удалить отзыв |
| GET | `/favorites` | Избранные события |
| POST | `/favorites` | Добавить в избранное |
| DELETE | `/favorites/:id` | Убрать из избранного |
| GET | `/categories` | Категории событий |
| GET | `/statuses` | Статусы событий |
| POST | `/upload/avatar` | Загрузить аватар |
| POST | `/upload/event` | Загрузить изображение события |
| POST | `/upload/venue` | Загрузить изображение площадки |

## Установка и запуск

### Требования
- [MAMP](https://www.mamp.info/) (или любой другой локальный сервер с PHP 8+ и MySQL)

### Шаги

1. Клонировать репозиторий в папку `htdocs` MAMP:
   ```bash
   git clone <url> /Applications/MAMP/htdocs
   ```

2. Запустить MAMP и убедиться, что серверы стартовали (Apache на порту 8888, MySQL на 8889).

3. Создать базу данных `afisha` в phpMyAdmin (`http://localhost:8888/phpMyAdmin`) и импортировать схему.

4. Если нужно изменить параметры подключения — отредактировать `afisha/config/Database.php`.

5. Открыть в браузере: `http://localhost:8888/frontend/`

## Функции

- Каталог событий с фильтрацией по категории, дате, статусу и поиском по названию
- Страницы событий и площадок
- Регистрация и вход для пользователей и организаторов
- Тёмная/светлая тема
- Добавление событий в избранное
- Корзина и оформление заказа на билеты
- Личный кабинет с историей заказов
- Кабинет организатора для управления событиями
- Загрузка изображений для событий, площадок и аватаров

## Авторизация

Используется JWT-токен. После входа токен сохраняется в `localStorage`. Защищённые запросы отправляют заголовок:
```
Authorization: Bearer <token>
```

---

## База данных

**СУБД:** MySQL 8 · **Кодировка:** utf8mb4 · **БД:** `afisha`

### Диаграмма связей

```
roles ──────────── users ──────────── favorites
                     │                    │
                     └──── reviews ───────┤
                     │                    │
organization_types ── organization        │
                     │                    │
statuses ────────────┤                    │
         │           │                    │
         └── events ─┴──── favorites ─────┤
               │                          │
               └──── tickets              │
                                          │
categories ──── venues ───────────────────┘
                  │
               contacts
               schedule
```

---

### `users` — пользователи

| Колонка | Тип | NULL | По умолчанию | Описание |
|---------|-----|------|--------------|----------|
| `user_id` | int | NO | AUTO_INCREMENT | Первичный ключ |
| `full_name` | varchar(100) | NO | — | Полное имя |
| `email` | text | YES | NULL | Email |
| `phone` | varchar(20) | NO | — | Телефон (логин) |
| `age` | tinyint | YES | NULL | Возраст |
| `avatar` | varchar(500) | YES | NULL | Путь к аватару |
| `role_id` | int | YES | NULL | FK → `roles.role_id` |
| `password_hash` | varchar(255) | NO | — | Хэш пароля |

---

### `roles` — роли пользователей

| Колонка | Тип | NULL | Описание |
|---------|-----|------|----------|
| `role_id` | int | NO | Первичный ключ |
| `name` | varchar(50) | NO | Название роли |

---

### `organization` — организаторы

| Колонка | Тип | NULL | По умолчанию | Описание |
|---------|-----|------|--------------|----------|
| `organization_id` | int | NO | AUTO_INCREMENT | Первичный ключ |
| `full_name` | varchar(100) | NO | — | Название организации |
| `address` | text | YES | NULL | Адрес |
| `inn` | text | YES | NULL | ИНН |
| `type_id` | int | YES | NULL | FK → `organization_types.type_id` |
| `status_id` | int | YES | NULL | FK → `statuses.status_id` |
| `password_hash` | varchar(255) | NO | — | Хэш пароля |
| `email` | text | YES | NULL | Email (логин) |
| `image` | varchar(500) | YES | NULL | Логотип |

---

### `organization_types` — типы организаций

| Колонка | Тип | NULL | Описание |
|---------|-----|------|----------|
| `type_id` | int | NO | Первичный ключ |
| `name` | varchar(50) | NO | Название типа |

---

### `events` — события

| Колонка | Тип | NULL | По умолчанию | Описание |
|---------|-----|------|--------------|----------|
| `event_id` | int | NO | AUTO_INCREMENT | Первичный ключ |
| `title` | varchar(255) | NO | — | Название |
| `description` | text | YES | NULL | Описание |
| `age_restriction` | tinyint | YES | NULL | Возрастное ограничение (0, 6, 12, 16, 18) |
| `start_datetime` | datetime | NO | — | Дата и время начала |
| `end_datetime` | datetime | NO | — | Дата и время окончания |
| `price` | decimal(10,2) | YES | 0.00 | Цена билета |
| `image` | text | YES | NULL | Путь к изображению |
| `organization_id` | int | YES | NULL | FK → `organization.organization_id` |
| `venue_id` | int | YES | NULL | FK → `venues.venue_id` |
| `category_id` | int | YES | NULL | FK → `categories.id` |
| `status_id` | int | YES | NULL | FK → `statuses.status_id` |

---

### `venues` — площадки

| Колонка | Тип | NULL | По умолчанию | Описание |
|---------|-----|------|--------------|----------|
| `venue_id` | int | NO | AUTO_INCREMENT | Первичный ключ |
| `name` | varchar(50) | NO | — | Название |
| `address` | text | YES | NULL | Адрес |
| `category_id` | int | YES | NULL | FK → `categories.id` |
| `age` | tinyint | YES | NULL | Возрастное ограничение |
| `description` | text | YES | NULL | Описание |
| `image` | text | YES | NULL | Путь к изображению |

---

### `categories` — категории

| Колонка | Тип | NULL | Описание |
|---------|-----|------|----------|
| `id` | int | NO | Первичный ключ |
| `name` | varchar(50) | NO | Название категории |

---

### `statuses` — статусы

| Колонка | Тип | NULL | Описание |
|---------|-----|------|----------|
| `status_id` | int | NO | Первичный ключ |
| `status_name` | varchar(50) | NO | Название статуса |

---

### `tickets` — билеты и корзина

| Колонка | Тип | NULL | По умолчанию | Описание |
|---------|-----|------|--------------|----------|
| `ticket_id` | int | NO | AUTO_INCREMENT | Первичный ключ |
| `user_id` | int | NO | — | FK → `users.user_id` (CASCADE DELETE) |
| `event_id` | int | NO | — | FK → `events.event_id` (CASCADE DELETE) |
| `quantity` | int | YES | 1 | Количество билетов |
| `price` | decimal(10,2) | NO | — | Цена на момент добавления |
| `status` | varchar(20) | YES | `'cart'` | `cart` — корзина, `paid` — оплачено |
| `payment_method` | varchar(50) | YES | NULL | Способ оплаты |
| `paid_at` | datetime | YES | NULL | Дата и время оплаты |
| `created_at` | datetime | YES | CURRENT_TIMESTAMP | Дата добавления |

---

### `reviews` — отзывы

| Колонка | Тип | NULL | По умолчанию | Описание |
|---------|-----|------|--------------|----------|
| `review_id` | int | NO | AUTO_INCREMENT | Первичный ключ |
| `user_id` | int | YES | NULL | FK → `users.user_id` |
| `text` | text | YES | NULL | Текст отзыва |
| `rating` | int | YES | NULL | Оценка |
| `created_at` | datetime | YES | NULL | Дата |
| `event_id` | int | YES | NULL | FK → `events.event_id` |
| `venue_id` | int | YES | NULL | FK → `venues.venue_id` |

---

### `favorites` — избранное

| Колонка | Тип | NULL | По умолчанию | Описание |
|---------|-----|------|--------------|----------|
| `favorite_id` | int | NO | AUTO_INCREMENT | Первичный ключ |
| `user_id` | int | YES | NULL | FK → `users.user_id` |
| `event_id` | int | YES | NULL | FK → `events.event_id` (событие или...) |
| `venue_id` | int | YES | NULL | FK → `venues.venue_id` (...площадка) |
| `created_at` | datetime | YES | NULL | Дата добавления |

---

### `contacts` — контакты площадок

| Колонка | Тип | NULL | По умолчанию | Описание |
|---------|-----|------|--------------|----------|
| `contact_id` | int | NO | AUTO_INCREMENT | Первичный ключ |
| `venue_id` | int | YES | NULL | FK → `venues.venue_id` |
| `number` | varchar(50) | YES | NULL | Телефон |
| `email` | text | YES | NULL | Email |
| `social_media` | text | YES | NULL | Ссылки на соцсети |
| `website` | text | YES | NULL | Сайт |

---

### `schedule` — расписание площадок

| Колонка | Тип | NULL | По умолчанию | Описание |
|---------|-----|------|--------------|----------|
| `shedule_id` | int | NO | AUTO_INCREMENT | Первичный ключ |
| `venue_id` | int | YES | NULL | FK → `venues.venue_id` |
| `organization_id` | int | YES | NULL | FK → `organization.organization_id` |
| `day_of_week` | varchar(10) | YES | NULL | День недели |
| `start_time` | time | YES | NULL | Время открытия |
| `end_time` | time | YES | NULL | Время закрытия |
| `description` | text | YES | NULL | Примечание |
