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
