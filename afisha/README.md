# Backend API — структура проекта

## Структура папок
```
project/
├── index.php               ← точка входа, роутер
├── .htaccess               ← редирект всего на index.php
├── config/
│   └── Database.php        ← PDO singleton
├── helpers/
│   ├── Auth.php            ← JWT генерация / проверка
│   └── Response.php        ← json ответы
├── models/
│   ├── BaseModel.php
│   ├── UserModel.php
│   ├── OrganizationModel.php
│   ├── EventModel.php
│   ├── VenueModel.php
│   └── ReviewFavoriteModel.php
└── controllers/
    ├── AuthController.php
    ├── EventVenueController.php
    └── ReviewFavoriteController.php
```

## API Endpoints

### AUTH
| Метод | URL | Описание |
|-------|-----|----------|
| POST | /api/auth/register | Регистрация пользователя |
| POST | /api/auth/login | Вход пользователя |
| POST | /api/auth/org/register | Регистрация организации |
| POST | /api/auth/org/login | Вход организации |
| GET  | /api/auth/me | Текущий пользователь (токен) |

### EVENTS
| Метод | URL | Описание |
|-------|-----|----------|
| GET    | /api/events | Список событий (фильтры: category_id, search, date_from) |
| GET    | /api/events/{id} | Одно событие |
| POST   | /api/events | Создать (только организация) |
| PUT    | /api/events/{id} | Обновить |
| DELETE | /api/events/{id} | Удалить |

### VENUES
| Метод | URL | Описание |
|-------|-----|----------|
| GET  | /api/venues | Список площадок |
| GET  | /api/venues/{id} | Площадка + контакты + расписание |
| POST | /api/venues | Создать |

### REVIEWS
| Метод | URL | Описание |
|-------|-----|----------|
| GET  | /api/reviews?event_id=1 | Отзывы по событию |
| GET  | /api/reviews?venue_id=1 | Отзывы по площадке |
| POST | /api/reviews | Добавить отзыв (авторизация) |

### FAVORITES
| Метод | URL | Описание |
|-------|-----|----------|
| GET    | /api/favorites | Избранное пользователя |
| POST   | /api/favorites | Добавить в избранное |
| DELETE | /api/favorites/{id} | Удалить из избранного |

## Авторизация
Все защищённые маршруты требуют заголовок:
```
Authorization: Bearer <token>
```

## Настройка БД
Откройте `config/Database.php` и укажите:
- host, db, user, password
