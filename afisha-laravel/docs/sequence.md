# Диаграммы последовательности

## 1. Авторизация пользователя

```mermaid
sequenceDiagram
    autonumber
    participant U  as Пользователь
    participant AC as AuthController
    participant DB as База данных

    U->>AC: POST /api/login {phone/email, password}
    AC->>DB: SELECT * FROM users WHERE phone=? OR email=?
    DB-->>AC: запись пользователя / null

    alt пользователь не найден
        AC-->>U: 401 Неверные учётные данные
    else Hash::check() вернул false
        AC-->>U: 401 Неверные учётные данные
    else blocked_until не истёк
        AC-->>U: 403 Аккаунт заблокирован до [дата]
    end

    AC->>DB: tokens()->delete() — удалить все токены пользователя
    AC->>DB: createToken('auth-token') — INSERT personal_access_tokens
    AC-->>U: 200 {token, user {id, first_name, email, phone, role}}
    Note over U: Сохранить токен, перейти в личный кабинет
```

---

## 2. Регистрация пользователя

```mermaid
sequenceDiagram
    autonumber
    participant U  as Пользователь
    participant AC as AuthController
    participant DB as База данных

    U->>AC: POST /api/register {first_name, phone, email, password, password_confirmation}
    AC->>AC: $request->validate([phone, email, password — required|unique])

    alt ошибка валидации (422)
        AC-->>U: 422 {errors: {поле: [сообщение]}}
    end

    AC->>AC: normalizePhone($phone) — убрать пробелы, привести к +7...
    AC->>AC: Hash::make($password) — bcrypt-хеш пароля
    AC->>DB: User::create({first_name, phone, email, password_hash, role_id=2})
    AC->>DB: AuditLog::write('register', 'user', user_id, user_id, 'user', {...})
    AC->>DB: $user->createToken('auth-token') — INSERT personal_access_tokens
    AC-->>U: 201 {token, user}
    Note over U: Аккаунт создан, вход выполнен автоматически
```

---

## 3. Покупка билета

```mermaid
sequenceDiagram
    autonumber
    participant U  as Пользователь
    participant TC as TicketController
    participant DB as База данных
    participant SM as SMTP-сервер

    Note over U,TC: Шаг 1 — добавление в корзину

    U->>TC: POST /api/cart/add {event_id, quantity}

    alt пользователь — организация
        TC-->>U: 403 Организации не могут использовать корзину
    end

    TC->>DB: SELECT SUM(quantity) FROM tickets<br/>WHERE event_id=? AND status IN (paid, return_pending, cart)
    DB-->>TC: число занятых мест

    alt sold + quantity > event.capacity
        TC-->>U: 422 Билеты закончились / Осталось только N мест
    end

    TC->>DB: Ticket::create({event_id, price=event.price, user_id, quantity, status=cart})
    TC-->>U: 201 {ticket_id, event, price, quantity, status=cart}

    Note over U,TC: Шаг 2 — оплата корзины

    U->>TC: POST /api/cart/checkout {payment_method, promo_code?}
    TC->>DB: SELECT tickets WHERE user_id=? AND paid_at IS NULL AND status=cart

    alt корзина пуста
        TC-->>U: 422 Корзина пуста
    end

    TC->>DB: UPDATE tickets SET status=paid, paid_at=NOW(), payment_method=?<br/>WHERE user_id=? AND paid_at IS NULL AND status=cart

    opt promo_code передан и активен
        TC->>DB: UPDATE promo_codes SET uses_count=uses_count+1 WHERE code=?
    end

    TC->>SM: Mail::to(user.email)->send(new TicketConfirmationMail(tickets, total, payLabel))
    SM-->>TC: 250 OK (письмо принято)
    TC-->>U: 200 {paid: N} "Оплачено"
    Note over U: Письмо с подтверждением отправлено на email
```

---

## 4. Создание мероприятия

