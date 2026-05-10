#!/bin/bash
# ═══════════════════════════════════════════════════════════════════
#  АфишаКолыма — скрипт деплоя на Ubuntu 22.04 / 24.04
#  Использование: sudo bash deploy.sh
# ═══════════════════════════════════════════════════════════════════
set -euo pipefail

# ── Настройки ────────────────────────────────────────────────────────────────
DEPLOY_DIR="/var/www/afisha"
DB_NAME="afisha"
DB_USER="afisha"
NGINX_SITE="afisha"

# ── Цвета для вывода ─────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; CYAN='\033[0;36m'; NC='\033[0m'
ok()   { echo -e "${GREEN}✓ $1${NC}"; }
info() { echo -e "${CYAN}▶ $1${NC}"; }
warn() { echo -e "${YELLOW}⚠ $1${NC}"; }
err()  { echo -e "${RED}✗ $1${NC}"; exit 1; }

# ── Проверки перед запуском ───────────────────────────────────────────────────
[ "$EUID" -ne 0 ] && err "Запустите скрипт от root: sudo bash deploy.sh"

# ── Запрос параметров ─────────────────────────────────────────────────────────
echo ""
echo -e "${CYAN}════════════════════════════════════════════════════${NC}"
echo -e "${CYAN}        Деплой АфишаКолыма                          ${NC}"
echo -e "${CYAN}════════════════════════════════════════════════════${NC}"
echo ""

read -p "Домен сайта (например: afisha.kolyma.ru): " DOMAIN
[ -z "$DOMAIN" ] && err "Домен не может быть пустым"

read -p "URL репозитория GitHub (https://github.com/...): " REPO_URL
[ -z "$REPO_URL" ] && err "URL репозитория не может быть пустым"

# Режим обновления если директория уже существует
UPDATE_MODE=false
if [ -d "$DEPLOY_DIR/.git" ]; then
    warn "Директория $DEPLOY_DIR уже существует — режим обновления"
    UPDATE_MODE=true
fi

# ── 1. Системные пакеты ───────────────────────────────────────────────────────
info "[1/7] Установка системных пакетов..."

# PHP репозиторий (актуальные версии)
if ! command -v php8.3 &>/dev/null; then
    apt-get install -y -qq software-properties-common
    add-apt-repository -y ppa:ondrej/php
fi

apt-get update -qq
apt-get install -y -qq \
    git curl unzip \
    nginx \
    mysql-server \
    php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml \
    php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl

# Composer
if ! command -v composer &>/dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --quiet
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi
ok "Пакеты установлены"

# ── 2. База данных ────────────────────────────────────────────────────────────
info "[2/7] Настройка MySQL..."

# Генерируем надёжный пароль
DB_PASS=$(openssl rand -base64 24 | tr -d '/+=' | head -c 32)

# Запускаем MySQL если не запущен
systemctl start mysql 2>/dev/null || true

if ! mysql -e "SHOW DATABASES LIKE '$DB_NAME';" 2>/dev/null | grep -q "$DB_NAME"; then
    mysql <<SQL
CREATE DATABASE \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL
    ok "База данных создана: $DB_NAME (пользователь: $DB_USER)"
else
    warn "База данных $DB_NAME уже существует — пропускаю создание"
    # Читаем пароль из существующего .env если есть
    if [ -f "$DEPLOY_DIR/afisha-laravel/.env" ]; then
        DB_PASS=$(grep '^DB_PASSWORD=' "$DEPLOY_DIR/afisha-laravel/.env" | cut -d= -f2)
    fi
fi

# ── 3. Репозиторий ────────────────────────────────────────────────────────────
info "[3/7] Репозиторий..."

if [ "$UPDATE_MODE" = true ]; then
    cd "$DEPLOY_DIR"
    git pull --ff-only
    ok "Репозиторий обновлён"
else
    git clone "$REPO_URL" "$DEPLOY_DIR"
    ok "Репозиторий клонирован в $DEPLOY_DIR"
fi

# ── 4. Laravel ────────────────────────────────────────────────────────────────
info "[4/7] Настройка Laravel..."
cd "$DEPLOY_DIR/afisha-laravel"

# Composer зависимости
composer install --no-dev --optimize-autoloader --no-interaction -q
ok "Composer зависимости установлены"

# .env файл
if [ ! -f .env ]; then
    cp .env.example .env

    # Подставляем значения
    sed -i "s|APP_ENV=.*|APP_ENV=production|"       .env
    sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|"         .env
    sed -i "s|APP_URL=.*|APP_URL=https://${DOMAIN}|" .env
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|" .env
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=${DB_USER}|" .env
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASS}|" .env
    sed -i "s|UPLOADS_URL=.*|UPLOADS_URL=https://${DOMAIN}/uploads|" .env

    php artisan key:generate --force
    ok ".env создан"
else
    ok ".env уже существует — не перезаписываю"
fi

# Импорт схемы БД (только при первом деплое)
SCHEMA_FILE="$DEPLOY_DIR/afisha-laravel/database/schema.sql"
if [ -f "$SCHEMA_FILE" ] && ! mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" \
        -e "SHOW TABLES LIKE 'users';" 2>/dev/null | grep -q "users"; then
    mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$SCHEMA_FILE"
    ok "Схема БД импортирована"
fi

# Миграции (применяются только новые)
php artisan migrate --force
ok "Миграции применены"