```mermaid
sequenceDiagram
    autonumber
    participant O  as Организация
    participant EC as EventController
    participant DB as База данных

    O->>EC: POST /api/events {title, description, start_datetime,<br/>venue_id, category_id, price, capacity?, image?}

    alt не аутентифицирована как Organization
        EC-->>O: 401 / 403 Нет доступа
    end

    EC->>EC: $request->validate([title, start_datetime, venue_id — required, ...])

    alt ошибка валидации
        EC-->>O: 422 {errors}
    end

    EC->>DB: Event::create({...поля..., organization_id, status_id=pending})
    DB-->>EC: event_id
    EC->>DB: AuditLog::write('create_event', 'event', event_id, org_id, 'organization', {...})
    EC-->>O: 201 {event_id, title, status=pending, ...}
    Note over O: Мероприятие отправлено на проверку модератором
```

---

## 5. Модерация мероприятия

```mermaid
sequenceDiagram
    autonumber
    participant M  as Модератор
    participant MC as ModerationController
    participant DB as База данных
    participant SM as SMTP-сервер

    M->>MC: GET /api/moderation/events

    MC->>MC: requireModerator() — проверить роль moderator|admin

    alt нет роли
        MC-->>M: 403 Нет доступа
    end

    MC->>DB: SELECT events WHERE status=pending (+ eager load venue, org, category)
    DB-->>MC: список мероприятий
    MC-->>M: 200 {events[]}

    M->>MC: PATCH /api/moderation/events/{id} {status: ACTIVE | REJECTED}
    MC->>DB: UPDATE events SET status_id=? WHERE event_id=?
    MC->>DB: AuditLog::write('update_event_status', 'event', event_id,<br/>moderator_id, 'moderator', {old_status, new_status})

    alt status = ACTIVE (одобрено)
        MC->>DB: SELECT org_subscriptions WHERE organization_id=?
        DB-->>MC: список подписчиков
        loop для каждого подписчика
            MC->>SM: Mail::to(subscriber.email)->send(new EventPublishedMail(event))
        end
    else status = REJECTED (отклонено)
        MC->>SM: Mail::to(org.email)->send(new OrgStatusMail(org, status, comment))
    end

    MC-->>M: 200 {event}
```

---

## 6. Возврат билета

```mermaid
sequenceDiagram
    autonumber
    participant U  as Пользователь
    participant TC as TicketController
    participant DB as База данных

    U->>TC: POST /api/tickets/{id}/return

    alt пользователь — организация
        TC-->>U: 403 Организации не могут использовать корзину
    end

    TC->>DB: SELECT ticket WHERE ticket_id=? AND user_id=? AND status=paid
    DB-->>TC: запись / null

    alt билет не найден или статус не paid
        TC-->>U: 404 Билет не найден или уже возвращён
    end

    TC->>DB: UPDATE tickets SET status=return_pending WHERE ticket_id=?
    TC-->>U: 200 "Заявка на возврат принята"
    Note over U: Заявка передана администратору на рассмотрение

    opt Администратор принимает решение
        Note over TC,DB: Администратор через панель меняет статус
        TC->>DB: UPDATE tickets SET status=returned WHERE ticket_id=?
        Note over U: Средства возвращаются пользователю вне системы
    end
```

---

## 7. Отправка напоминаний

```mermaid
sequenceDiagram
    autonumber
    participant CR as CRON (ISPmanager)
    participant AR as Artisan / SendEventReminders
    participant DB as База данных
    participant SM as SMTP-сервер

    CR->>AR: php artisan reminders:send (ежедневно в 08:00)

    AR->>DB: SELECT tickets<br/>JOIN events ON events.event_id = tickets.event_id<br/>WHERE tickets.status = paid<br/>AND events.start_datetime BETWEEN tomorrow 00:00:00 AND 23:59:59

    DB-->>AR: коллекция Ticket (с загруженными event, user)

    alt нет билетов на завтра
        AR-->>CR: команда завершена (нет отправок)
    end

    AR->>AR: groupBy('user_id') — сгруппировать по пользователю

    loop для каждого пользователя
        AR->>SM: Mail::to(user.email)->send(new EventReminderMail(user, tickets[]))
        SM-->>AR: 250 OK
    end

    AR-->>CR: команда завершена (exit 0)
    Note over CR: Следующий запуск — через 24 часа
```