# Кэш конфигурации
php artisan config:cache
php artisan route:cache
ok "Кэш Laravel обновлён"

# Права доступа
chown -R www-data:www-data "$DEPLOY_DIR"
chmod -R 755 "$DEPLOY_DIR"
chmod -R 775 "$DEPLOY_DIR/afisha-laravel/storage"
chmod -R 775 "$DEPLOY_DIR/afisha-laravel/bootstrap/cache"

# Директория для загрузок
mkdir -p "$DEPLOY_DIR/frontend/uploads/avatars"
mkdir -p "$DEPLOY_DIR/frontend/uploads/events"
mkdir -p "$DEPLOY_DIR/frontend/uploads/venues"
chown -R www-data:www-data "$DEPLOY_DIR/frontend/uploads"
chmod -R 775 "$DEPLOY_DIR/frontend/uploads"
ok "Права доступа установлены"

# ── 5. Nginx ──────────────────────────────────────────────────────────────────
info "[5/7] Настройка Nginx..."

cat > /etc/nginx/sites-available/$NGINX_SITE << NGINX
server {
    listen 80;
    server_name ${DOMAIN} www.${DOMAIN};

    root ${DEPLOY_DIR};
    index index.html;

    charset utf-8;
    client_max_body_size 20M;

    # Главная страница → редирект на /frontend/
    location = / {
        return 301 /frontend/;
    }

    # Фронтенд — статические HTML/CSS/JS файлы
    location /frontend/ {
        try_files \$uri \$uri/ /frontend/index.html;
        expires 1h;
    }

    # Загруженные пользователями файлы (аватары, фото событий и площадок)
    location /uploads/ {
        alias ${DEPLOY_DIR}/frontend/uploads/;
        expires 30d;
        add_header Cache-Control "public";
        try_files \$uri =404;
    }

    # Laravel API — все /api/* запросы через index.php
    location ~ ^/api(/.*)?$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME ${DEPLOY_DIR}/afisha-laravel/public/index.php;
        fastcgi_param DOCUMENT_ROOT   ${DEPLOY_DIR}/afisha-laravel/public;
        fastcgi_read_timeout 60;
        include fastcgi_params;
    }

    # Кэш статики
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff2?|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Скрываем служебные Laravel-файлы
    location ~ /\.(env|git) {
        deny all;
    }

    # Логи
    access_log /var/log/nginx/${NGINX_SITE}.access.log;
    error_log  /var/log/nginx/${NGINX_SITE}.error.log;
}
NGINX

# Активируем сайт
ln -sf /etc/nginx/sites-available/$NGINX_SITE /etc/nginx/sites-enabled/$NGINX_SITE
rm -f /etc/nginx/sites-enabled/default

nginx -t && systemctl reload nginx
ok "Nginx настроен"

# ── 6. PHP-FPM ────────────────────────────────────────────────────────────────
info "[6/7] Настройка PHP..."
PHP_INI="/etc/php/8.3/fpm/php.ini"
sed -i "s/upload_max_filesize = .*/upload_max_filesize = 20M/" "$PHP_INI"
sed -i "s/post_max_size = .*/post_max_size = 22M/"             "$PHP_INI"
sed -i "s/memory_limit = .*/memory_limit = 256M/"              "$PHP_INI"
systemctl restart php8.3-fpm
ok "PHP настроен"

# ── 7. Автозапуск ─────────────────────────────────────────────────────────────
info "[7/7] Настройка автозапуска..."
systemctl enable nginx mysql php8.3-fpm
ok "Автозапуск включён"

# ── Итог ──────────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  Деплой завершён успешно!${NC}"
echo -e "${GREEN}════════════════════════════════════════════════════${NC}"
echo ""
echo -e "  Сайт:      ${CYAN}http://${DOMAIN}/frontend/${NC}"
echo -e "  API:       ${CYAN}http://${DOMAIN}/api/${NC}"
echo -e "  Загрузки:  ${CYAN}http://${DOMAIN}/uploads/${NC}"
echo ""
echo -e "  БД имя:    ${YELLOW}${DB_NAME}${NC}"
echo -e "  БД юзер:   ${YELLOW}${DB_USER}${NC}"
echo -e "  БД пароль: ${YELLOW}${DB_PASS}${NC}  ← сохраните!"
echo ""
echo -e "${CYAN}Следующий шаг — настройка HTTPS:${NC}"
echo -e "  apt install certbot python3-certbot-nginx -y"
echo -e "  certbot --nginx -d ${DOMAIN} -d www.${DOMAIN}"
echo ""

# ── Подсказка для обновлений ──────────────────────────────────────────────────
cat > "$DEPLOY_DIR/update.sh" << 'UPDATEEOF'
#!/bin/bash
# Обновление приложения (запускать в /var/www/afisha)
set -e
cd /var/www/afisha
git pull --ff-only
cd afisha-laravel
composer install --no-dev --optimize-autoloader --no-interaction -q
php artisan migrate --force
php artisan config:cache
php artisan route:cache
chown -R www-data:www-data /var/www/afisha/afisha-laravel/storage
chown -R www-data:www-data /var/www/afisha/frontend/uploads
echo "✓ Обновление завершено"
UPDATEEOF
chmod +x "$DEPLOY_DIR/update.sh"
ok "Скрипт обновления создан: $DEPLOY_DIR/update.sh"
